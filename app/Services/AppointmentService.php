<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\HealthRecord;
use App\Models\Pet;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentService
{
    public const HORIZON_DAYS = 14;

    /**
     * Statuses that still occupy a vet's time.
     *
     * @var list<AppointmentStatus>
     */
    public const HOLDING = [
        AppointmentStatus::Pending,
        AppointmentStatus::Approved,
        AppointmentStatus::Rescheduled,
    ];

    /**
     * @var list<AppointmentStatus>
     */
    public const MUTABLE = [
        AppointmentStatus::Pending,
        AppointmentStatus::Approved,
    ];

    /**
     * @var array<int, string>
     */
    public const WEEKDAYS = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',
    ];

    /**
     * @var list<int>
     */
    public const LENGTHS = [10, 15, 20, 30, 45, 60];

    public function __construct(private NotificationService $notifications) {}

    /**
     * @return list<array{weekday: int, start: string, end: string, slot_minutes: int}>
     */
    public function windows(User $vet): array
    {
        $windows = [];

        foreach ($vet->available_slots ?? [] as $item) {
            $window = $this->windowFrom($item);

            if ($window !== null) {
                $windows[] = $window;
            }
        }

        return $windows;
    }

    /**
     * @return list<array{weekday: string, start: string, end: string, slot_minutes: string}>
     */
    public function editorWindows(User $vet): array
    {
        return array_map(fn (array $window): array => [
            'weekday' => (string) $window['weekday'],
            'start' => $window['start'],
            'end' => $window['end'],
            'slot_minutes' => (string) $window['slot_minutes'],
        ], $this->windows($vet));
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array{weekday: int, start: string, end: string, slot_minutes: int}>
     */
    public function normalizeWindows(array $rows): array
    {
        $windows = [];

        foreach (array_values($rows) as $index => $row) {
            $start = $this->clock((string) ($row['start'] ?? ''));
            $end = $this->clock((string) ($row['end'] ?? ''));
            $length = (int) ($row['slot_minutes'] ?? 0);

            if ($end <= $start) {
                $this->fail("windows.$index.end", 'The end time has to be after the start.');
            }

            if (($this->minutesPastMidnight($end) - $this->minutesPastMidnight($start)) < $length) {
                $this->fail("windows.$index.slot_minutes", 'The visit length has to fit between the start and end.');
            }

            $windows[] = [
                'weekday' => (int) $row['weekday'],
                'start' => $start,
                'end' => $end,
                'slot_minutes' => $length,
            ];
        }

        return $windows;
    }

    public function summary(User $vet): string
    {
        return collect($this->windows($vet))
            ->map(fn (array $window): string => self::WEEKDAYS[$window['weekday']].' '.$window['start'].'–'.$window['end'])
            ->implode(', ');
    }

    /**
     * @return array{earliest: ?array{value: string, label: string, date: string}, dates: array<string, array{label: string, slots: list<array{value: string, label: string}>}>}
     */
    public function calendar(User $vet, ?int $ignoreId = null): array
    {
        $dates = [];
        $earliest = null;
        $day = now()->startOfDay();
        $holding = $this->holdingRows($vet, false);

        for ($offset = 0; $offset < self::HORIZON_DAYS; $offset++) {
            $slots = [];

            foreach ($this->generatedOn($vet, $day) as $slot) {
                if ($slot['start']->lte(now())) {
                    continue;
                }

                if ($this->overlaps($holding, $vet, $slot['start'], $slot['minutes'], $ignoreId)) {
                    continue;
                }

                $value = $slot['start']->format('Y-m-d\TH:i');
                $entry = [
                    'value' => $value,
                    'label' => $slot['start']->format('g:i A'),
                ];
                $slots[] = $entry;
                $earliest ??= [
                    'value' => $value,
                    'label' => $slot['start']->format('D, M j, g:i A'),
                    'date' => $slot['start']->toDateString(),
                ];
            }

            if ($slots !== []) {
                $dates[$day->toDateString()] = [
                    'label' => $day->format('D, M j'),
                    'slots' => $slots,
                ];
            }

            $day->addDay();
        }

        return [
            'earliest' => $earliest,
            'dates' => $dates,
        ];
    }

    /**
     * @param  array{pet_id: int|string, vet_id: int|string, appointment_time: string, reason?: ?string, owner_notes?: ?string}  $data
     */
    public function book(User $owner, array $data): Appointment
    {
        $pet = Pet::query()->findOrFail($data['pet_id']);
        abort_unless($pet->owner_id === $owner->id, 403);

        $vet = User::query()->findOrFail($data['vet_id']);
        abort_unless($vet->isVet(), 422);

        return DB::transaction(function () use ($owner, $pet, $vet, $data): Appointment {
            User::query()->whereKey($vet->id)->lockForUpdate()->first();
            $slot = $this->assertOpenSlot($vet, $data['appointment_time']);

            $appointment = Appointment::query()->create([
                'pet_id' => $pet->id,
                'owner_id' => $owner->id,
                'vet_id' => $vet->id,
                'appointment_time' => $slot['start'],
                'status' => AppointmentStatus::Pending,
                'reason' => $data['reason'] ?? null,
                'owner_notes' => $data['owner_notes'] ?? null,
            ]);

            $when = $slot['start']->format('M j, g:i A');
            $this->notifications->notify(
                $vet,
                'New appointment request',
                $owner->name.' requested '.$pet->name.' on '.$when.'.',
                'appointment',
                route('vet.appointments'),
            );
            $this->notifications->notify(
                $owner,
                'Appointment requested',
                'Your visit for '.$pet->name.' with '.$vet->name.' on '.$when.' is waiting for approval.',
                'appointment',
                route('owner.appointments.index'),
            );

            return $appointment;
        });
    }

    public function reschedule(Appointment $appointment, User $actor, string $time): Appointment
    {
        $this->assertParty($appointment, $actor);

        return DB::transaction(function () use ($appointment, $actor, $time): Appointment {
            User::query()->whereKey($appointment->vet_id)->lockForUpdate()->first();
            $locked = $this->locked($appointment);
            $this->assertMutable($locked, 'rescheduled');
            $slot = $this->assertOpenSlot($locked->vet, $time, $locked->id);
            $locked->appointment_time = $slot['start'];
            $locked->save();

            $when = $slot['start']->format('M j, g:i A');
            $other = $this->otherParty($locked, $actor);
            $this->notifications->notify(
                $other,
                'Visit rescheduled',
                $locked->pet->name.' was moved to '.$when.'.',
                'appointment',
                $this->linkFor($other),
            );

            return $locked;
        });
    }

    public function cancel(Appointment $appointment, User $actor): void
    {
        $this->assertParty($appointment, $actor);

        DB::transaction(function () use ($appointment, $actor): void {
            $locked = $this->locked($appointment);
            $this->assertMutable($locked, 'cancelled');
            $locked->status = AppointmentStatus::Cancelled;
            $locked->save();

            $when = $locked->appointment_time->timezone(config('app.timezone'))->format('M j, g:i A');
            $other = $this->otherParty($locked, $actor);
            $this->notifications->notify(
                $other,
                'Visit cancelled',
                $locked->pet->name.'\'s visit on '.$when.' was cancelled.',
                'appointment',
                $this->linkFor($other),
            );
        });
    }

    public function approve(Appointment $appointment, User $vet): void
    {
        abort_unless($appointment->vet_id === $vet->id, 403);

        DB::transaction(function () use ($appointment, $vet): void {
            $locked = $this->locked($appointment);

            if ($locked->status !== AppointmentStatus::Pending) {
                $this->fail('appointment', 'Only a pending visit can be approved.');
            }

            $locked->status = AppointmentStatus::Approved;
            $locked->save();

            $when = $locked->appointment_time->timezone(config('app.timezone'))->format('M j, g:i A');
            $this->notifications->notify(
                $locked->owner,
                'Visit approved',
                $vet->name.' approved '.$locked->pet->name.' on '.$when.'.',
                'appointment',
                route('owner.appointments.index'),
            );
        });
    }

    /**
     * @param  array{title: string, diagnosis?: ?string, treatment?: ?string, symptoms?: ?string, medications?: ?string, lab_results?: ?string, notes?: ?string}  $data
     */
    public function completeWithTreatment(Appointment $appointment, User $vet, array $data): void
    {
        abort_unless($appointment->vet_id === $vet->id, 403);

        DB::transaction(function () use ($appointment, $vet, $data): void {
            $locked = $this->locked($appointment);

            if (! in_array($locked->status, self::HOLDING, true)) {
                $this->fail('appointment', 'This visit can no longer be completed.');
            }

            HealthRecord::query()->create([
                'pet_id' => $locked->pet_id,
                'vet_id' => $vet->id,
                'visit_date' => $locked->appointment_time->toDateString(),
                'record_type' => 'treatment',
                'title' => $data['title'],
                'diagnosis' => $data['diagnosis'] ?? null,
                'treatment' => $data['treatment'] ?? null,
                'symptoms' => $data['symptoms'] ?? null,
                'medications' => $data['medications'] ?? null,
                'lab_results' => $data['lab_results'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $locked->status = AppointmentStatus::Completed;
            $locked->vet_notes = $data['notes'] ?? $locked->vet_notes;
            $locked->save();

            $this->notifications->notify(
                $locked->owner,
                'Visit completed',
                $vet->name.' added a treatment for '.$locked->pet->name.' and marked the visit complete.',
                'appointment',
                route('owner.appointments.index'),
            );
        });
    }

    public function ics(Appointment $appointment, User $actor): string
    {
        $this->assertParty($appointment, $actor);
        $appointment->loadMissing(['pet', 'vet', 'owner']);

        $start = $appointment->appointment_time->copy()->utc();
        $minutes = $this->minutesCovering($appointment->vet, $start->copy()->timezone(config('app.timezone'))) ?? 30;
        $end = $start->copy()->addMinutes($minutes);
        $summary = 'FurShield visit for '.$appointment->pet->name;
        $description = trim($appointment->pet->name.' with '.$appointment->vet->name.($appointment->reason ? '. '.$appointment->reason : ''));

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//FurShield//Appointments//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:appointment-'.$appointment->id.'@furshield',
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'DTSTART:'.$start->format('Ymd\THis\Z'),
            'DTEND:'.$end->format('Ymd\THis\Z'),
            'SUMMARY:'.$this->icsText($summary),
            'DESCRIPTION:'.$this->icsText($description),
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", $lines)."\r\n";
    }

    public function canChange(Appointment $appointment): bool
    {
        return in_array($appointment->status, self::MUTABLE, true);
    }

    private function assertParty(Appointment $appointment, User $actor): void
    {
        abort_unless(in_array($actor->id, [$appointment->owner_id, $appointment->vet_id], true), 403);
    }

    private function assertMutable(Appointment $appointment, string $verb): void
    {
        if (! $this->canChange($appointment)) {
            $this->fail('appointment', 'Only a pending or approved visit can be '.$verb.'.');
        }
    }

    private function locked(Appointment $appointment): Appointment
    {
        $locked = Appointment::query()->whereKey($appointment->id)->lockForUpdate()->firstOrFail();
        $locked->load(['pet', 'vet', 'owner']);

        return $locked;
    }

    private function otherParty(Appointment $appointment, User $actor): User
    {
        return $actor->id === $appointment->owner_id ? $appointment->vet : $appointment->owner;
    }

    private function linkFor(User $user): string
    {
        return $user->isVet()
            ? route('vet.appointments')
            : route('owner.appointments.index');
    }

    /**
     * @return array{start: Carbon, minutes: int}
     */
    private function assertOpenSlot(User $vet, string $value, ?int $ignoreId = null): array
    {
        try {
            $start = Carbon::parse($value, config('app.timezone'))->seconds(0);
        } catch (\Throwable) {
            $this->fail('appointment_time', 'Choose an open time.');
        }

        if ($start->lte(now())) {
            $this->fail('appointment_time', 'Choose a future time.');
        }

        $match = null;

        foreach ($this->generatedOn($vet, $start) as $slot) {
            if ($slot['start']->equalTo($start)) {
                $match = $slot;
                break;
            }
        }

        if ($match === null) {
            $this->fail('appointment_time', 'Choose an open time from this vet’s schedule.');
        }

        if ($this->overlaps($this->holdingRows($vet, true), $vet, $start, $match['minutes'], $ignoreId)) {
            $this->fail('appointment_time', 'That time was just booked. Pick another open time.');
        }

        return $match;
    }

    /**
     * @return list<array{start: Carbon, minutes: int}>
     */
    private function generatedOn(User $vet, CarbonInterface $day): array
    {
        $day = $day->copy()->timezone(config('app.timezone'))->startOfDay();
        $slots = [];

        foreach ($this->windows($vet) as $window) {
            if ($window['weekday'] !== $day->dayOfWeekIso) {
                continue;
            }

            $cursor = $day->copy()->setTimeFromTimeString($window['start']);
            $end = $day->copy()->setTimeFromTimeString($window['end']);

            while ($cursor->copy()->addMinutes($window['slot_minutes'])->lte($end)) {
                $slots[$cursor->format('Y-m-d H:i')] = [
                    'start' => $cursor->copy(),
                    'minutes' => $window['slot_minutes'],
                ];
                $cursor->addMinutes($window['slot_minutes']);
            }
        }

        ksort($slots);

        return array_values($slots);
    }

    /**
     * @param  Collection<int, Appointment>  $holding
     */
    private function overlaps(Collection $holding, User $vet, Carbon $start, int $minutes, ?int $ignoreId): bool
    {
        $end = $start->copy()->addMinutes($minutes);

        foreach ($holding as $row) {
            if ($ignoreId !== null && $row->id === $ignoreId) {
                continue;
            }

            $otherStart = $row->appointment_time->copy()->timezone(config('app.timezone'))->seconds(0);
            $otherMinutes = $this->minutesCovering($vet, $otherStart) ?? $minutes;
            $otherEnd = $otherStart->copy()->addMinutes($otherMinutes);

            if ($start->lt($otherEnd) && $otherStart->lt($end)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection<int, Appointment>
     */
    private function holdingRows(User $vet, bool $lock): Collection
    {
        $query = Appointment::query()
            ->where('vet_id', $vet->id)
            ->whereIn('status', self::HOLDING)
            ->orderBy('appointment_time');

        if ($lock) {
            $query->lockForUpdate();
        }

        return $query->get();
    }

    private function minutesCovering(User $vet, Carbon $start): ?int
    {
        foreach ($this->generatedOn($vet, $start) as $slot) {
            if ($slot['start']->equalTo($start)) {
                return $slot['minutes'];
            }
        }

        return null;
    }

    /**
     * @return array{weekday: int, start: string, end: string, slot_minutes: int}|null
     */
    private function windowFrom(mixed $item): ?array
    {
        if (is_array($item) && isset($item['weekday'], $item['start'], $item['end'], $item['slot_minutes'])) {
            $start = $this->clock((string) $item['start']);
            $end = $this->clock((string) $item['end']);
            $length = (int) $item['slot_minutes'];

            if ($start === '' || $end === '' || $end <= $start || ! in_array($length, self::LENGTHS, true)) {
                return null;
            }

            $weekday = (int) $item['weekday'];

            if ($weekday < 1 || $weekday > 7) {
                return null;
            }

            return [
                'weekday' => $weekday,
                'start' => $start,
                'end' => $end,
                'slot_minutes' => $length,
            ];
        }

        if (! is_string($item) || ! preg_match('/^(mon|tue|wed|thu|fri|sat|sun)[a-z]*\s+(\d{1,2}:\d{2})/i', trim($item), $matches)) {
            return null;
        }

        $weekday = [
            'mon' => 1,
            'tue' => 2,
            'wed' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6,
            'sun' => 7,
        ][strtolower($matches[1])];
        $start = $this->clock($matches[2]);
        $startAt = Carbon::createFromFormat('H:i', $start);
        $endAt = $startAt->copy()->addHour();

        if ($endAt->day !== $startAt->day) {
            return null;
        }

        return [
            'weekday' => $weekday,
            'start' => $start,
            'end' => $endAt->format('H:i'),
            'slot_minutes' => 30,
        ];
    }

    private function clock(string $value): string
    {
        if (! preg_match('/^(\d{1,2}):(\d{2})/', trim($value), $matches)) {
            return '';
        }

        $hour = (int) $matches[1];
        $minute = (int) $matches[2];

        if ($hour > 23 || $minute > 59) {
            return '';
        }

        return sprintf('%02d:%02d', $hour, $minute);
    }

    private function minutesPastMidnight(string $clock): int
    {
        [$hour, $minute] = array_map('intval', explode(':', $clock));

        return ($hour * 60) + $minute;
    }

    private function icsText(string $value): string
    {
        return str_replace(
            ['\\', "\r\n", "\n", ',', ';'],
            ['\\\\', '\n', '\n', '\,', '\;'],
            $value,
        );
    }

    /**
     * @throws ValidationException
     */
    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}

<?php

namespace App\Services;

use App\Enums\CareStatus;
use App\Enums\HealthRecordType;
use App\Models\HealthRecord;
use App\Models\Pet;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReminderService
{
    public function __construct(private NotificationService $notifications) {}

    public function schedule(HealthRecord $record): void
    {
        $days = HealthRecordType::tryFrom((string) $record->record_type)?->recurrenceDays();
        $record->recurrence_days = $days;

        if ($days === null || $record->visit_date === null) {
            $record->next_due_date = null;
        } else {
            $record->next_due_date = Carbon::parse($record->visit_date)
                ->startOfDay()
                ->addDays($days)
                ->toDateString();
        }

        $record->reminder_sent_at = null;
    }

    public function statusFor(Pet $pet): CareStatus
    {
        $today = Carbon::today();
        $horizon = $today->copy()->addDays(7);
        $dueSoon = false;

        foreach ($this->dueDates($pet) as $due) {
            if ($due->lt($today)) {
                return CareStatus::Overdue;
            }

            if ($due->lte($horizon)) {
                $dueSoon = true;
            }
        }

        return $dueSoon ? CareStatus::DueSoon : CareStatus::OnTrack;
    }

    public function sendDueReminders(?CarbonInterface $today = null): int
    {
        $today = Carbon::parse($today ?? Carbon::today())->startOfDay();
        $sent = 0;

        $records = HealthRecord::query()
            ->whereNotNull('next_due_date')
            ->whereDate('next_due_date', '<=', $today->copy()->addDays(7)->toDateString())
            ->with('pet.owner')
            ->orderBy('id')
            ->get();

        foreach ($records as $record) {
            if ($this->sendIfNeeded($record, $today)) {
                $sent++;
            }
        }

        return $sent;
    }

    private function sendIfNeeded(HealthRecord $record, CarbonInterface $today): bool
    {
        $due = Carbon::parse($record->next_due_date)->startOfDay();
        $daysUntil = $this->daysUntil($today, $due);
        $stage = $this->stage($daysUntil);

        if ($stage === null) {
            return false;
        }

        if ($record->reminder_sent_at !== null) {
            $sentStage = $this->stage($this->daysUntil($record->reminder_sent_at, $due));
            if ($this->rank($sentStage) >= $this->rank($stage)) {
                return false;
            }
        }

        $pet = $record->pet;
        $owner = $pet?->owner;

        if ($pet === null || $owner === null) {
            return false;
        }

        [$title, $message] = $this->copy($stage, $pet->name, $record->title, $due, $daysUntil);

        DB::transaction(function () use ($owner, $title, $message, $pet, $record, $today, $stage): void {
            $this->notifications->notify(
                $owner,
                $title,
                $message,
                $stage === 'overdue' ? 'alert' : 'reminder',
                route('owner.pets.health', $pet),
            );

            $record->update(['reminder_sent_at' => $today]);
        });

        return true;
    }

    /**
     * @return list<Carbon>
     */
    private function dueDates(Pet $pet): array
    {
        $values = $pet->relationLoaded('healthRecords')
            ? $pet->healthRecords->pluck('next_due_date')
            : $pet->healthRecords()->whereNotNull('next_due_date')->pluck('next_due_date');

        $dates = [];

        foreach ($values as $value) {
            if ($value === null) {
                continue;
            }

            $dates[] = Carbon::parse($value)->startOfDay();
        }

        return $dates;
    }

    private function daysUntil(CarbonInterface $from, CarbonInterface $due): int
    {
        return (int) round($from->copy()->startOfDay()->diffInDays($due->copy()->startOfDay(), false));
    }

    private function stage(int $daysUntil): ?string
    {
        if ($daysUntil < 0) {
            return 'overdue';
        }

        if ($daysUntil <= 1) {
            return 'tomorrow';
        }

        if ($daysUntil <= 7) {
            return 'week';
        }

        return null;
    }

    private function rank(?string $stage): int
    {
        return match ($stage) {
            'week' => 1,
            'tomorrow' => 2,
            'overdue' => 3,
            default => 0,
        };
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function copy(string $stage, string $petName, string $title, CarbonInterface $due, int $daysUntil): array
    {
        $when = $due->format('M d, Y');

        return match ($stage) {
            'week' => [
                "{$petName} is due for care in {$daysUntil} days",
                "{$title} is due on {$when}.",
            ],
            'tomorrow' => [
                $daysUntil === 0
                    ? "{$petName} is due for care today"
                    : "{$petName} is due for care tomorrow",
                "{$title} is due on {$when}.",
            ],
            default => [
                "{$petName} is overdue for care",
                "{$title} was due on {$when}.",
            ],
        };
    }
}

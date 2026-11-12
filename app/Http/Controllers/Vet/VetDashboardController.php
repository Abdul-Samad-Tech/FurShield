<?php

namespace App\Http\Controllers\Vet;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Pet;
use App\Services\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VetDashboardController extends Controller
{
    public function __construct(private AppointmentService $appointments) {}

    public function appointments(): View
    {
        $vet = Auth::user();
        $open = Appointment::query()
            ->where('vet_id', $vet->id)
            ->whereIn('status', AppointmentService::HOLDING)
            ->with(['pet', 'owner'])
            ->orderBy('appointment_time')
            ->get();

        $today = $open->filter(fn (Appointment $appointment): bool => $appointment->appointment_time->isSameDay(now()))->values();
        $upcoming = $open->filter(fn (Appointment $appointment): bool => $appointment->appointment_time->gt(now()->endOfDay()))->values();
        $history = Appointment::query()
            ->where('vet_id', $vet->id)
            ->where(function ($query): void {
                $query->whereIn('status', [AppointmentStatus::Completed, AppointmentStatus::Cancelled])
                    ->orWhere(function ($query): void {
                        $query->whereIn('status', AppointmentService::HOLDING)
                            ->where('appointment_time', '<', now()->startOfDay());
                    });
            })
            ->with(['pet', 'owner'])
            ->orderByDesc('appointment_time')
            ->paginate(8);

        $calendars = [];

        foreach ($open as $appointment) {
            if ($this->appointments->canChange($appointment)) {
                $calendars[$appointment->id] = $this->appointments->calendar($vet, $appointment->id);
            }
        }

        return view('vet.appointments', compact('today', 'upcoming', 'history', 'calendars'));
    }

    public function updateAppointment(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($appointment->vet_id === Auth::id(), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:approved,cancelled'],
        ]);

        if ($validated['status'] === 'approved') {
            $this->appointments->approve($appointment, Auth::user());
        } else {
            $this->appointments->cancel($appointment, Auth::user());
        }

        return back()->with('success', 'Appointment updated.');
    }

    public function reschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($appointment->vet_id === Auth::id(), 403);

        $data = $request->validate([
            'appointment_time' => ['required', 'date'],
        ]);

        $this->appointments->reschedule($appointment, Auth::user(), $data['appointment_time']);

        return back()->with('success', 'The visit was moved. The owner has been told.');
    }

    public function ics(Appointment $appointment): Response
    {
        $body = $this->appointments->ics($appointment, Auth::user());

        return response($body, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="furshield-visit.ics"',
        ]);
    }

    public function petHistory(Pet $pet): View
    {
        $hasAccess = Appointment::query()
            ->where('vet_id', Auth::id())
            ->where('pet_id', $pet->id)
            ->exists();

        abort_unless($hasAccess, 403);

        $pet->load(['owner', 'healthRecords.vet']);

        return view('vet.pet-history', compact('pet'));
    }

    public function storeTreatment(Request $request, Appointment $appointment): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'diagnosis' => ['nullable', 'string'],
            'treatment' => ['nullable', 'string'],
            'symptoms' => ['nullable', 'string'],
            'medications' => ['nullable', 'string'],
            'lab_results' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->appointments->completeWithTreatment($appointment, Auth::user(), $validated);

        return back()->with('success', 'Treatment logged and the visit is complete.');
    }

    public function editProfile(): View
    {
        $windows = old('windows', $this->appointments->editorWindows(Auth::user()));

        return view('vet.profile', [
            'user' => Auth::user(),
            'windows' => array_values(is_array($windows) ? $windows : []),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        if ($request->boolean('windows_present')) {
            $request->merge([
                'windows' => collect($request->input('windows', []))->map(function ($row) {
                    if (! is_array($row)) {
                        return $row;
                    }

                    $row['start'] = substr((string) ($row['start'] ?? ''), 0, 5);
                    $row['end'] = substr((string) ($row['end'] ?? ''), 0, 5);

                    return $row;
                })->all(),
            ]);
        }

        $rules = [
            'specialization' => ['nullable', 'string', 'max:160'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
            'bio' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
        ];

        if ($request->boolean('windows_present')) {
            $rules['windows'] = ['nullable', 'array', 'max:14'];
            $rules['windows.*.weekday'] = ['required', 'integer', 'between:1,7'];
            $rules['windows.*.start'] = ['required', 'date_format:H:i'];
            $rules['windows.*.end'] = ['required', 'date_format:H:i'];
            $rules['windows.*.slot_minutes'] = ['required', 'integer', Rule::in(AppointmentService::LENGTHS)];
        }

        $validated = $request->validate($rules);
        $slots = $request->boolean('windows_present')
            ? $this->appointments->normalizeWindows($validated['windows'] ?? [])
            : Auth::user()->available_slots;

        Auth::user()->update([
            'specialization' => $validated['specialization'] ?? null,
            'experience_years' => $validated['experience_years'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'city' => $validated['city'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'available_slots' => $slots,
        ]);

        return back()->with('success', 'Vet profile updated.');
    }
}

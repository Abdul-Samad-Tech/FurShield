<?php

namespace App\Http\Controllers\Owner;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;
use App\Services\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(private AppointmentService $appointments) {}

    public function index(): View
    {
        $appointments = Appointment::query()
            ->where('owner_id', Auth::id())
            ->with(['pet', 'vet'])
            ->orderByDesc('appointment_time')
            ->paginate(10);

        return view('owner.appointments.index', compact('appointments'));
    }

    public function create(Request $request): View
    {
        $pets = Pet::query()->where('owner_id', Auth::id())->orderBy('name')->get();
        $reschedule = null;

        if ($request->filled('reschedule')) {
            $reschedule = Appointment::query()->with('vet')->findOrFail($request->integer('reschedule'));
            abort_unless($reschedule->owner_id === Auth::id(), 403);
            abort_unless($this->appointments->canChange($reschedule), 422);
        }

        $vets = User::query()
            ->where('role', UserRole::Vet)
            ->when($reschedule, fn ($query) => $query->whereKey($reschedule->vet_id))
            ->orderBy('name')
            ->get();

        $ignoreId = $reschedule?->id;
        $step = 1;

        if ($request->filled('pet_id') || $reschedule) {
            $step = 2;
        }

        if (($request->filled('pet_id') && $request->filled('vet')) || $reschedule) {
            $step = 3;
        }

        $payload = [
            'locked' => $reschedule !== null,
            'vets' => $vets->map(fn (User $vet): array => [
                'id' => $vet->id,
                'name' => $vet->name,
                'city' => $vet->city,
                'specialization' => $vet->specialization,
                'calendar' => $this->appointments->calendar($vet, $ignoreId),
            ])->values(),
            'initial' => [
                'pet_id' => (string) old('pet_id', $reschedule->pet_id ?? $request->input('pet_id', '')),
                'vet_id' => (string) old('vet_id', $reschedule->vet_id ?? $request->input('vet', '')),
                'reason' => (string) old('reason', $reschedule->reason ?? $request->input('reason', '')),
                'notes' => (string) old('owner_notes', $reschedule->owner_notes ?? ''),
                'time' => (string) old('appointment_time', ''),
                'step' => $request->session()->get('errors') ? 3 : $step,
            ],
        ];

        return view('owner.appointments.create', compact('pets', 'payload', 'reschedule'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pet_id' => ['required', 'exists:pets,id'],
            'vet_id' => ['required', 'exists:users,id'],
            'appointment_time' => ['required', 'date'],
            'reason' => ['nullable', 'string', 'max:200'],
            'owner_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->appointments->book(Auth::user(), $data);

        return redirect()
            ->route('owner.appointments.index')
            ->with('success', 'Appointment requested. Waiting for vet approval.');
    }

    public function reschedule(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless($appointment->owner_id === Auth::id(), 403);

        $data = $request->validate([
            'appointment_time' => ['required', 'date'],
        ]);

        $this->appointments->reschedule($appointment, Auth::user(), $data['appointment_time']);

        return redirect()
            ->route('owner.appointments.index')
            ->with('success', 'The visit was moved. The vet has been told.');
    }

    public function cancel(Appointment $appointment): RedirectResponse
    {
        $this->appointments->cancel($appointment, Auth::user());

        return back()->with('success', 'The visit was cancelled.');
    }

    public function ics(Appointment $appointment): Response
    {
        $body = $this->appointments->ics($appointment, Auth::user());

        return response($body, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="furshield-visit.ics"',
        ]);
    }
}

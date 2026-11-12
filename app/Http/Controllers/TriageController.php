<?php

namespace App\Http\Controllers;

use App\Models\HealthRecord;
use App\Models\Pet;
use App\Services\NearbyDirectoryService;
use App\Services\ReminderService;
use App\Services\TriageService;
use App\Support\Furshield;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TriageController extends Controller
{
    public function show(): View
    {
        return view('triage.show', [
            'species' => config('triage.species'),
            'symptoms' => config('triage.symptoms'),
            'disclaimer' => app(TriageService::class)->disclaimer(),
        ]);
    }

    public function store(Request $request, TriageService $triage): RedirectResponse
    {
        $validated = $this->validateCheck($request);
        $assessment = $triage->assess($validated['species'], $validated['symptoms']);
        $request->session()->put('triage.assessment', $assessment);

        return redirect()->route('triage.result', array_filter([
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
        ], fn ($value) => $value !== null));
    }

    public function result(Request $request, TriageService $triage, NearbyDirectoryService $nearby): View|RedirectResponse
    {
        $assessment = $request->session()->get('triage.assessment');

        if (! is_array($assessment) || ! isset($assessment['level'], $assessment['species'], $assessment['symptoms'])) {
            return redirect()->route('triage')->withErrors([
                'triage' => 'Start a symptom check to see a result.',
            ]);
        }

        $validated = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
        ]);

        $directory = ['clinics' => []];

        if ($assessment['level'] === 'emergency') {
            $directory = $nearby->vets(
                isset($validated['latitude']) ? (float) $validated['latitude'] : null,
                isset($validated['longitude']) ? (float) $validated['longitude'] : null,
                null,
                null,
                null,
                false,
            );
        }

        $pets = Auth::user()?->isOwner()
            ? Pet::query()->where('owner_id', Auth::id())->orderBy('name')->get()
            : collect();

        return view('triage.result', [
            'assessment' => $assessment,
            'disclaimer' => $triage->disclaimer(),
            'clinics' => array_slice($directory['clinics'], 0, 4),
            'located' => isset($validated['latitude'], $validated['longitude']),
            'emergencyTel' => Furshield::emergencyTel(),
            'emergencyPhone' => Furshield::emergencyPhone(),
            'pets' => $pets,
        ]);
    }

    public function save(Request $request, TriageService $triage, ReminderService $reminders): RedirectResponse
    {
        $owner = Auth::user();
        abort_unless($owner !== null && $owner->isOwner(), 403);

        $validated = $this->validateCheck($request);

        if (! isset($validated['pet_id'])) {
            return back()->withErrors(['pet_id' => 'Choose a pet.'])->withInput();
        }

        $pet = Pet::query()
            ->where('owner_id', $owner->id)
            ->findOrFail($validated['pet_id']);
        $assessment = $triage->assess($validated['species'], $validated['symptoms']);
        $timeline = $triage->timeline($assessment);

        DB::transaction(function () use ($pet, $timeline, $reminders): void {
            $record = new HealthRecord([
                'pet_id' => $pet->id,
                'visit_date' => now()->toDateString(),
                'record_type' => $timeline['record_type'],
                'title' => $timeline['title'],
                'symptoms' => $timeline['symptoms'],
                'notes' => $timeline['notes'],
                'diagnosis' => null,
                'treatment' => null,
            ]);

            $reminders->schedule($record);
            $record->save();
        });

        $request->session()->put('triage.assessment', $assessment);

        return redirect()
            ->route('owner.pets.health', $pet)
            ->with('success', 'Symptom check saved to the health timeline.');
    }

    /**
     * @return array{species: string, symptoms: list<string>, latitude?: float, longitude?: float, pet_id?: int}
     */
    private function validateCheck(Request $request): array
    {
        return $request->validate([
            'species' => ['required', Rule::in(array_keys(config('triage.species')))],
            'symptoms' => ['required', 'array', 'min:1', 'max:14'],
            'symptoms.*' => ['string', 'distinct', Rule::in(array_keys(config('triage.symptoms')))],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'pet_id' => ['sometimes', 'integer'],
        ], [
            'species.required' => 'Choose the kind of animal.',
            'species.in' => 'Choose the kind of animal.',
            'symptoms.required' => 'Choose at least one symptom.',
            'symptoms.min' => 'Choose at least one symptom.',
            'symptoms.*.in' => 'Choose a symptom from the list.',
        ]);
    }
}

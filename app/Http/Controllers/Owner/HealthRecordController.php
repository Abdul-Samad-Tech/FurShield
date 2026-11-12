<?php

namespace App\Http\Controllers\Owner;

use App\Enums\HealthRecordType;
use App\Http\Controllers\Controller;
use App\Models\HealthRecord;
use App\Models\InsurancePolicy;
use App\Models\Pet;
use App\Services\ReminderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class HealthRecordController extends Controller
{
    public function index(Pet $pet): View
    {
        $this->authorizeOwner($pet);
        $records = $pet->healthRecords()->with('vet')->get();
        $policies = $pet->insurancePolicies;

        return view('owner.health.index', compact('pet', 'records', 'policies'));
    }

    public function store(Request $request, Pet $pet, ReminderService $reminders): RedirectResponse
    {
        $this->authorizeOwner($pet);

        $validated = $request->validate([
            'visit_date' => ['required', 'date'],
            'record_type' => ['required', Rule::enum(HealthRecordType::class)],
            'title' => ['required', 'string', 'max:160'],
            'diagnosis' => ['nullable', 'string'],
            'treatment' => ['nullable', 'string'],
            'symptoms' => ['nullable', 'string'],
            'medications' => ['nullable', 'string'],
            'lab_results' => ['nullable', 'string'],
            'allergies' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('health-docs', 'public');
        }

        DB::transaction(function () use ($pet, $validated, $documentPath, $reminders): void {
            $record = new HealthRecord([
                'pet_id' => $pet->id,
                'visit_date' => $validated['visit_date'],
                'record_type' => $validated['record_type'],
                'title' => $validated['title'],
                'diagnosis' => $validated['diagnosis'] ?? null,
                'treatment' => $validated['treatment'] ?? null,
                'symptoms' => $validated['symptoms'] ?? null,
                'medications' => $validated['medications'] ?? null,
                'lab_results' => $validated['lab_results'] ?? null,
                'allergies' => $validated['allergies'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'document_path' => $documentPath,
            ]);

            $reminders->schedule($record);
            $record->save();
        });

        return back()->with('success', 'Health record added to timeline.');
    }

    public function storeInsurance(Request $request, Pet $pet): RedirectResponse
    {
        $this->authorizeOwner($pet);

        $validated = $request->validate([
            'provider_name' => ['required', 'string', 'max:160'],
            'policy_number' => ['required', 'string', 'max:120'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'claim_notes' => ['nullable', 'string'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('insurance', 'public');
        }

        InsurancePolicy::query()->create([
            'pet_id' => $pet->id,
            'provider_name' => $validated['provider_name'],
            'policy_number' => $validated['policy_number'],
            'valid_from' => $validated['valid_from'] ?? null,
            'valid_to' => $validated['valid_to'] ?? null,
            'claim_notes' => $validated['claim_notes'] ?? null,
            'document_path' => $documentPath,
        ]);

        return back()->with('success', 'Insurance policy stored.');
    }

    private function authorizeOwner(Pet $pet): void
    {
        abort_unless($pet->owner_id === Auth::id(), 403);
    }
}

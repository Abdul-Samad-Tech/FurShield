<?php

namespace App\Http\Controllers\Shelter;

use App\Enums\AdoptionStatus;
use App\Http\Controllers\Controller;
use App\Models\AdoptionInterest;
use App\Models\AdoptionListing;
use App\Models\ShelterCareLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ShelterController extends Controller
{
    public function listings(): View
    {
        $listings = AdoptionListing::query()
            ->where('shelter_id', Auth::id())
            ->withCount('interests')
            ->latest()
            ->paginate(10);

        return view('shelter.listings', compact('listings'));
    }

    public function create(): View
    {
        return view('shelter.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pet_name' => ['required', 'string', 'max:120'],
            'species' => ['required', 'string', 'max:80'],
            'breed' => ['nullable', 'string', 'max:120'],
            'age' => ['nullable', 'integer', 'min:0', 'max:100'],
            'gender' => ['nullable', 'string', 'max:30'],
            'health_status' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('adoptions', 'public');
        }

        AdoptionListing::query()->create([
            'shelter_id' => Auth::id(),
            'pet_name' => $validated['pet_name'],
            'species' => $validated['species'],
            'breed' => $validated['breed'] ?? null,
            'age' => $validated['age'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'health_status' => $validated['health_status'] ?? null,
            'description' => $validated['description'] ?? null,
            'photo_path' => $photoPath,
            'status' => AdoptionStatus::Available,
        ]);

        return redirect()->route('shelter.listings')->with('success', 'Adoptable pet listed.');
    }

    public function show(AdoptionListing $listing): View
    {
        abort_unless($listing->shelter_id === Auth::id(), 403);
        $listing->load(['careLogs', 'interests.owner']);

        return view('shelter.show', compact('listing'));
    }

    public function storeCareLog(Request $request, AdoptionListing $listing): RedirectResponse
    {
        abort_unless($listing->shelter_id === Auth::id(), 403);

        $validated = $request->validate([
            'care_type' => ['required', 'string', 'max:80'],
            'notes' => ['nullable', 'string'],
        ]);

        ShelterCareLog::query()->create([
            'listing_id' => $listing->id,
            'care_type' => $validated['care_type'],
            'notes' => $validated['notes'] ?? null,
            'logged_at' => Carbon::now('UTC'),
        ]);

        return back()->with('success', 'Care log added.');
    }

    public function updateInterest(Request $request, AdoptionInterest $interest): RedirectResponse
    {
        abort_unless($interest->listing->shelter_id === Auth::id(), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,accepted,rejected,finalized'],
            'shelter_response' => ['nullable', 'string'],
        ]);

        $interest->update([
            'status' => $validated['status'],
            'shelter_response' => $validated['shelter_response'] ?? null,
        ]);

        if ($validated['status'] === 'finalized') {
            $interest->listing->update(['status' => AdoptionStatus::Adopted]);
        }

        return back()->with('success', 'Adoption interest updated.');
    }

    public function updateStatus(Request $request, AdoptionListing $listing): RedirectResponse
    {
        abort_unless($listing->shelter_id === Auth::id(), 403);

        $validated = $request->validate([
            'status' => ['required', 'in:available,pending,adopted'],
        ]);

        $listing->update(['status' => AdoptionStatus::from($validated['status'])]);

        return back()->with('success', 'Listing status updated.');
    }
}

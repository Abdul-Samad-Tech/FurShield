<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Services\PetIdService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PetController extends Controller
{
    public function index(): View
    {
        $pets = Pet::query()
            ->where('owner_id', Auth::id())
            ->with('healthRecords')
            ->withCount(['healthRecords', 'appointments'])
            ->latest()
            ->get();

        return view('owner.pets.index', compact('pets'));
    }

    public function create(): View
    {
        return view('owner.pets.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'species' => ['required', 'string', 'max:80'],
            'breed' => ['nullable', 'string', 'max:120'],
            'age' => ['nullable', 'integer', 'min:0', 'max:100'],
            'gender' => ['nullable', 'string', 'max:30'],
            'medical_history' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('pets', 'public');
        }

        Pet::query()->create([
            'owner_id' => Auth::id(),
            'name' => $validated['name'],
            'species' => $validated['species'],
            'breed' => $validated['breed'] ?? null,
            'age' => $validated['age'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'medical_history' => $validated['medical_history'] ?? null,
            'photo_path' => $photoPath,
            'gallery' => [],
        ]);

        return redirect()->route('owner.pets.index')->with('success', 'Pet profile created successfully.');
    }

    public function show(Pet $pet): View
    {
        $this->authorizeOwner($pet);
        $pet->load(['healthRecords.vet', 'insurancePolicies', 'appointments.vet', 'sightings']);

        return view('owner.pets.show', compact('pet'));
    }

    public function edit(Pet $pet): View
    {
        $this->authorizeOwner($pet);

        return view('owner.pets.edit', compact('pet'));
    }

    public function update(Request $request, Pet $pet): RedirectResponse
    {
        $this->authorizeOwner($pet);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'species' => ['required', 'string', 'max:80'],
            'breed' => ['nullable', 'string', 'max:120'],
            'age' => ['nullable', 'integer', 'min:0', 'max:100'],
            'gender' => ['nullable', 'string', 'max:30'],
            'medical_history' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'gallery_images' => ['nullable', 'array'],
            'gallery_images.*' => ['image', 'max:4096'],
        ]);

        $photoPath = $pet->photo_path;
        if ($request->hasFile('photo')) {
            if ($photoPath !== null) {
                Storage::disk('public')->delete($photoPath);
            }
            $photoPath = $request->file('photo')->store('pets', 'public');
        }

        $gallery = $pet->gallery ?? [];
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $image) {
                $gallery[] = $image->store('pets/gallery', 'public');
            }
        }

        $pet->update([
            'name' => $validated['name'],
            'species' => $validated['species'],
            'breed' => $validated['breed'] ?? null,
            'age' => $validated['age'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'medical_history' => $validated['medical_history'] ?? null,
            'photo_path' => $photoPath,
            'gallery' => $gallery,
        ]);

        return redirect()->route('owner.pets.show', $pet)->with('success', 'Pet profile updated.');
    }

    public function updatePublicId(Request $request, Pet $pet, PetIdService $petIds): RedirectResponse
    {
        $this->authorizeOwner($pet);

        $data = $request->validate([
            'fields' => ['nullable', 'array'],
            'fields.*' => ['string', Rule::in(array_keys(PetIdService::FIELDS))],
            'is_lost' => ['nullable', 'boolean'],
            'lost_note' => ['nullable', 'string', 'max:500'],
        ]);

        $petIds->updateSharing(
            $pet,
            $data['fields'] ?? [],
            $request->boolean('is_lost'),
            $data['lost_note'] ?? null,
        );

        return back()->with('success', 'The public pet tag was updated.');
    }

    public function regeneratePublicToken(Pet $pet, PetIdService $petIds): RedirectResponse
    {
        $this->authorizeOwner($pet);
        $petIds->regenerate($pet);

        return back()->with('success', 'The old tag link no longer works. Download a new tag.');
    }

    public function tag(Request $request, Pet $pet, PetIdService $petIds): Response
    {
        $this->authorizeOwner($pet);
        $download = $request->boolean('download');

        return response($petIds->tagSvg($pet), 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => ($download ? 'attachment' : 'inline').'; filename="furshield-pet-tag.svg"',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function destroy(Pet $pet): RedirectResponse
    {
        $this->authorizeOwner($pet);
        $pet->delete();

        return redirect()->route('owner.pets.index')->with('success', 'Pet profile removed.');
    }

    private function authorizeOwner(Pet $pet): void
    {
        abort_unless($pet->owner_id === Auth::id(), 403);
    }
}

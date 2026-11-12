<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Services\PetIdService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicPetController extends Controller
{
    public function __construct(private PetIdService $petIds) {}

    public function show(string $token): View
    {
        $pet = $this->pet($token);

        return view('pets.public', [
            'card' => $this->petIds->publicCard($pet),
            'token' => $pet->public_token,
        ]);
    }

    public function found(Request $request, string $token): RedirectResponse
    {
        $pet = $this->pet($token);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
            'finder_name' => ['nullable', 'string', 'max:80'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
        ]);

        $this->petIds->recordSighting($pet, $data);

        return redirect()
            ->route('pets.public', $pet->public_token)
            ->with('success', 'The owner has been told. Thank you.');
    }

    private function pet(string $token): Pet
    {
        return Pet::query()->where('public_token', $token)->firstOrFail();
    }
}

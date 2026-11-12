<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Feedback;
use App\Models\User;
use App\Services\AppointmentService;
use App\Services\NearbyDirectoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DirectoryController extends Controller
{
    public function vets(Request $request, NearbyDirectoryService $nearby): View
    {
        $filters = $request->validate([
            'latitude' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'city' => ['nullable', 'string', 'max:120'],
            'specialization' => ['nullable', 'string', 'max:160'],
            'rating' => ['nullable', 'numeric', 'min:1', 'max:5'],
            'today' => ['nullable', 'boolean'],
        ]);

        $directory = $nearby->vets(
            isset($filters['latitude']) ? (float) $filters['latitude'] : null,
            isset($filters['longitude']) ? (float) $filters['longitude'] : null,
            $filters['city'] ?? null,
            $filters['specialization'] ?? null,
            isset($filters['rating']) ? (float) $filters['rating'] : null,
            $request->boolean('today'),
        );

        return view('directory.vets', [
            'clinics' => $directory['clinics'],
            'specializations' => $directory['specializations'],
            'located' => isset($filters['latitude'], $filters['longitude']),
        ]);
    }

    public function showVet(User $user, AppointmentService $appointments): View
    {
        abort_unless($user->isVet(), 404);

        $feedbacks = Feedback::query()
            ->where('target_type', User::class)
            ->where('target_id', $user->id)
            ->with('user')
            ->latest()
            ->get();

        $average = round((float) $feedbacks->avg('rating'), 1);

        return view('directory.vet-show', [
            'vet' => $user,
            'feedbacks' => $feedbacks,
            'average' => $average,
            'canRate' => Auth::check() && Auth::user()->isOwner(),
            'availability' => $appointments->summary($user),
        ]);
    }

    public function shelters(Request $request): View
    {
        $shelters = User::query()
            ->where('role', UserRole::Shelter)
            ->when($request->filled('city'), fn ($q) => $q->where('city', 'like', '%'.$request->string('city').'%'))
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = '%'.$request->string('q').'%';
                $q->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('shelter_name', 'like', $term);
                });
            })
            ->orderBy('name')
            ->paginate(9)
            ->withQueryString();

        return view('directory.shelters', compact('shelters'));
    }

    public function showShelter(User $user): View
    {
        abort_unless($user->isShelter(), 404);

        $feedbacks = Feedback::query()
            ->where('target_type', User::class)
            ->where('target_id', $user->id)
            ->with('user')
            ->latest()
            ->get();

        $average = round((float) $feedbacks->avg('rating'), 1);
        $listings = $user->adoptionListings()->latest()->take(6)->get();

        return view('directory.shelter-show', [
            'shelter' => $user,
            'feedbacks' => $feedbacks,
            'average' => $average,
            'listings' => $listings,
            'canRate' => Auth::check() && Auth::user()->isOwner(),
        ]);
    }
}

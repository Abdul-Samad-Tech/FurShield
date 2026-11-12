<?php

namespace App\Http\Controllers;

use App\Enums\AdoptionStatus;
use App\Models\AdoptionInterest;
use App\Models\AdoptionListing;
use App\Services\AdoptionMatchService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdoptionBrowseController extends Controller
{
    public function index(Request $request, AdoptionMatchService $matches): View
    {
        $answers = $matches->answers($request);
        $query = AdoptionListing::query()
            ->whereIn('status', [AdoptionStatus::Available, AdoptionStatus::Pending])
            ->with('shelter')
            ->when($request->filled('species'), fn ($q) => $q->where('species', 'like', '%'.$request->string('species').'%'))
            ->when($request->filled('q'), function ($q) use ($request): void {
                $term = '%'.$request->string('q').'%';
                $q->where(function ($inner) use ($term): void {
                    $inner->where('pet_name', 'like', $term)
                        ->orWhere('breed', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            });

        if ($answers === null) {
            $listings = $query->latest()->paginate(12)->withQueryString();
        } else {
            $ranked = $query->get()
                ->sortByDesc(fn (AdoptionListing $listing): int => $matches->score($listing, $answers))
                ->values();
            $listings = $this->paginate($ranked, $request);
        }

        $deck = $listings->getCollection()->map(function (AdoptionListing $listing) use ($matches, $answers): array {
            $percent = $answers ? $matches->score($listing, $answers) : null;

            return $matches->card($listing, $percent);
        })->values();

        return view('adoption.index', [
            'listings' => $listings,
            'deck' => $deck,
            'answers' => $answers,
        ]);
    }

    public function show(AdoptionListing $listing): View
    {
        $listing->load('shelter');

        return view('adoption.show', compact('listing'));
    }

    public function expressInterest(Request $request, AdoptionListing $listing): JsonResponse|RedirectResponse
    {
        abort_unless(Auth::user()->isOwner(), 403);
        abort_unless($listing->status !== AdoptionStatus::Adopted, 422);

        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($listing, $validated): void {
            AdoptionInterest::query()->updateOrCreate(
                [
                    'listing_id' => $listing->id,
                    'owner_id' => Auth::id(),
                ],
                [
                    'message' => $validated['message'] ?? null,
                    'status' => 'pending',
                ]
            );

            if ($listing->status === AdoptionStatus::Available) {
                $listing->update(['status' => AdoptionStatus::Pending]);
            }
        });

        $message = 'Interest submitted to the shelter.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message]);
        }

        return back()->with('success', $message);
    }

    /**
     * @param  Collection<int, AdoptionListing>  $items
     */
    private function paginate(Collection $items, Request $request): LengthAwarePaginator
    {
        $perPage = 12;
        $page = max(1, (int) $request->input('page', 1));

        return new Paginator(
            $items->slice(($page - 1) * $perPage, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
    }
}

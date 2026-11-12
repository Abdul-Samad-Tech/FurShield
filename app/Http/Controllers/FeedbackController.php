<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    public function storeForUser(Request $request, User $user): RedirectResponse
    {
        abort_unless(Auth::check(), 403);
        abort_unless($user->isVet() || $user->isShelter(), 422);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        Feedback::query()->updateOrCreate(
            [
                'user_id' => Auth::id(),
                'target_type' => User::class,
                'target_id' => $user->id,
            ],
            [
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]
        );

        return back()->with('success', 'Thanks for your feedback.');
    }
}

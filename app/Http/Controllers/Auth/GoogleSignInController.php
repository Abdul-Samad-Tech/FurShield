<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Exceptions\GoogleSignInException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\GmailNoticeService;
use App\Services\GoogleIdToken;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GoogleSignInController extends Controller
{
    public function store(Request $request, GoogleIdToken $tokens, GmailNoticeService $mail): RedirectResponse
    {
        abort_unless(filled(config('services.google.client_id')), 404);

        $validated = $request->validate([
            'credential' => ['required', 'string', 'max:8000'],
        ]);

        try {
            $identity = $tokens->verify($validated['credential']);
        } catch (GoogleSignInException) {
            throw ValidationException::withMessages([
                'credential' => 'Google could not confirm that sign-in. Try again.',
            ]);
        }

        $user = User::query()->where('email', $identity['email'])->first();
        $joined = $user === null;

        if ($user === null) {
            $user = User::create([
                'name' => $identity['name'],
                'email' => $identity['email'],
                'password' => Str::password(40),
                'role' => UserRole::Owner,
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();

            event(new Registered($user));
        } elseif ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        $mail->welcome($user, $joined);

        return redirect()->intended(route($user->role->dashboardRoute(), absolute: false));
    }
}

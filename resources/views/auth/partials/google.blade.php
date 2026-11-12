@if (filled(config('services.google.client_id')))
    <div class="mt-6 border-t border-mist-deep pt-6">
        <p class="text-center text-sm text-ink-mute">or</p>
        <div
            id="g_id_onload"
            data-client_id="{{ config('services.google.client_id') }}"
            data-callback="furshieldGoogleSignIn"
            data-auto_prompt="false"
            data-context="signin"
        ></div>
        <div
            class="g_id_signin mt-3 flex justify-center"
            data-type="standard"
            data-theme="outline"
            data-size="large"
            data-text="continue_with"
            data-shape="pill"
            data-logo_alignment="left"
            data-width="320"
        ></div>
        <form id="google-signin-form" method="POST" action="{{ route('auth.google') }}" class="hidden">
            @csrf
            <input type="hidden" name="credential" id="google-credential" value="">
        </form>
        <x-input-error :messages="$errors->get('credential')" class="mt-2" />
        <p class="mt-2 text-center text-xs text-ink-mute">A new Google account joins as a pet owner. An email that already has an account signs in to that account.</p>
    </div>
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <script>
        window.furshieldGoogleSignIn = function (response) {
            var input = document.getElementById('google-credential');
            var form = document.getElementById('google-signin-form');

            if (!input || !form || !response || typeof response.credential !== 'string' || response.credential === '') {
                return;
            }

            input.value = response.credential;
            form.submit();
        };
    </script>
@endif

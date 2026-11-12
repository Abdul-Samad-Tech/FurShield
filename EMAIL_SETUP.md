# Email, Google sign-in, and Gemini

Secrets stay in `.env`. This file has no passwords and no API keys. `.env` is not committed.

## Gmail

Create an app password in the Google account (Account → Security → 2-Step Verification → App passwords). Then set:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=you@gmail.com
MAIL_PASSWORD="the app password"
MAIL_FROM_ADDRESS=you@gmail.com
MAIL_FROM_NAME=FurShield

SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_SECURE=false
SMTP_USER=you@gmail.com
SMTP_PASS="the app password"
MAIL_FROM="FurShield <you@gmail.com>"
```

`SMTP_SECURE=false` with port 587 means STARTTLS, which is what Gmail expects. Port 465 would use `SMTP_SECURE=true`.

`SMTP_*` is what Laravel’s mailer reads when those values are filled. `MAIL_MAILER=smtp` selects it. Password reset and email verification use this mailer. Leave `MAIL_MAILER=log` to keep messages in the log instead of sending them.

## Google sign-in

In Google Cloud → Credentials, create an OAuth client ID of type Web application. Add `http://127.0.0.1:8000` and the deployed origin as authorized JavaScript origins. No client secret is used. Google gives the browser an ID token, and Laravel checks the signature, the audience, and the email.

```env
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
VITE_GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLOCK_TOLERANCE_SEC=60
```

`VITE_GOOGLE_CLIENT_ID` is the same public id. The login and register pages read `GOOGLE_CLIENT_ID` from Laravel, so a Vite rebuild is not required. A new Google account joins as a pet owner. If that Gmail address already belongs to a vet, shelter, or admin, sign-in opens that existing account.

`GOOGLE_CLOCK_TOLERANCE_SEC` is how far the computer clock may be wrong. Use 60 when the clock is correct.

## Gemini

The FurShield Assistant calls Gemini with the OpenAI-compatible endpoint. The key stays on the server.

```env
GEMINI_API_KEY=
GEMINI_MODEL=gemini-2.5-flash
FURSHIELD_ASSISTANT_ENABLED=true
```

Leave `FURSHIELD_ASSISTANT_API_KEY` empty when `GEMINI_API_KEY` is set. Do not rename the Gemini key to a `VITE_` variable. With the flag false, the assistant button stays hidden.

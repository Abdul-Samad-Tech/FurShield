@component('mail.shell', [
    'title' => $joined ? 'Welcome to FurShield' : 'Welcome back to FurShield',
    'preheader' => $joined
        ? 'Your Gmail is connected. Your FurShield account is ready.'
        : 'You signed in to FurShield with Gmail.',
])
    <p style="margin:0 0 12px;font-size:16px;line-height:1.5;color:#102027;">Hello {{ $user->name }},</p>
    @if ($joined)
        <p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#102027;">Your Gmail account is now a FurShield pet owner account. You can keep health records, book visits, and order supplies from this address.</p>
    @else
        <p style="margin:0 0 12px;font-size:15px;line-height:1.6;color:#102027;">You just signed in with Gmail. Your pets, visits, and orders are where you left them.</p>
    @endif
    <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#5B737C;">Signed in as {{ $user->email }}.</p>
    <a href="{{ route('dashboard') }}" style="display:inline-block;background:#0E7C74;color:#F3FAF8;text-decoration:none;padding:12px 18px;border-radius:12px;font-size:14px;font-weight:600;">Open FurShield</a>
@endcomponent

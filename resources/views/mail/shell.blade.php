<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;padding:0;background:#F3FAF8;color:#102027;font-family:Outfit,'Segoe UI',sans-serif;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">{{ $preheader }}</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F3FAF8;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border:1px solid #D7EEE8;border-radius:16px;">
                    <tr>
                        <td style="padding:24px 28px;background:#0E7C74;border-radius:16px 16px 0 0;">
                            <p style="margin:0;font-family:Syne,'Segoe UI',sans-serif;font-size:22px;font-weight:700;letter-spacing:-0.02em;color:#F3FAF8;">FurShield</p>
                            <p style="margin:6px 0 0;font-size:13px;color:#D7EEE8;">{{ $title }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            {{ $slot }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 28px 24px;">
                            <p style="margin:0;font-size:12px;line-height:1.5;color:#5B737C;">This message was sent by FurShield because you used your Gmail account or placed an order. The full card number is never stored or emailed.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;">
                    <tr>
                        <td style="background:#0f172a;padding:20px 28px;color:#ffffff;font-size:15px;font-weight:600;letter-spacing:.02em;">
                            {{ config('app.name', 'Asset Management') }}
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:28px;">
                            <p style="margin:0 0 16px;font-size:15px;">Hi {{ $greetingName }},</p>

                            <h1 style="margin:0 0 12px;font-size:19px;line-height:1.35;font-weight:600;">{{ $title }}</h1>

                            <p style="margin:0 0 20px;font-size:15px;line-height:1.6;color:#334155;">{{ $body }}</p>

                            @if ($actionUrl)
                                <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
                                    <tr>
                                        <td style="border-radius:8px;background:#0f172a;">
                                            <a href="{{ $actionUrl }}" style="display:inline-block;padding:11px 20px;font-size:14px;font-weight:600;color:#ffffff;text-decoration:none;">{{ $actionLabel }}</a>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            @if ($sentAt)
                                <p style="margin:0;font-size:13px;color:#64748b;">Raised {{ $sentAt }}</p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 28px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:12px;color:#64748b;line-height:1.5;">
                            You are receiving this because you manage assets in {{ config('app.name', 'Asset Management') }}.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

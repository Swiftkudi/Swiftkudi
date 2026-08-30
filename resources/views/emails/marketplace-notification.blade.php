<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $subjectLine }}</title>
</head>
<body style="margin:0;background:#f8fafc;color:#0f172a;font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',Arial,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f8fafc;padding:32px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:620px;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
                <tr>
                    <td style="padding:24px 28px;border-bottom:1px solid #e2e8f0;">
                        <div style="font-size:22px;font-weight:800;letter-spacing:-0.02em;color:#4f46e5;">SwiftKudi</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:30px 28px;">
                        <div style="font-size:14px;color:#64748b;margin-bottom:12px;">Hello {{ $recipient->name ?: 'there' }},</div>
                        <h1 style="font-size:22px;line-height:1.35;margin:0 0 16px;color:#0f172a;">{{ $subjectLine }}</h1>
                        <div style="font-size:15px;line-height:1.75;color:#334155;white-space:pre-line;">{{ $messageText }}</div>
                        @if($actionUrl)
                        <div style="margin-top:26px;">
                            <a href="{{ $actionUrl }}" style="display:inline-block;background:#4f46e5;color:#ffffff;text-decoration:none;padding:12px 18px;border-radius:9px;font-size:14px;font-weight:700;">{{ $actionLabel }}</a>
                        </div>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td style="padding:20px 28px;background:#f8fafc;border-top:1px solid #e2e8f0;font-size:12px;line-height:1.6;color:#64748b;">
                        This is a transactional notification about activity on your SwiftKudi account. You can manage non-critical notification preferences from your account settings.<br>
                        &copy; {{ date('Y') }} {{ config('app.name', 'SwiftKudi') }}.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

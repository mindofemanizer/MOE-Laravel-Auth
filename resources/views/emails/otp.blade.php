<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Verification Code</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: system-ui, -apple-system, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background-color: #4f46e5; padding: 24px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 20px;">{{ config('app.name') }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 32px;">
                            <h2 style="color: #111827; margin: 0 0 16px; font-size: 18px;">Verification Code</h2>
                            <p style="color: #4b5563; margin: 0 0 24px; font-size: 14px; line-height: 1.5;">
                                {{ $message }}
                            </p>
                            <div style="background-color: #f3f4f6; border-radius: 8px; padding: 16px; text-align: center; margin-bottom: 24px;">
                                <span style="font-size: 32px; font-weight: bold; color: #4f46e5; letter-spacing: 8px;">{{ $code }}</span>
                            </div>
                            <p style="color: #6b7280; margin: 0; font-size: 13px; line-height: 1.5;">
                                If you did not request this code, please ignore this email.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f9fafb; padding: 16px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="color: #9ca3af; margin: 0; font-size: 12px;">
                                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

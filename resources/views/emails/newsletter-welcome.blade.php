{{--
    A plain HTML mail view, not a Markdown one. Markdown mail treats any line
    indented by four spaces as a code block, which turned this whole layout into
    escaped source text in the inbox. Rendered through `view:`, the HTML is sent
    as written, so keep the styles inline: nothing here is processed further.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to WestHub Healthcare Newsletter</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f9fafb;">
<div style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f9fafb; padding: 20px 0;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); border: 1px solid #e5e7eb;">

        {{-- Header Section --}}
        <div style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); padding: 40px 30px; text-align: center;">
            <div style="margin-bottom: 20px;">
                <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 800; letter-spacing: -0.02em;">WestHub<span style="color: #93c5fd; font-weight: 300;">Healthcare</span></h1>
                <p style="color: #dbeafe; font-size: 12px; text-transform: uppercase; letter-spacing: 0.2em; margin-top: 4px;">Excellence in Care</p>
            </div>
        </div>

        {{-- Main Content Section --}}
        <div style="padding: 40px 30px;">
            <h2 style="color: #111827; font-size: 24px; font-weight: 700; margin: 0 0 16px 0; line-height: 1.2;">Welcome to our community!</h2>

            <p style="color: #374151; font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
                Thank you for subscribing to the WestHub Healthcare newsletter. We are thrilled to have you with us.
            </p>

            <p style="color: #374151; font-size: 16px; line-height: 1.6; margin-bottom: 24px;">
                You'll be the first to receive our latest updates, health tips, and exclusive news directly in your inbox.
            </p>

            <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin: 0 0 24px 0;">
                <tr>
                    <td style="background-color: #1e3a8a; border-radius: 9999px;">
                        <a href="{{ config('app.url') }}" style="display: inline-block; padding: 14px 28px; color: #ffffff; font-size: 15px; font-weight: 700; text-decoration: none;">Visit Our Website</a>
                    </td>
                </tr>
            </table>

            <div style="border-top: 1px solid #f3f4f6; padding-top: 24px; margin-top: 24px;">
                <p style="color: #6b7280; font-size: 14px; font-style: italic; margin: 0;">
                    Best regards,<br>
                    The WestHub Healthcare Team
                </p>
            </div>
        </div>

        {{-- Footer Section --}}
        <div style="background-color: #f3f4f6; padding: 30px; text-align: center;">
            <p style="color: #9ca3af; font-size: 12px; margin: 0 0 12px 0;">
                WestHub Healthcare &bull; Premier Healthcare Services
            </p>
            <p style="color: #9ca3af; font-size: 11px; margin: 0; line-height: 1.5;">
                You are receiving this email because you subscribed on our website.<br>
                To unsubscribe, <a href="{{ route('newsletter.unsubscribe', ['email' => $subscriber->email]) }}" style="color: #6b7280; text-decoration: underline;">click here</a>.
            </p>
        </div>
    </div>
</div>
</body>
</html>

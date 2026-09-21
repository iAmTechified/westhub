@php
    $isAccepted = $templateKey === 'join.accepted';
    $isDeclined = $templateKey === 'join.declined';
    $accent = $isAccepted ? '#22c55e' : ($isDeclined ? '#f43f5e' : '#14abd5');
    $headline = $isAccepted
        ? 'Your Application Has Been Accepted'
        : ($isDeclined ? 'Your Application Status Update' : 'Your Application Update');
@endphp
{{--
    A plain HTML mail view, not a Markdown one. Markdown mail treats any line
    indented by four spaces as a code block, which sent this layout out as
    escaped source text. Rendered through `view:`, the HTML goes as written.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $headline }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f7fbff;">
<div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto;">
        <div style="margin:0 0 18px 0; border:1px solid #dbe9f8; border-radius:18px; overflow:hidden; background:linear-gradient(140deg, #ffffff, #f7fbff);">
            <div style="padding:20px 22px; background:linear-gradient(130deg, {{ $accent }} 0%, #075ea6 100%); color:#ffffff;">
                <p style="margin:0; font-size:11px; letter-spacing:1.7px; text-transform:uppercase; opacity:0.92;">WestHub Healthcare</p>
                <h2 style="margin:10px 0 0 0; font-size:22px; line-height:1.3; color:#ffffff;">{{ $headline }}</h2>
            </div>

            <div style="padding:22px;">
                <p style="margin:0 0 12px 0; font-size:15px; color:#12334b;">Hello {{ $recipientName }},</p>
                <div style="margin:0; font-size:14px; line-height:1.72; color:#39556f;">
                    {!! nl2br(e($bodyText)) !!}
                </div>

                <div style="margin-top:20px; padding:14px 16px; border-radius:12px; background:#eef7ff; border:1px solid #d6e8fa;">
                    <p style="margin:0; font-size:12px; line-height:1.6; color:#4c647c;">
                        Need support? Reply directly to this message and the WestHub team will assist you.
                    </p>
                </div>
            </div>
        </div>

        <p style="margin:0; font-size:14px; color:#39556f;">
            Regards,<br>
            {{ config('app.name') }}
        </p>
    </div>
</div>
</body>
</html>

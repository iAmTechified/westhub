<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your free month of WestHub healthcare services</title>
</head>
<body style="margin:0; padding:0; background:#f2f6fa; font-family: Arial, Helvetica, sans-serif; color:#3d3d3d; line-height:1.6;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2f6fa; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background:#ffffff; border-radius:16px; overflow:hidden;">

                    <tr>
                        <td style="background:#14264f; background-image:linear-gradient(120deg,#14264f 0%,#0d4e74 55%,#0c7a66 100%); padding:32px 32px 28px 32px;">
                            <p style="margin:0 0 6px 0; font-size:12px; letter-spacing:1.6px; text-transform:uppercase; color:#ffffff; opacity:.75;">WestHub Healthcare</p>
                            <p style="margin:0; font-size:34px; font-weight:bold; color:#ffffff;">
                                {{ $offer->offerAmount }} <span style="color:#34d399;">{{ $offer->offerHighlight }}</span>
                            </p>
                            <p style="margin:8px 0 0 0; font-size:15px; color:#ffffff; opacity:.9;">{{ $offer->offerSubline }}</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 32px 8px 32px;">
                            <h1 style="margin:0 0 12px 0; font-size:22px; color:#1c3f78;">Your free month is reserved, {{ \Illuminate\Support\Str::of($claim->full_name)->trim()->explode(' ')->first() }}.</h1>
                            <p style="margin:0 0 18px 0; font-size:15px;">
                                Thanks for claiming the WestHub free-month offer. Keep the voucher code below. A care
                                coordinator will call you within one business day to plan your first visit.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#eafaff; border:1px dashed #14abd5; border-radius:14px;">
                                <tr>
                                    <td style="padding:18px 22px;">
                                        <p style="margin:0 0 4px 0; font-size:11px; letter-spacing:1.4px; text-transform:uppercase; color:#075ea6; font-weight:bold;">Your voucher code</p>
                                        <p style="margin:0; font-size:26px; font-weight:bold; letter-spacing:1.5px; color:#1c3f78;">{{ $claim->voucher_code }}</p>
                                        @if($claim->expires_at)
                                            <p style="margin:8px 0 0 0; font-size:13px; color:#3d3d3d;">Valid until {{ $claim->expires_at->format('j M Y') }}.</p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:22px 32px 6px 32px;">
                            <a href="{{ route('home') }}?promo={{ urlencode($claim->voucher_code) }}"
                               style="display:inline-block; background:#14abd5; color:#ffffff; text-decoration:none; font-weight:bold; font-size:15px; padding:14px 26px; border-radius:999px;">
                                Book my first appointment
                            </a>
                        </td>
                    </tr>

                    @if($offer->includedServices !== [])
                        <tr>
                            <td style="padding:18px 32px 0 32px;">
                                <p style="margin:0 0 8px 0; font-size:13px; font-weight:bold; color:#1c3f78;">What the free month covers</p>
                                <ul style="margin:0; padding-left:18px; font-size:14px;">
                                    @foreach($offer->includedServices as $service)
                                        <li style="margin-bottom:4px;">{{ $service }}</li>
                                    @endforeach
                                </ul>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td style="padding:22px 32px 28px 32px;">
                            <p style="margin:0; font-size:12px; color:#8c8c8c;">
                                {{ $offer->finePrint }}
                                @if($offer->endsAtLabel())
                                    Offer ends {{ $offer->endsAtLabel() }}.
                                @endif
                            </p>
                            <p style="margin:16px 0 0 0; font-size:14px;">WestHub Healthcare</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>New free-month claim</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color:#12334b; line-height:1.6;">
    <h1 style="font-size:20px; margin:0 0 4px 0;">New free-month claim</h1>
    <p style="margin:0 0 18px 0; color:#5b6b7a;">Call this person within one business day.</p>

    <table cellpadding="6" cellspacing="0" style="border-collapse:collapse; font-size:14px;">
        <tr><td style="font-weight:bold;">Name</td><td>{{ $claim->full_name }}</td></tr>
        <tr><td style="font-weight:bold;">Email</td><td><a href="mailto:{{ $claim->email }}">{{ $claim->email }}</a></td></tr>
        <tr><td style="font-weight:bold;">Phone</td><td>{{ $claim->phone ?: 'Not provided' }}</td></tr>
        <tr><td style="font-weight:bold;">Service</td><td>{{ $claim->service?->name ?: 'Not specified' }}</td></tr>
        <tr><td style="font-weight:bold;">Voucher</td><td>{{ $claim->voucher_code }}</td></tr>
        <tr><td style="font-weight:bold;">Expires</td><td>{{ $claim->expires_at?->format('j M Y') ?: 'No expiry set' }}</td></tr>
        <tr><td style="font-weight:bold;">Marketing consent</td><td>{{ $claim->consent_at ? 'Yes' : 'No' }}</td></tr>
        <tr><td style="font-weight:bold;">Claimed from</td><td>{{ $claim->source_page ?: 'Unknown page' }}</td></tr>
        <tr><td style="font-weight:bold;">Claimed at</td><td>{{ $claim->created_at?->format('j M Y, g:i A') }}</td></tr>
    </table>

    @php($adminUrl = rtrim((string) config('services.westhub_admin.base_url'), '/').'/admin/promo-claims')

    <p style="margin-top:22px;">
        <a href="{{ $adminUrl }}" style="display:inline-block; background:#14abd5; color:#ffffff; text-decoration:none; font-weight:bold; padding:11px 20px; border-radius:999px; font-size:14px;">
            Open promo claims
        </a>
    </p>

    <p style="margin-top:20px; font-size:12px; color:#8c8c8c;">
        The claim is already saved and the voucher has been emailed to the client. No admin action is required unless
        you want to record the follow-up call.
    </p>
</body>
</html>

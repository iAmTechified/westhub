<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>New join application</title>
</head>
<body style="font-family: Arial, sans-serif; color: #12334b; line-height: 1.6;">
    <h1 style="font-size: 24px; margin-bottom: 12px;">New join application received</h1>

    <p style="margin-bottom: 18px;">
        A new WestHub join application is ready for review in the admin queue.
    </p>

    <table cellpadding="0" cellspacing="0" style="border-collapse: collapse; width: 100%; max-width: 640px;">
        <tbody>
            <tr>
                <td style="padding: 8px 0; font-weight: 700; width: 190px;">WestHub ID</td>
                <td style="padding: 8px 0;">{{ $payload['westhub_id'] }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 700;">Submitted</td>
                <td style="padding: 8px 0;">{{ $payload['submitted_at'] }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 700;">Name</td>
                <td style="padding: 8px 0;">{{ $payload['full_name'] }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 700;">Email</td>
                <td style="padding: 8px 0;">{{ $payload['email'] }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 700;">Phone</td>
                <td style="padding: 8px 0;">{{ $payload['phone'] ?: 'Not provided' }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 700;">Applicant type</td>
                <td style="padding: 8px 0;">{{ $payload['applicant_type'] }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 700;">Position / profession</td>
                <td style="padding: 8px 0;">{{ $payload['position_profession'] }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 0; font-weight: 700;">Date available</td>
                <td style="padding: 8px 0;">{{ $payload['date_available'] }}</td>
            </tr>
        </tbody>
    </table>

    @if(! empty($payload['admin_url']))
        <p style="margin-top: 24px;">
            <a href="{{ $payload['admin_url'] }}" style="display: inline-block; padding: 12px 18px; border-radius: 999px; background: #075EA6; color: #ffffff; text-decoration: none;">
                Open join request queue
            </a>
        </p>
    @endif
</body>
</html>

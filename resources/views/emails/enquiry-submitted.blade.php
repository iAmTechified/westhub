<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We received your enquiry</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f8fafc;
            padding-bottom: 40px;
        }
        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-spacing: 0;
            color: #1c3f78;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .header {
            background-color: #1c3f78;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .message-box {
            background-color: #f1f5f9;
            padding: 24px;
            border-radius: 8px;
            border-left: 4px solid #1c3f78;
            margin-bottom: 24px;
        }
        .message-box h3 {
            margin-top: 0;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
        }
        .message-box p {
            margin-bottom: 0;
            color: #334155;
            white-space: pre-line;
        }
        .footer {
            padding: 30px;
            text-align: center;
            font-size: 14px;
            color: #64748b;
        }
        .footer a {
            color: #1c3f78;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main">
            <tr>
                <td class="header">
                    <h1>WestHub Healthcare</h1>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <p>Hello <strong>{{ $enquiry->full_name }}</strong>,</p>
                    
                    <p>
                        Thank you for reaching out to WestHub Healthcare. We have received your enquiry and our care coordination team will review it and get back to you as soon as possible.
                    </p>

                    @if($enquiry->message)
                        <div class="message-box">
                            <h3>Your Message</h3>
                            <p>{{ $enquiry->message }}</p>
                        </div>
                    @endif

                    <p>
                        In the meantime, if you have any urgent questions, feel free to reply directly to this email.
                    </p>

                    <p>
                        Best regards,<br>
                        <strong>The WestHub Team</strong>
                    </p>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    <p>
                        &copy; {{ date('Y') }} WestHub Healthcare. All rights reserved.<br>
                        <a href="{{ config('app.url') }}">Visit our website</a>
                    </p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>

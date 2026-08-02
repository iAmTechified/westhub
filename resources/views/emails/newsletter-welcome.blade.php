<x-mail::message>
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

            <x-mail::button :url="config('app.url')">
                Visit Our Website
            </x-mail::button>

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
</x-mail::message>

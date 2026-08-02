<x-mail::message>
# Hello {{ $recipientName }},

{{ $bodyText }}

<x-mail::button :url="config('app.url')">
Visit WestHub Healthcare
</x-mail::button>

Warm regards,<br>
{{ config('app.name') }} Team
</x-mail::message>

<x-mail::message>
# Hello {{ $recipientName }},

{!! nl2br(e($bodyText)) !!}

<x-mail::panel>
If you need urgent assistance, reply directly to this email and our care coordination team will get back to you.
</x-mail::panel>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

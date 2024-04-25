<x-mail::message>
# {{ config('app.name') }} Email Verification

Hi {{ $userName }}! <br />

Your email verification code is: {{ $code }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>

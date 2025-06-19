<x-mail::message>
# Verify Email

Hi {{ $user->username }},
Please click on the below link to verify your email address:
<x-mail::button :url="$url">
verify email
</x-mail::button>

</x-mail::message>

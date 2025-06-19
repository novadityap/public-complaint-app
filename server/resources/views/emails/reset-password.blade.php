<x-mail::message>
# Reset Password

Hi {{ $user->username }},
Please click on the below link to reset your password:
<x-mail::button :url="$url">
reset password
</x-mail::button>

</x-mail::message>

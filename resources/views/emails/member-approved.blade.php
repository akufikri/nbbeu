<x-mail::message>
# Congratulations, {{ $user->name }}!

Your membership application at **North Borneo Banking Executive Union (NBBEU)** has been approved.

- Member No.: **{{ $user->member_no }}**
- Company: {{ $user->company }}

Your member card and certificate are attached to this email (PDF).

To access the member dashboard (download documents, renew, update profile), please create your account password first:

<x-mail::button :url="route('password.request')">
Create Account Password
</x-mail::button>

Thank you,<br>
NBBEU Admin
</x-mail::message>

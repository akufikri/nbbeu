<x-mail::message>
# Congratulations, {{ $user->name }}!

Your membership application at **North Borneo Banking Executive Union (NBBEU)** has been approved.

- Member No.: **{{ $user->member_no }}**
- Company: {{ $user->company }}

Your member card and certificate are attached to this email (PDF).

You can log in to the member dashboard anytime using the email and password you registered with.

Thank you,<br>
NBBEU Admin
</x-mail::message>

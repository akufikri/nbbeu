<x-mail::message>
# Congratulations, {{ $user->name }}!

Your membership application at **North Borneo Banking Executive Union (NBBEU)** has been approved.

To activate your membership, please complete payment of the one-time registration fee:

<x-mail::button :url="$billUrl">
Pay Registration Fee
</x-mail::button>

Thank you,<br>
NBBEU Admin
</x-mail::message>

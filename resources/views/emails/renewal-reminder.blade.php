<x-mail::message>
# Hi, {{ $user->name }}

Your NBBEU membership (Member No. **{{ $user->member_no }}**) will expire on
**{{ $user->renewal_expires_at?->format('d M Y') }}** ({{ $daysLeft }} days left).

Renew soon to keep your membership active.

<x-mail::button :url="route('dashboard')">
Renew Now
</x-mail::button>

Thank you,<br>
NBBEU Admin
</x-mail::message>

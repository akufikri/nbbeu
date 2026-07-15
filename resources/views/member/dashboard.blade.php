<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Member Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <h3 class="font-medium text-gray-900">Membership Status</h3>

                @if ($user->status === 'pending')
                    <p class="mt-2 text-sm text-amber-700 bg-amber-50 rounded-md p-3">
                        Your application is still awaiting admin review.
                        @if ($latestPayment && $latestPayment->status !== 'paid')
                            Payment is also not yet complete —
                            <a href="{{ route('registration.status', ['email' => $user->email]) }}" class="underline">check status</a>.
                        @endif
                    </p>
                @elseif ($user->status === 'rejected')
                    <p class="mt-2 text-sm text-red-700 bg-red-50 rounded-md p-3">
                        Your application was rejected. {{ $user->rejection_reason ? 'Reason: '.$user->rejection_reason : '' }}
                    </p>
                @else
                    <div class="mt-3 grid sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Member No.</p>
                            <p class="font-mono font-medium">{{ $user->member_no }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Valid Until</p>
                            <p class="font-medium">{{ $user->renewal_expires_at?->format('d M Y') }}</p>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-3">
                        @if ($memberCard)
                            <a href="{{ route('member.documents.card') }}" class="px-4 py-2 text-sm rounded-sm bg-nbbeu-navy text-white hover:bg-nbbeu-gold hover:text-nbbeu-navy-deep">
                                Download Member Card
                            </a>
                        @endif
                        @if ($certificate)
                            <a href="{{ route('member.documents.certificate') }}" class="px-4 py-2 text-sm rounded-sm bg-nbbeu-navy text-white hover:bg-nbbeu-gold hover:text-nbbeu-navy-deep">
                                Download Certificate
                            </a>
                        @endif
                    </div>

                    @if ($renewalDue)
                        <div class="mt-4 text-sm bg-amber-50 rounded-md p-3">
                            <p class="text-amber-700">
                                Your membership will expire / has expired on {{ $user->renewal_expires_at?->format('d M Y') }}.
                            </p>
                            <form method="POST" action="{{ route('member.renewal') }}" class="mt-2">
                                @csrf
                                <button type="submit" class="px-4 py-2 text-sm rounded-sm bg-nbbeu-gold text-nbbeu-navy-deep font-medium">
                                    Renew Now
                                </button>
                            </form>
                        </div>
                    @endif
                @endif
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <h3 class="font-medium text-gray-900">Profile Details</h3>
                <p class="mt-2 text-sm text-gray-600">{{ $user->name }} &bull; {{ $user->email }} &bull; {{ $user->phone }} &bull; {{ $user->company }}</p>
                <a href="{{ route('profile.edit') }}" class="mt-3 inline-block text-sm underline text-gray-600 hover:text-gray-900">
                    Update profile
                </a>
            </div>
        </div>
    </div>
</x-app-layout>

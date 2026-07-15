<x-guest-layout>
    <h1 class="text-lg font-semibold mb-4">Application Status</h1>

    <form method="GET" action="{{ route('registration.status') }}">
        <x-input-label for="email" value="Email" />
        <div class="flex gap-2 mt-1">
            <x-text-input id="email" class="block w-full" type="email" name="email" :value="$email" placeholder="name@email.com" required />
            <x-primary-button>Check</x-primary-button>
        </div>
    </form>

    @if ($email)
        <div class="mt-6 border-t pt-4">
            @if (! $user)
                <p class="text-sm text-gray-600">No application found for this email.</p>
            @else
                @php
                    $statusLabel = match ($user->status) {
                        'pending' => 'Pending Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        default => $user->status,
                    };
                    $paymentLabel = match ($payment?->status) {
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        default => 'Awaiting Payment',
                    };
                @endphp

                <dl class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Name</dt>
                        <dd class="font-medium">{{ $user->name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Payment Status</dt>
                        <dd class="font-medium">{{ $paymentLabel }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Application Status</dt>
                        <dd class="font-medium">{{ $statusLabel }}</dd>
                    </div>
                    @if ($user->status === 'rejected' && $user->rejection_reason)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Reason</dt>
                            <dd class="font-medium">{{ $user->rejection_reason }}</dd>
                        </div>
                    @endif
                    @if ($user->member_no)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Member No.</dt>
                            <dd class="font-medium">{{ $user->member_no }}</dd>
                        </div>
                    @endif
                </dl>

                @if ($billUrl)
                    <a href="{{ $billUrl }}" class="inline-block mt-4 text-sm underline text-gray-600 hover:text-gray-900">
                        Continue / check payment
                    </a>
                @endif
            @endif
        </div>
    @endif
</x-guest-layout>

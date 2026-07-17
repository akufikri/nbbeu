<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Renewal') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div @class([
                'overflow-hidden shadow-sm rounded-lg p-6',
                'bg-red-50 border-2 border-red-300' => $renewalDue,
                'bg-white' => ! $renewalDue,
            ])>
                @if ($user->status !== 'approved')
                    <p class="text-sm text-gray-600">Renewal is only available for approved members.</p>
                @else
                    <p class="text-gray-500 text-sm">Valid Until</p>
                    <p class="font-medium text-lg">{{ $user->renewal_expires_at?->format('d M Y') }}</p>

                    @if ($renewalDue)
                        <p class="mt-3 text-2xl font-bold {{ $daysUntilExpiry > 0 ? 'text-amber-700' : 'text-red-700' }}">
                            @if ($daysUntilExpiry > 0)
                                Expires in {{ $daysUntilExpiry }} {{ Str::plural('day', $daysUntilExpiry) }}
                            @else
                                Expired {{ abs($daysUntilExpiry) }} {{ Str::plural('day', abs($daysUntilExpiry)) }} ago
                            @endif
                        </p>
                        <p class="mt-1 text-sm text-gray-600">Renew now to keep your membership active without interruption.</p>
                    @endif

                    <form method="POST" action="{{ route('member.renewal') }}" class="mt-4">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center px-8 py-3.5 text-sm font-medium leading-none text-white whitespace-nowrap rounded-md bg-nbbeu-navy text-nbbeu-navy-deep hover:opacity-90">
                            Renew Now
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

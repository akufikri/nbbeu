<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Member Card') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                @if (! $memberCard)
                    <p class="text-sm text-gray-600">Your member card has not been generated yet.</p>
                @else
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                        <img src="{{ $qrDataUri }}" alt="Member card QR code" class="w-40 h-40 border border-gray-200 rounded-md p-2">
                        <div>
                            <p class="text-gray-500 text-sm">Member No.</p>
                            <p class="font-mono font-medium">{{ $memberCard->card_number }}</p>
                            <p class="text-gray-500 text-sm mt-3">Valid Until</p>
                            <p class="font-medium">{{ $memberCard->expires_at?->format('d M Y') }}</p>
                            <a href="{{ route('member.documents.card') }}" class="mt-4 inline-block px-4 py-2 text-sm rounded-sm bg-nbbeu-navy text-white hover:bg-nbbeu-gold hover:text-nbbeu-navy-deep">
                                Download Member Card (PDF)
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

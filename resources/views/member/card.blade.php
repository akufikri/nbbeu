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
                    <div class="flex flex-col items-center">
                        {{-- Card preview: image template + text overlay --}}
                        <div class="relative w-64" style="aspect-ratio: 845 / 985;">
                            <img src="{{ asset('assets/illustrations/front-kad-ahli-new.png') }}"
                                 alt="Member card"
                                 class="w-full h-full object-cover rounded-xl shadow-xl">
                            {{-- Text overlay in cream section (~top 66.6%) --}}
                            <div class="absolute inset-x-0 flex flex-col items-center text-center px-3"
                                 style="top: 69.5%; gap: 4%;">
                                <p class="font-bold leading-tight text-xs tracking-wide" style="color:#3B2500;">
                                    {{ strtoupper($user->name) }}
                                </p>
                                <p class="text-xs" style="color:#3B2500; margin-top:4%;">
                                    Member ID {{ $memberCard->card_number }}
                                </p>
                                <p class="text-xs" style="color:#5C4000; margin-top:4%;">
                                    www.nbbeu.org.my
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 grid sm:grid-cols-2 gap-4 text-sm w-64">
                            <div>
                                <p class="text-gray-500">Member No.</p>
                                <p class="font-mono font-medium">{{ $memberCard->card_number }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Valid Until</p>
                                <p class="font-medium">{{ $memberCard->expires_at?->format('d M Y') }}</p>
                            </div>
                        </div>

                        <a href="{{ route('member.documents.card') }}"
                           class="mt-4 inline-block px-4 py-2 text-sm rounded-sm bg-nbbeu-navy text-white hover:bg-nbbeu-gold hover:text-nbbeu-navy-deep">
                            Download Member Card
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

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
                <di class="flex items-center justify-center">
                        <div>
                        <div class="flex flex-wrap gap-6">
                            <div class="w-full max-w-xl" style="container-type: inline-size;">
                                <div
                                    class="relative w-full rounded-lg overflow-hidden shadow-md"
                                    style="aspect-ratio: 340 / 214; background-image: url('{{ asset('assets/illustrations/front-kad-ahli.png') }}'); background-size: cover;"
                                >
                                    <div class="absolute overflow-hidden" style="left: 69%; top: 16.82%; width: 20.30%; height: 35.42%;">
                                        @if ($photoUrl)
                                            <img src="{{ $photoUrl }}" alt="" class="w-full h-full object-cover">
                                        @endif
                                    </div>

                                    <div class="absolute overflow-hidden whitespace-nowrap text-ellipsis" style="left: 16.41%; width: 32%; top: 17.5%; font-size: 2.6cqw; color: #16305C;">
                                        {{ $user->name }}
                                    </div>
                                    <div class="absolute overflow-hidden whitespace-nowrap text-ellipsis" style="left: 16.41%; width: 32%; top: 30.1%; font-size: 2.6cqw; color: #16305C;">
                                        {{ $user->phone }}
                                    </div>
                                    <div class="absolute overflow-hidden whitespace-nowrap text-ellipsis" style="left: 16.41%; width: 32%; top: 43.1%; font-size: 2.6cqw; color: #16305C;">
                                        {{ $user->email }}
                                    </div>
                                    <div class="absolute overflow-hidden whitespace-nowrap text-ellipsis" style="left: 16.41%; width: 32%; top: 56.4%; font-size: 2.6cqw; color: #16305C;">
                                        {{ $location }}
                                    </div>

                                    <div class="absolute" style="left: 72.5%; top: 54.5%; width: 12%;">
                                        <img src="{{ $qrDataUri }}" alt="Member card QR code" class="w-full h-auto">
                                    </div>
                                </div>
                            </div>

                            <div class="w-full max-w-xl">
                                <img src="{{ asset('assets/illustrations/back-kad-ahli.png') }}" alt="Member card back" class="w-full h-auto rounded-lg shadow-md" style="aspect-ratio: 340 / 214;">
                            </div>
                        </div>

                        <div class="mt-6 grid sm:grid-cols-2 gap-4 text-sm max-w-xl">
                            <div>
                                <p class="text-gray-500">Member No.</p>
                                <p class="font-mono font-medium">{{ $memberCard->card_number }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Valid Until</p>
                                <p class="font-medium">{{ $memberCard->expires_at?->format('d M Y') }}</p>
                            </div>
                        </div>

                        <a href="{{ route('member.documents.card') }}" class="mt-4 inline-block px-4 py-2 text-sm rounded-sm bg-nbbeu-navy text-white hover:bg-nbbeu-gold hover:text-nbbeu-navy-deep">
                            Download Member Card
                        </a>
                    </div>
                </di>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

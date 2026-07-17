<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Certificate') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                @if (! $certificate)
                    <p class="text-sm text-gray-600">Your certificate has not been generated yet.</p>
                @else
                    <p class="text-gray-500 text-sm">Certificate No.</p>
                    <p class="font-mono font-medium">{{ $certificate->cert_number }}</p>
                    <p class="text-gray-500 text-sm mt-3">Issued</p>
                    <p class="font-medium">{{ $certificate->issued_at?->format('d M Y') }}</p>
                    <a href="{{ route('member.documents.certificate') }}" class="mt-4 inline-block px-4 py-2 text-sm rounded-sm bg-nbbeu-navy text-white hover:bg-nbbeu-gold hover:text-nbbeu-navy-deep">
                        Download Certificate (PDF)
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

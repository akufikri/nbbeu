<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Iuran Bulanan (Monthly Union Dues)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                @if (session('status') === 'union-dues-created')
                    <p class="mb-4 text-sm text-green-700 bg-green-50 rounded-md p-3">
                        Your consent letter has been generated. Download it below.
                    </p>
                @endif

                @if (! $hasMemberProfile)
                    <p class="text-sm text-amber-700 bg-amber-50 rounded-md p-3">
                        Complete your profile first. This feature requires details (IC number, employer)
                        that were not captured for your account. Please contact the secretariat.
                    </p>
                @elseif (! $mandate)
                    <h3 class="font-medium text-gray-900">Set Up Salary Deduction</h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Enter the monthly amount you wish to have deducted from your salary in favour of NBBEU.
                        A consent letter will be generated for you to download, sign, and submit to your employer's
                        HR/payroll department yourself. NBBEU does not process this automatically.
                    </p>

                    <form method="POST" action="{{ route('member.union-dues.store') }}" class="mt-4 max-w-xs space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="deduction_amount" :value="__('Deduction Amount (RM)')" />
                            <x-text-input id="deduction_amount" name="deduction_amount" type="number" step="0.01" min="1" class="mt-1 block w-full" :value="old('deduction_amount')" required />
                            <x-input-error class="mt-2" :messages="$errors->get('deduction_amount')" />
                        </div>
                        <button type="submit" class="px-4 py-2 text-sm rounded-sm bg-nbbeu-navy text-white hover:bg-nbbeu-gold hover:text-nbbeu-navy-deep">
                            Generate Consent Letter
                        </button>
                    </form>
                @else
                    <h3 class="font-medium text-gray-900">Your Union Dues Mandate</h3>

                    <div class="mt-3 grid sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Status</p>
                            @if ($mandate->status === 'active')
                                <span class="px-2 py-1 text-xs rounded-full bg-green-50 text-green-700">Active</span>
                            @elseif ($mandate->status === 'cancelled')
                                <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-600">Cancelled</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-amber-50 text-amber-700">Awaiting Submission to HR</span>
                            @endif
                        </div>
                        <div>
                            <p class="text-gray-500">Deduction Amount</p>
                            <p class="font-medium">RM {{ number_format($mandate->deduction_amount, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Consent Signed At</p>
                            <p class="font-medium">{{ $mandate->consent_signed_at?->format('d M Y') ?? '-' }}</p>
                        </div>
                    </div>

                    <p class="mt-4 text-sm text-gray-600">
                        Download this letter, sign it, and submit it yourself to your employer's HR/payroll
                        department. NBBEU does not process this automatically.
                    </p>

                    @if ($mandate->consent_file_path)
                        <a href="{{ route('member.union-dues.download', $mandate) }}" class="mt-3 inline-block px-4 py-2 text-sm rounded-sm bg-nbbeu-navy text-white hover:bg-nbbeu-gold hover:text-nbbeu-navy-deep">
                            Download Consent Letter
                        </a>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

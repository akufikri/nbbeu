<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Payment History') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                <h3 class="font-medium text-gray-900">Payments</h3>

                @if ($payments->isEmpty())
                    <p class="mt-2 text-sm text-gray-600">No payments found.</p>
                @else
                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-sm text-left">
                            <thead>
                                <tr class="text-gray-500 border-b">
                                    <th class="py-2 pr-4">Jumlah</th>
                                    <th class="py-2 pr-4">Tujuan</th>
                                    <th class="py-2 pr-4">Status</th>
                                    <th class="py-2 pr-4">Tarikh Bayar</th>
                                    <th class="py-2 pr-4">Resit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($payments as $payment)
                                    <tr class="border-b last:border-0">
                                        <td class="py-2 pr-4">RM {{ number_format($payment->amount, 2) }}</td>
                                        <td class="py-2 pr-4">{{ $payment->purpose }}</td>
                                        <td class="py-2 pr-4">
                                            @if ($payment->status === 'paid')
                                                <span class="px-2 py-1 text-xs rounded-full bg-green-50 text-green-700">Paid</span>
                                            @elseif ($payment->status === 'failed')
                                                <span class="px-2 py-1 text-xs rounded-full bg-red-50 text-red-700">Failed</span>
                                            @else
                                                <span class="px-2 py-1 text-xs rounded-full bg-amber-50 text-amber-700">Pending</span>
                                            @endif
                                        </td>
                                        <td class="py-2 pr-4">{{ $payment->paid_at?->format('d M Y') ?? '-' }}</td>
                                        <td class="py-2 pr-4">
                                            @if ($payment->status === 'paid')
                                                <a href="{{ route('member.payments.receipt', $payment) }}"
                                                   class="text-xs text-blue-600 hover:underline" target="_blank">
                                                    Muat Turun
                                                </a>
                                            @else
                                                <span class="text-xs text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $payments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

<x-site-layout
    title="Card Verification — NBBEU"
    description="Verify an NBBEU member card."
    :hide-nav="true"
>
    <section class="page-header">
        <div class="max-w-7xl mx-auto px-6">
            <h1>NBBEU Member Card Verification</h1>
        </div>
    </section>

    <section class="py-16">
        <div class="max-w-7xl mx-auto px-6">
            @if (! $result)
                <div class="rounded-md bg-red-50 p-4 text-sm text-red-700">
                    Card not found / invalid.
                </div>
            @elseif ($result['expired'])
                <div class="rounded-md bg-amber-50 p-4 text-sm text-amber-700">
                    The card for <strong>{{ $result['name'] }}</strong> ({{ $result['member_no'] }}) has <strong>Expired</strong>.
                </div>
            @else
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700">
                    <p class="font-semibold">Status: Valid</p>
                    <p class="mt-2">Name: {{ $result['name'] }}</p>
                    <p>Member No.: {{ $result['member_no'] }}</p>
                </div>
            @endif
        </div>
    </section>
</x-site-layout>

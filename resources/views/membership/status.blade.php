<x-site-layout
    title="Application Status — NBBEU"
    description="Check the status of your NBBEU membership application."
    :hide-nav="true"
>
    <section class="page-header">
        <div class="max-w-7xl mx-auto px-6">
            <a href="{{ route('registration.create') }}" class="page-header__crumb">← Back to Registration</a>
            <h1>Application Status</h1>
            <p>Enter the email you registered with to check your membership application status.</p>
        </div>
    </section>

    <section class="py-16">
        <form class="form mx-auto" method="GET" action="{{ route('registration.status') }}">
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ $email }}" placeholder="name@email.com" required>
            </div>

            <button type="submit" class="btn-submit">Check Status</button>
        </form>

        @if ($email)
            <div class="max-w-7xl mx-auto px-6 mt-10">
                <div class="form mx-auto">
                    @if (! $user)
                        <p class="text-nb-ink-muted font-sans text-sm">No application found for this email.</p>
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

                        <dl class="font-sans text-sm space-y-3">
                            <div class="flex justify-between items-center pb-3 border-b border-nb-rule">
                                <dt class="text-nb-ink-muted">Name</dt>
                                <dd class="font-medium text-nb-ink">{{ $user->name }}</dd>
                            </div>
                            <div class="flex justify-between items-center pb-3 border-b border-nb-rule">
                                <dt class="text-nb-ink-muted">Payment Status</dt>
                                <dd class="font-medium text-nb-ink">{{ $paymentLabel }}</dd>
                            </div>
                            <div class="flex justify-between items-center pb-3 border-b border-nb-rule">
                                <dt class="text-nb-ink-muted">Application Status</dt>
                                <dd class="font-medium text-nb-ink">{{ $statusLabel }}</dd>
                            </div>
                            @if ($user->status === 'rejected' && $user->rejection_reason)
                                <div class="flex justify-between items-center pb-3 border-b border-nb-rule">
                                    <dt class="text-nb-ink-muted">Reason</dt>
                                    <dd class="font-medium text-nb-ink">{{ $user->rejection_reason }}</dd>
                                </div>
                            @endif
                            @if ($user->member_no)
                                <div class="flex justify-between items-center pb-3 border-b border-nb-rule">
                                    <dt class="text-nb-ink-muted">Member No.</dt>
                                    <dd class="font-medium text-nb-ink">{{ $user->member_no }}</dd>
                                </div>
                            @endif
                        </dl>

                        @if ($billUrl)
                            <a href="{{ $billUrl }}" class="cta-text mt-6 inline-flex">Continue / check payment →</a>
                        @endif
                    @endif
                </div>
            </div>
        @endif
    </section>
</x-site-layout>

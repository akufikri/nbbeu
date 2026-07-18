<x-site-layout
    title="Membership Registration — NBBEU"
    description="Complete the 6-step application below. Once submitted, NBBEU will review your application and email you a payment link after approval."
    :hide-nav="true"
>
    <section class="page-header">
        <div class="max-w-7xl mx-auto px-6">
            <a href="{{ route('home') }}" class="page-header__crumb">← Back to Home</a>
            <h1>NBBEU Membership Registration</h1>
            <p>Complete the 6-step application below. Once submitted, NBBEU will review your application and email you a payment link after approval.</p>
        </div>
    </section>

    <section class="py-16">
        <livewire:registration-wizard />
    </section>
</x-site-layout>

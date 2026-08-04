<x-site-layout
    title="Collective Agreement — NBBEU"
    description="NBBEU's collective agreement with participating banking institutions."
>
    <section class="page-header">
        <div class="max-w-7xl mx-auto px-6">
            <a href="{{ route('home') }}" class="page-header__crumb">← Back to Home</a>
            <h1>Collective Agreement</h1>
        </div>
    </section>

    <section class="py-16">
        <div class="article-body clause mx-auto px-6">
            @if ($text)
                <div class="clause__item">
                    <p>{!! nl2br(e($text)) !!}</p>
                </div>
            @else
                <p class="text-nb-ink-muted font-sans text-sm">Collective agreement details will be published here soon.</p>
            @endif
        </div>
    </section>
</x-site-layout>

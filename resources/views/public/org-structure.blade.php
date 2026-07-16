<x-site-layout
    title="Organization Structure — NBBEU"
    description="The NBBEU Executive Board, elected through member deliberation to guide the strategic direction of the union."
>
    <section class="page-header">
        <div class="max-w-7xl mx-auto px-6">
            <a href="{{ route('home') }}" class="page-header__crumb">← Back to Home</a>
            <h1>Organization Structure</h1>
            <p>The NBBEU Executive Board, elected through member deliberation to guide the strategic direction of the union.</p>
        </div>
    </section>

    <section class="py-16">
        <div class="max-w-7xl mx-auto px-6">
            <div class="head-numbered scroll-reveal">
                <span class="head-numbered__label">01 — Executive</span>
                <div>
                    <h2>Executive Board</h2>
                    <p>Responsible for daily operations and official representation of the union.</p>
                </div>
            </div>

            @if ($orgChart->isEmpty())
                <p class="text-nb-ink-muted font-sans text-sm">Organization structure coming soon.</p>
            @else
                <div class="directory scroll-reveal">
                    @foreach ($orgChart as $person)
                        <div class="directory__item">
                            <div class="directory__mono">
                                @if ($person->photo)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($person->photo) }}" alt="{{ $person->name }}">
                                @else
                                    {{ collect(explode(' ', $person->name))->map(fn ($part) => mb_substr($part, 0, 1))->join('') }}
                                @endif
                            </div>
                            <div>
                                <h4>{{ $person->name }}</h4>
                                <p class="role">{{ $person->position }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-site-layout>

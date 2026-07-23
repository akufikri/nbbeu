<x-site-layout
    title="Struktur Kepimpinan 2025-2027 — NBBEU"
    description="Struktur Kepimpinan NBBEU 2025-2027, dipilih melalui musyawarah ahli untuk membimbing hala tuju strategik kesatuan."
>
    <section class="page-header">
        <div class="max-w-7xl mx-auto px-6">
            <a href="{{ route('home') }}" class="page-header__crumb">← Kembali ke Laman Utama</a>
            <h1>Struktur Kepimpinan 2025-2027</h1>
            <p>Bertanggungjawab terhadap operasi harian dan perwakilan rasmi kesatuan.</p>
        </div>
    </section>

    <section class="py-16">
        <div class="max-w-7xl mx-auto px-6">
            <div class="head-numbered scroll-reveal">
                <span class="head-numbered__label">01 — Eksekutif</span>
                <div>
                    <h2>Jawatankuasa Eksekutif</h2>
                    <p>Bertanggungjawab terhadap operasi harian dan perwakilan rasmi kesatuan.</p>
                </div>
            </div>

            @if ($orgChart->isEmpty())
                <p class="text-nb-ink-muted font-sans text-sm">Struktur organisasi akan dikemaskini tidak lama lagi.</p>
            @elseif ($orgChartTree->count() === 1)
                <div id="org-chart-tree" class="org-chart-tree scroll-reveal" data-tree="{{ json_encode($orgChartTree->first()) }}"></div>
            @else
                <div class="directory scroll-reveal">
                    @foreach ($orgChart as $person)
                        <div class="directory__item">
                            <div class="directory__mono">
                                @if ($person->photo)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('cloudinary')->url($person->photo) }}" alt="{{ $person->name }}">
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

    @if ($orgChartTree->count() === 1)
        @push('scripts')
            <link rel="stylesheet" href="{{ asset('assets/vendor/orgchart/jquery.orgchart.min.css') }}">
            <script src="{{ asset('assets/vendor/orgchart/jquery.orgchart.min.js') }}"></script>
            <style>
                /* Read-only public display: no collapse/edit controls, larger scale, full-width centered, no grid backdrop */
                .org-chart-tree { zoom: 1.6; width: 100%; text-align: center; }
                .org-chart-tree .toggleBtn { display: none !important; }
                .org-chart-tree .orgchart { background-image: none; }
                .org-chart-tree .node .title { background-color: var(--color-primary) !important; border-color: var(--color-primary) !important; }
                .org-chart-tree .node .content { border-color: var(--color-primary) !important; }
                .org-chart-tree .node::before { background-color: var(--color-primary) !important; }
                .org-chart-tree .hierarchy::before,
                .org-chart-tree .nodes.vertical .hierarchy::after,
                .org-chart-tree .nodes.vertical .hierarchy::before { border-color: var(--color-primary) !important; }
            </style>
            <script>
                $(function () {
                    const treeData = JSON.parse(document.getElementById('org-chart-tree').dataset.tree);
                    $('#org-chart-tree').orgchart({
                        data: treeData,
                        nodeContent: 'title',
                        toggleSiblingsResp: false,
                    });
                    // Read-only display — collapse/expand isn't offered on the public page.
                    $('#org-chart-tree .toggleBtn').remove();
                });
            </script>
        @endpush
    @endif
</x-site-layout>

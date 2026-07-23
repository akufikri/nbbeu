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
                .org-chart-tree { zoom: 1.5; width: 100%; text-align: center; }
                .org-chart-tree .toggleBtn { display: none !important; }
                .org-chart-tree .orgchart { background-image: none; }

                /* Vendor default is a fixed 130x20px single-line title/content — too small
                   once a name wraps to two lines or an avatar is added, so it's widened
                   and allowed to grow here instead of clipping with an ellipsis. */
                .org-chart-tree .node .title,
                .org-chart-tree .node .content { width: 130px; }
                .org-chart-tree .node .title {
                    background-color: var(--color-primary) !important;
                    border-color: var(--color-primary) !important;
                    height: auto; min-height: 24px; white-space: normal; overflow: visible; text-overflow: clip;
                    display: flex; align-items: center; justify-content: center; gap: 0.3rem;
                    padding: 0.25rem 0.35rem; line-height: 1.15; font-size: 11px;
                }
                .org-chart-tree .node .content { border-color: var(--color-primary) !important; }
                .org-chart-tree .node::before { background-color: var(--color-primary) !important; }
                .org-chart-tree .hierarchy::before,
                .org-chart-tree .nodes.vertical .hierarchy::after,
                .org-chart-tree .nodes.vertical .hierarchy::before { border-color: var(--color-primary) !important; }

                .org-chart-tree .node .title__avatar {
                    flex: none; width: 18px; height: 18px; border-radius: 50%; object-fit: cover;
                }
                .org-chart-tree .node .title__avatar--initials {
                    display: flex; align-items: center; justify-content: center;
                    background: rgba(255, 255, 255, 0.15); color: #fff;
                    font-size: 0.55rem; font-weight: 600;
                }
                .org-chart-tree .node .title__name { text-align: left; }
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

                    // The orgchart library has no built-in photo/avatar support — it only
                    // renders name/title text. Inject a photo (or an initials placeholder,
                    // matching the flat directory view) into each node's title bar, reading
                    // back the node's original data that the library stashes via .data('nodeData').
                    $('#org-chart-tree .node').each(function () {
                        const nodeData = $(this).data('nodeData');
                        if (!nodeData) return;

                        const $title = $(this).find('.title');
                        const name = $title.text().trim();
                        $title.empty();

                        if (nodeData.img) {
                            $title.append($('<img>').attr('src', nodeData.img).addClass('title__avatar'));
                        } else {
                            const initials = name.split(' ').map((part) => part.charAt(0)).slice(0, 2).join('');
                            $title.append(
                                $('<span>').addClass('title__avatar title__avatar--initials').text(initials)
                            );
                        }

                        $title.append($('<span>').addClass('title__name').text(name));
                    });
                });
            </script>
        @endpush
    @endif
</x-site-layout>

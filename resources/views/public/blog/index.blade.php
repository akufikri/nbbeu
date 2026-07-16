<x-site-layout
    title="News &amp; Publications — NBBEU"
    description="In-depth analysis of financial dynamics, regulatory policy, and official corporate publications from the NBBEU secretariat."
>
    <section class="page-header">
        <div class="max-w-7xl mx-auto px-6">
            <a href="{{ route('home') }}" class="page-header__crumb">← Back to Home</a>
            <h1>News &amp; Publications</h1>
            <p>In-depth analysis of financial dynamics, regulatory policy, and official corporate publications from the NBBEU secretariat.</p>
        </div>
    </section>

    <section class="py-16">
        <div class="max-w-7xl mx-auto px-6">
            <div class="blog-toolbar">
                <div class="field field--search">
                    <label for="blog-search">Search Articles</label>
                    <input type="search" id="blog-search" value="{{ $search }}" placeholder="Search title or topic… (min. 3 characters)" autocomplete="off">
                </div>
                <div class="filter-chips" id="blog-filter-chips" role="group" aria-label="Filter categories">
                    <a href="{{ route('blog.index', array_filter(['q' => $search])) }}" data-category="" class="filter-chip @if (! $category) is-active @endif">All</a>
                    @foreach (\App\Models\Post::CATEGORIES as $key => $label)
                        <a href="{{ route('blog.index', array_filter(['q' => $search, 'category' => $key])) }}" data-category="{{ $key }}" class="filter-chip @if ($category === $key) is-active @endif">{{ $label }}</a>
                    @endforeach
                </div>
            </div>

            <div id="blog-results" data-blog-results-url="{{ route('blog.index') }}">
                @include('public.blog._results', ['posts' => $posts, 'search' => $search, 'category' => $category])
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            $(function () {
                const $input = $('#blog-search');
                const $results = $('#blog-results');
                const $chips = $('#blog-filter-chips .filter-chip');
                const resultsUrl = $results.data('blog-results-url');
                let activeCategory = $chips.filter('.is-active').data('category') || '';
                let debounceTimer = null;

                function runSearch() {
                    const q = $input.val().trim();
                    if (q.length > 0 && q.length < 3) return;

                    $.get(resultsUrl, { q: q, category: activeCategory })
                        .done(function (html) {
                            $results.html(html);
                        });

                    const params = new URLSearchParams();
                    if (q) params.set('q', q);
                    if (activeCategory) params.set('category', activeCategory);
                    const newUrl = resultsUrl + (params.toString() ? '?' + params.toString() : '');
                    window.history.replaceState({}, '', newUrl);
                }

                $input.on('input', function () {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(runSearch, 300);
                });

                $chips.on('click', function (e) {
                    e.preventDefault();
                    $chips.removeClass('is-active');
                    $(this).addClass('is-active');
                    activeCategory = $(this).data('category') || '';
                    runSearch();
                });
            });
        </script>
    @endpush
</x-site-layout>

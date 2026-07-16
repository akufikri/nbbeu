@if ($posts->isEmpty())
    <p id="blog-empty" class="text-nb-ink-muted font-sans text-sm">
        @if ($search || $category)
            No articles match your search.
        @else
            No articles published yet.
        @endif
    </p>
@else
    <div class="index-list scroll-reveal revealed">
        @foreach ($posts as $post)
            <article class="index-list__row">
                <div class="index-list__thumb">
                    @if ($post->cover_image)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($post->cover_image) }}" alt="{{ $post->title }}">
                    @else
                        Photo 16:10
                    @endif
                </div>
                <div>
                    @if ($post->category)
                        <span class="index-list__date">{{ \App\Models\Post::CATEGORIES[$post->category] ?? $post->category }}</span>
                    @endif
                    <h3><a href="{{ route('blog.show', $post) }}">{{ $post->title }}</a></h3>
                    <p>{{ $post->excerpt }}</p>
                    <span class="index-list__date">{{ $post->published_at?->format('F Y') }}</span>
                </div>
            </article>
        @endforeach
    </div>

    <div class="pagination-wrap">
        {{ $posts->links() }}
    </div>
@endif

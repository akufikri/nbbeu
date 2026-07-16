<x-site-layout
    :title="$post->title . ' — NBBEU'"
    :description="$post->excerpt ?? $post->title"
>
    <article class="py-16 px-6">
        <div class="max-w-7xl mx-auto">
        <div class="article-body mx-auto">
            <a href="{{ route('blog.index') }}" class="page-header__crumb">← Back to News</a>
            <p class="article-meta mt-6">
                {{ $post->published_at?->format('F Y') }} · By {{ $post->author->name }}
            </p>
            <h1 class="mt-3">{{ $post->title }}</h1>

            @if ($post->hero_image || $post->cover_image)
                <div class="article-hero-image mt-8">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($post->hero_image ?? $post->cover_image) }}" alt="{{ $post->title }}">
                </div>
            @endif

            <div class="mt-8">
                {!! $post->content !!}
            </div>
        </div>
        </div>
    </article>
</x-site-layout>

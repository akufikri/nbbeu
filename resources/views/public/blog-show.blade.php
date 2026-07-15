<x-webflow-layout
    :title="$post->title . ' — NBBEU'"
    :description="$post->excerpt ?? $post->title"
    ogType="article"
>
    <section class="section_service">
        <div class="padding-global padding-section-medium">
            <div class="container-large">
                <a
                    href="{{ route('blog.index') }}"
                    class="align-auto w-inline-block"
                    ><div class="lean-more">
                        <div>&larr; Back to Blog</div>
                        <div class="line-more_line"></div>
                    </div></a
                >
                <div class="spacer-large"></div>
                <div class="max-width-medium">
                    @if ($post->published_at)
                        <div class="batch_list">
                            <div class="batch">
                                <div class="text-sm">
                                    {{ $post->published_at->format('d M Y') }}
                                </div>
                            </div>
                        </div>
                        <div class="spacer-medium"></div>
                    @endif
                    <h1 class="">{{ $post->title }}</h1>
                    <div class="spacer-medium"></div>
                    <div class="text-color-secondary">
                        By {{ $post->author->name }}
                    </div>
                </div>
                <div class="spacer-huge"></div>
                @if ($post->cover_image)
                    <div class="service_img">
                        <div class="img-wrapper">
                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::url($post->cover_image) }}"
                                loading="lazy"
                                sizes="(max-width: 767px) 100vw, 768px"
                                alt="{{ $post->title }}"
                                class="img"
                            />
                        </div>
                    </div>
                    <div class="spacer-huge"></div>
                @endif
                <div class="max-width-medium">
                    <div class="w-richtext text-color-secondary">
                        {!! $post->content !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-webflow-layout>

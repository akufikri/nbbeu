<x-webflow-layout
    title="Blog — NBBEU"
    description="News and insights from NBBEU — North Borneo Banking Executive Union."
>
    <section class="section_service">
        <div class="padding-global padding-section-medium">
            <div class="container-large">
                <div class="max-width-medium">
                    <div class="tag">Blog</div>
                    <div class="spacer-medium"></div>
                    <h1 class="">News &amp; Insights</h1>
                    <div class="spacer-medium"></div>
                    <div class="text-color-secondary">
                        Updates, announcements, and perspectives from
                        NBBEU — North Borneo Banking Executive Union.
                    </div>
                </div>
                <div class="spacer">
                    <div
                        style="height: 4rem"
                        class="spacer-desktop"
                    ></div>
                    <div
                        style="height: 3rem"
                        class="spacer-tablet"
                    ></div>
                    <div
                        style="height: 2rem"
                        class="spacer-mobile"
                    ></div>
                </div>
                @if ($posts->isEmpty())
                    <div class="text-color-secondary">
                        No posts yet.
                    </div>
                @else
                    <div class="w-dyn-list">
                        <div role="list" class="blog_list w-dyn-items">
                            @foreach ($posts as $post)
                                <div
                                    role="listitem"
                                    class="blog_item w-dyn-item"
                                >
                                    <a
                                        href="{{ route('blog.show', $post) }}"
                                        class="w-inline-block"
                                        ><div class="thumbnail_img">
                                            <div class="img-wrapper">
                                                <img
                                                    loading="lazy"
                                                    src="{{ $post->cover_image ? \Illuminate\Support\Facades\Storage::url($post->cover_image) : asset('webflow/cdn.prod.website-files.com/684f91df71b424da63a64df2/6855af9f35a779340c720572_image-1.png') }}"
                                                    alt="{{ $post->title }}"
                                                    sizes="100vw"
                                                    class="img"
                                                />
                                            </div>
                                        </div>
                                        <div class="spacer-small"></div>
                                        <div class="wrap-padding-small">
                                            @if ($post->published_at)
                                                <div class="batch_list">
                                                    <div class="batch">
                                                        <div class="text-sm">
                                                            {{ $post->published_at->format('d M Y') }}
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="spacer-small"></div>
                                            @endif
                                            <h3 class="text-xl">
                                                {{ $post->title }}
                                            </h3>
                                            <div class="spacer-small"></div>
                                            <div
                                                class="text-color-secondary"
                                            >
                                                {{ $post->excerpt }}
                                            </div>
                                            <div
                                                class="spacer-xlarge"
                                            ></div>
                                            <div class="align-auto">
                                                <div class="lean-more">
                                                    <div>Learn more</div>
                                                    <div
                                                        class="line-more_line"
                                                    ></div>
                                                </div>
                                            </div></div
                                    ></a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="spacer">
                        <div
                            style="height: 4rem"
                            class="spacer-desktop"
                        ></div>
                        <div
                            style="height: 3rem"
                            class="spacer-tablet"
                        ></div>
                        <div
                            style="height: 2rem"
                            class="spacer-mobile"
                        ></div>
                    </div>
                    <div class="text-color-secondary">
                        {{ $posts->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-webflow-layout>

<x-site-layout
    title="Galeri — NBBEU"
    description="Galeri foto aktiviti dan program North Borneo Banking Executive Union (NBBEU)."
>
    <section class="page-header">
        <div class="max-w-7xl mx-auto px-6">
            <a href="{{ route('home') }}" class="page-header__crumb">← Kembali ke Laman Utama</a>
            <h1>Galeri</h1>
            <p>Koleksi foto aktiviti dan program NBBEU.</p>
        </div>
    </section>

    <section class="py-16">
        <div class="max-w-7xl mx-auto px-6">
            @if ($categories->isNotEmpty())
                <div class="filter-chips mb-8" role="group" aria-label="Filter kategori">
                    <a href="{{ route('gallery.index') }}" class="filter-chip @if (! $category) is-active @endif">Semua</a>
                    @foreach ($categories as $key)
                        <a href="{{ route('gallery.index', ['category' => $key]) }}" class="filter-chip @if ($category === $key) is-active @endif">{{ $key }}</a>
                    @endforeach
                </div>
            @endif

            @if ($items->isEmpty())
                <p class="text-nb-ink-muted font-sans text-sm">Belum ada foto galeri.</p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 scroll-reveal">
                    @foreach ($items as $item)
                        <div>
                            <button
                                type="button"
                                class="gallery-thumb block w-full aspect-square rounded-md overflow-hidden bg-nb-paper-final"
                                data-full="{{ \Illuminate\Support\Facades\Storage::disk('cloudinary')->url($item->image) }}"
                                data-caption="{{ $item->title }}"
                            >
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('cloudinary')->url($item->image) }}" alt="{{ $item->title }}" class="w-full h-full object-cover hover:opacity-90 transition">
                            </button>
                            @if ($item->title || $item->category)
                                <div class="mt-2">
                                    @if ($item->title)
                                        <p class="text-sm font-medium text-nb-ink">{{ $item->title }}</p>
                                    @endif
                                    @if ($item->category)
                                        <p class="text-xs text-nb-ink-muted">{{ $item->category }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </section>

    <div id="gallery-lightbox" class="fixed inset-0 z-[999] hidden items-center justify-center bg-black/80 p-6" role="dialog" aria-modal="true">
        <button type="button" id="gallery-lightbox-close" class="absolute top-6 right-6 text-white text-3xl leading-none" aria-label="Close">&times;</button>
        <button type="button" id="gallery-lightbox-prev" class="absolute left-2 sm:left-6 top-1/2 -translate-y-1/2 text-white text-4xl leading-none px-3 py-2 hover:opacity-70" aria-label="Previous">&#8249;</button>
        <button type="button" id="gallery-lightbox-next" class="absolute right-2 sm:right-6 top-1/2 -translate-y-1/2 text-white text-4xl leading-none px-3 py-2 hover:opacity-70" aria-label="Next">&#8250;</button>
        <figure class="max-w-4xl max-h-full">
            <img id="gallery-lightbox-image" src="" alt="" class="max-w-full max-h-[80vh] object-contain mx-auto">
            <figcaption id="gallery-lightbox-caption" class="text-white text-center mt-4 font-sans text-sm"></figcaption>
        </figure>
    </div>

    @push('scripts')
        <script>
            (function () {
                const lightbox = document.getElementById('gallery-lightbox');
                const lightboxImage = document.getElementById('gallery-lightbox-image');
                const lightboxCaption = document.getElementById('gallery-lightbox-caption');
                const thumbs = Array.from(document.querySelectorAll('.gallery-thumb'));
                let currentIndex = 0;

                function show(index) {
                    currentIndex = (index + thumbs.length) % thumbs.length;
                    const thumb = thumbs[currentIndex];
                    lightboxImage.src = thumb.dataset.full;
                    lightboxCaption.textContent = thumb.dataset.caption || '';
                }

                thumbs.forEach((thumb, index) => {
                    thumb.addEventListener('click', () => {
                        show(index);
                        lightbox.classList.remove('hidden');
                        lightbox.classList.add('flex');
                    });
                });

                function closeLightbox() {
                    lightbox.classList.add('hidden');
                    lightbox.classList.remove('flex');
                    lightboxImage.src = '';
                }

                document.getElementById('gallery-lightbox-close').addEventListener('click', closeLightbox);
                document.getElementById('gallery-lightbox-prev').addEventListener('click', () => show(currentIndex - 1));
                document.getElementById('gallery-lightbox-next').addEventListener('click', () => show(currentIndex + 1));
                lightbox.addEventListener('click', (e) => { if (e.target === lightbox) closeLightbox(); });
                document.addEventListener('keydown', (e) => {
                    if (lightbox.classList.contains('hidden')) return;
                    if (e.key === 'Escape') closeLightbox();
                    if (e.key === 'ArrowLeft') show(currentIndex - 1);
                    if (e.key === 'ArrowRight') show(currentIndex + 1);
                });
            })();
        </script>
    @endpush
</x-site-layout>

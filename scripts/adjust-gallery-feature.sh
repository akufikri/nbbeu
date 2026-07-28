#!/usr/bin/env bash
# Run from anywhere inside the Laravel project: bash scripts/adjust-gallery-feature.sh
# Adds a full Gallery feature:
#   - Filament admin CRUD (Gallery) — upload photos, title, category, order, visibility
#   - Public /gallery page — grid + lightbox (prev/next arrows, keyboard nav) + category filter
#   - Landing page preview section (#galeri) with title/category captions
#   - A video embed in the "Profil Ahli" section
#   - Nav/footer "Galeri" links
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_ROOT="$SCRIPT_DIR"
while [ ! -f "$APP_ROOT/artisan" ] && [ "$APP_ROOT" != "/" ]; do
    APP_ROOT="$(dirname "$APP_ROOT")"
done
if [ ! -f "$APP_ROOT/artisan" ]; then
    echo "Could not locate artisan (looked upward from $SCRIPT_DIR). Run this from inside the Laravel project." >&2
    exit 1
fi
cd "$APP_ROOT"

# ------------------------------------------------------------
# 1) Migration
# ------------------------------------------------------------
mkdir -p database/migrations
cat > database/migrations/2026_07_24_000001_create_gallery_items_table.php <<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_items', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('image');
            $table->string('category')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('display_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_items');
    }
};
PHP

# ------------------------------------------------------------
# 2) Model
# ------------------------------------------------------------
mkdir -p app/Models
cat > app/Models/GalleryItem.php <<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'image', 'category', 'display_order', 'is_active'])]
class GalleryItem extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
PHP

# ------------------------------------------------------------
# 3) Filament admin resource
# ------------------------------------------------------------
mkdir -p app/Filament/Resources/Galleries/Schemas app/Filament/Resources/Galleries/Tables app/Filament/Resources/Galleries/Pages

cat > app/Filament/Resources/Galleries/GalleryResource.php <<'PHP'
<?php

namespace App\Filament\Resources\Galleries;

use App\Filament\Resources\Galleries\Pages\CreateGallery;
use App\Filament\Resources\Galleries\Pages\EditGallery;
use App\Filament\Resources\Galleries\Pages\ListGalleries;
use App\Filament\Resources\Galleries\Schemas\GalleryForm;
use App\Filament\Resources\Galleries\Tables\GalleryTable;
use App\Models\GalleryItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GalleryResource extends Resource
{
    protected static ?string $model = GalleryItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Gallery';

    public static function form(Schema $schema): Schema
    {
        return GalleryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GalleryTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGalleries::route('/'),
            'create' => CreateGallery::route('/create'),
            'edit' => EditGallery::route('/{record}/edit'),
        ];
    }
}
PHP

cat > app/Filament/Resources/Galleries/Schemas/GalleryForm.php <<'PHP'
<?php

namespace App\Filament\Resources\Galleries\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GalleryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            FileUpload::make('image')
                ->image()
                ->imageResizeMode('cover')
                ->imageResizeTargetWidth('1200')
                ->imageResizeTargetHeight('900')
                ->maxSize(4096)
                ->disk('cloudinary')
                ->directory('gallery')
                ->required(),
            TextInput::make('title')
                ->maxLength(255),
            TextInput::make('category')
                ->maxLength(100)
                ->helperText('Optional tag used to group/filter photos, e.g. "Seminar", "CSR".'),
            TextInput::make('display_order')
                ->numeric()
                ->default(0)
                ->required(),
            Toggle::make('is_active')
                ->label('Visible on public site')
                ->default(true),
        ]);
    }
}
PHP

cat > app/Filament/Resources/Galleries/Tables/GalleryTable.php <<'PHP'
<?php

namespace App\Filament\Resources\Galleries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GalleryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('display_order')
            ->columns([
                ImageColumn::make('image')
                    ->disk('cloudinary')
                    ->square(),
                TextColumn::make('title')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('category')
                    ->badge()
                    ->placeholder('-'),
                TextColumn::make('display_order')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
PHP

cat > app/Filament/Resources/Galleries/Pages/ListGalleries.php <<'PHP'
<?php

namespace App\Filament\Resources\Galleries\Pages;

use App\Filament\Resources\Galleries\GalleryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGalleries extends ListRecords
{
    protected static string $resource = GalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
PHP

cat > app/Filament/Resources/Galleries/Pages/CreateGallery.php <<'PHP'
<?php

namespace App\Filament\Resources\Galleries\Pages;

use App\Filament\Resources\Galleries\GalleryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGallery extends CreateRecord
{
    protected static string $resource = GalleryResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
PHP

cat > app/Filament/Resources/Galleries/Pages/EditGallery.php <<'PHP'
<?php

namespace App\Filament\Resources\Galleries\Pages;

use App\Filament\Resources\Galleries\GalleryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGallery extends EditRecord
{
    protected static string $resource = GalleryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
PHP

# ------------------------------------------------------------
# 4) Public controller
# ------------------------------------------------------------
mkdir -p app/Http/Controllers/Public
cat > app/Http/Controllers/Public/GalleryController.php <<'PHP'
<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->query('category');

        $items = GalleryItem::where('is_active', true)
            ->when($category, fn ($query) => $query->where('category', $category))
            ->orderBy('display_order')
            ->paginate(12)
            ->withQueryString();

        $categories = GalleryItem::where('is_active', true)
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('public.gallery', [
            'items' => $items,
            'categories' => $categories,
            'category' => $category,
        ]);
    }
}
PHP

# ------------------------------------------------------------
# 5) HomeController — add gallery preview query
# ------------------------------------------------------------
cat > app/Http/Controllers/Public/HomeController.php <<'PHP'
<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use App\Models\OrgChart;
use App\Models\Post;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $orgChart = OrgChart::where('is_active', true)
            ->orderBy('display_order')
            ->limit(4)
            ->get();

        $latestPosts = Post::where('status', 'published')
            ->latest('published_at')
            ->limit(3)
            ->get();

        $galleryPreview = GalleryItem::where('is_active', true)
            ->orderBy('display_order')
            ->limit(6)
            ->get();

        $approvedMembersCount = User::where('status', 'approved')->count();
        $memberCompaniesCount = User::where('status', 'approved')->distinct('company')->count('company');
        $leadershipCount = $orgChart->count();

        return view('public.home', [
            'orgChart' => $orgChart,
            'latestPosts' => $latestPosts,
            'galleryPreview' => $galleryPreview,
            'approvedMembersCount' => $approvedMembersCount,
            'memberCompaniesCount' => $memberCompaniesCount,
            'leadershipCount' => $leadershipCount,
        ]);
    }
}
PHP

# ------------------------------------------------------------
# 6) Route
# ------------------------------------------------------------
ROUTES="routes/web.php"
if [ -f "$ROUTES" ] && ! grep -q "gallery.index" "$ROUTES"; then
    perl -0777 -pi -e "s/use App\\\\Http\\\\Controllers\\\\Public\\\\BlogController;/use App\\\\Http\\\\Controllers\\\\Public\\\\BlogController;\nuse App\\\\Http\\\\Controllers\\\\Public\\\\GalleryController;/" "$ROUTES"
    perl -0777 -pi -e "s/(Route::prefix\('blog'\)->name\('blog\.'\)->group\(function \(\) \{\n    Route::get\('\/', \[BlogController::class, 'index'\]\)->name\('index'\);\n    Route::get\('\/\{post\}', \[BlogController::class, 'show'\]\)->name\('show'\);\n\}\);\n)/\$1\nRoute::get('\/gallery', [GalleryController::class, 'index'])->name('gallery.index');\n/" "$ROUTES"
    echo "Patched: $ROUTES"
else
    echo "Skipped (already patched or missing): $ROUTES"
fi

# ------------------------------------------------------------
# 7) Public gallery page (grid + lightbox with prev/next + category filter)
# ------------------------------------------------------------
mkdir -p resources/views/public
cat > resources/views/public/gallery.blade.php <<'BLADE'
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
BLADE

# ------------------------------------------------------------
# 8) Landing page — full rewrite (gallery preview section + video)
# ------------------------------------------------------------
cat > resources/views/public/home.blade.php <<'BLADE'
<x-site-layout
    title="NBBEU — North Borneo Banking Executive Union"
    description="Solidariti, Profesionalisme, Kesejahteraan. NBBEU memperjuangkan hak, kebajikan dan pembangunan profesional eksekutif perbankan di seluruh Sabah."
>
    <header id="hero" class="hero-marquee" data-count-trigger>
        <div class="max-w-7xl mx-auto px-6">
            <div class="hero-marquee__row scroll-reveal">
                <div class="hero-marquee__top">
                    <p class="hero-stat__eyebrow">North Borneo Banking Executive Union (NBBEU)</p>
                    <h1 class="hero-marquee__headline">Solidariti, Profesionalisme, Kesejahteraan</h1>
                    <div class="hero-stat__actions mt-4">
                        <a href="#program" class="cta-outline">Sertai Keahlian</a>
                        <a href="#tentang-kami" class="cta-text">Ketahui lebih lanjut →</a>
                    </div>
                </div>
                <div class="hero-marquee__image">
                    <img src="{{ asset('assets/images/hero.png') }}" alt="" srcset="">
                </div>
            </div>

            <div class="hero-marquee__sub scroll-reveal">
                <p class="hero-stat__lede">Kami merupakan organisasi yang mewakili eksekutif sektor perbankan di Malaysia, memperjuangkan hak, kebajikan dan pembangunan profesional ahli-ahli kami. Bersatu demi masa depan yang adil dan sejahtera.</p>
            </div>

            <div class="supporting-stats" id="stats">
                <div class="stat-row">
                    <div class="stat-cell">
                        <div class="tnum"><span class="count-up" data-target="2024">0</span></div>
                        <p>Tahun Ditubuhkan</p>
                    </div>
                    <div class="stat-cell">
                        <div class="tnum"><span class="count-up" data-target="{{ $memberCompaniesCount }}">0</span></div>
                        <p>Institusi Perbankan</p>
                    </div>
                    <div class="stat-cell">
                        <div class="tnum">100%</div>
                        <p>Akreditasi Rasmi</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section id="tentang-kami" class="prose-section px-6 scroll-reveal">
        <p class="lede"><span class="head-inline">Sejarah Penubuhan.</span> North Borneo Banking Executive Union ditubuhkan pada tahun 2024 sebagai platform rasmi bagi eksekutif perbankan di Malaysia khususnya di Sabah. Penubuhan kami bertujuan memperkukuh suara profesional dalam industri kewangan serta menjadi rakan strategik kepada pihak pengurusan dalam memupuk hubungan industri yang harmoni dan progresif.</p>
        <p><span class="head-inline">Visi.</span> Menjadi kesatuan eksekutif perbankan yang terunggul, dihormati dan menjadi rujukan dalam memperjuangkan hak dan kebajikan ahli secara profesional, beretika dan berintegriti.</p>
        <p><span class="head-inline">Misi.</span> Mempertahankan Hak — menjamin keadilan pekerjaan dan kebajikan ahli di semua peringkat. Membangunkan Profesional — menyediakan latihan, pensijilan dan pembangunan kerjaya berterusan. Mempromosi Integriti — menggalakkan budaya kerja inklusif, telus dan beretika tinggi. Menjalin Kerjasama — bekerjasama dengan institusi kewangan, kerajaan dan badan profesional untuk kemajuan industri.</p>
    </section>

    <section id="profil-ahli" class="py-24 bg-nb-paper-final">
        <div class="max-w-7xl mx-auto px-6">
            <div class="scroll-reveal">
                <span class="head-numbered__label">01 — Ahli</span>
                <h2 class="mt-2">Profil Ahli</h2>
            </div>

            <div class="mt-8 scroll-reveal rounded-md overflow-hidden max-w-3xl mx-auto">
                <video controls playsinline class="w-full h-auto block" preload="metadata">
                    <source src="https://res.cloudinary.com/pvufs4ep/video/upload/v1784875084/WhatsApp_Video_2026-07-23_at_08.16.15_1_ko3naa.mp4" type="video/mp4">
                </video>
            </div>

            <div class="supporting-stats mt-10 scroll-reveal">
                <div class="stat-row">
                    <div class="stat-cell">
                        <div class="tnum"><span class="count-up" data-target="{{ $approvedMembersCount }}">0</span>+</div>
                        <p>Ahli Aktif</p>
                    </div>
                    <div class="stat-cell">
                        <div class="tnum">Sabah</div>
                        <p>Liputan Cawangan</p>
                    </div>
                    <div class="stat-cell">
                        <div class="tnum"><span class="count-up" data-target="{{ $memberCompaniesCount }}">0</span></div>
                        <p>Institusi Perbankan</p>
                    </div>
                </div>
            </div>

            <p class="mt-6 font-sans text-sm text-nb-ink-muted max-w-2xl">
                Kami berbangga mempunyai lebih {{ $approvedMembersCount }} ahli aktif dari pelbagai institusi perbankan di seluruh negeri Sabah. Ahli kami terdiri daripada Pegawai Profesional yang berdedikasi dalam memacu kecemerlangan industri perbankan.
            </p>
        </div>
    </section>

    <section id="program" class="py-24">
        <div class="max-w-7xl mx-auto px-6">
              <div class="scroll-reveal">
                <span class="head-numbered__label">02 — Program</span>
                <h2 class="mt-2">Program &amp; Aktiviti</h2>
                <p class="mt-2 font-sans text-sm">Akses kepada pembangunan kerjaya eksekutif dan rangkaian serantau yang terkuat.</p>
            </div>

            <dl class="spec-sheet scroll-reveal">
                <div class="spec-row">
                    <dt><span class="spec-row__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.5-2.9 7.9-7 10-4.1-2.1-7-5.5-7-10V6l7-3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg></span>Pembangunan Profesional</dt>
                    <dd>Seminar kepimpinan, bengkel pengurusan risiko, etika perbankan, dan program pensijilan profesional.</dd>
                </div>
                <div class="spec-row">
                    <dt><span class="spec-row__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v3m0 0L6 9m6-3l6 3M4 9l2 5H2l2-5zm16 0l2 5h-4l2-5zM4 9h4m8 0h4M6 14v3a2 2 0 002 2h8a2 2 0 002-2v-3M10 21h4"/></svg></span>Advokasi &amp; Kebajikan</dt>
                    <dd>Memperjuangkan faedah pekerjaan, keseimbangan kerja-hidup, keselamatan pekerjaan dan perlindungan sosial ahli.</dd>
                </div>
                <div class="spec-row">
                    <dt><span class="spec-row__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="8" r="3"/><path stroke-linecap="round" d="M4 20v-1a5 5 0 015-5h0a5 5 0 015 5v1"/><circle cx="17" cy="9" r="2.5"/><path stroke-linecap="round" d="M15.5 14.2A4.5 4.5 0 0120 18.5V20"/></svg></span>Tanggungjawab Sosial Korporat (CSR)</dt>
                    <dd>Program komuniti, sumbangan amal, dan kerjasama strategik bersama institusi kewangan untuk pembangunan masyarakat.</dd>
                </div>
            </dl>
        </div>
    </section>

    <section id="how-to-join" class="py-24 bg-sig-navy text-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="scroll-reveal">
                <span class="head-numbered__label">03 — Prosedur</span>
                <h2 class="mt-2 text-white">Prosedur Pendaftaran Rasmi</h2>
                <p class="mt-2 text-white/70 font-sans text-sm">Proses yang telus bagi mengekalkan standard integriti kepimpinan kesatuan.</p>
            </div>

            <div class="step-sequence mt-10 scroll-reveal">
                <div class="step">
                    <span class="step__num">01</span>
                    <div>
                        <h4>Sediakan Penaja Anda</h4>
                        <p>Anda perlu dicalonkan oleh 2 ahli aktif NBBEU (Pencadang dan Penyokong) sebagai sebahagian daripada permohonan anda.</p>
                    </div>
                </div>
                <div class="step">
                    <span class="step__num">02</span>
                    <div>
                        <h4>Lengkapkan Borang</h4>
                        <p>Lengkapkan profil eksekutif, rekod kerjaya, dan pengesahan institusi secara dalam talian.</p>
                    </div>
                </div>
                <div class="step">
                    <span class="step__num">03</span>
                    <div>
                        <h4>Yuran Pendaftaran</h4>
                        <p>Selesaikan yuran pentadbiran kesatuan tahunan melalui pindahan bank segera yang selamat.</p>
                    </div>
                </div>
                <div class="step">
                    <span class="step__num">04</span>
                    <div>
                        <h4>Semakan Admin</h4>
                        <p>Lembaga Kehormat NBBEU akan mengesahkan kelayakan dalam tempoh 5-7 hari bekerja.</p>
                    </div>
                </div>
                <div class="step">
                    <span class="step__num">05</span>
                    <div>
                        <h4>Pensijilan</h4>
                        <p>Terima Kad Ahli fizikal dan digital serta Sijil Kehormat yang diiktiraf secara rasmi.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="org-structure" class="py-24">
        <div class="max-w-7xl mx-auto px-6">
            <div class="mb-10 scroll-reveal flex items-start justify-between gap-6 flex-wrap">
                <div>
                    <h2>Struktur Kepimpinan 2025-2027</h2>
                    <p class="mt-2 text-nb-ink-muted font-sans text-sm">Diketuai oleh profesional yang berdedikasi untuk membimbing hala tuju kesatuan.</p>
                </div>
                <a href="{{ route('org-structure') }}" class="cta-text">Lihat Struktur Organisasi Penuh →</a>
            </div>

            @if ($orgChart->isEmpty())
                <p class="text-nb-ink-muted font-sans text-sm">Struktur organisasi akan dikemaskini tidak lama lagi.</p>
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

    <section id="hak-advokasi" class="py-24">
        <div class="max-w-7xl mx-auto px-6">
            <div class="scroll-reveal">
                <span class="head-numbered__label">04 — Advokasi</span>
                <h2 class="mt-2">Hak &amp; Advokasi</h2>
                <p class="mt-2 font-sans text-sm text-nb-ink-muted">NBBEU komited memperjuangkan perkara berikut untuk setiap ahli.</p>
            </div>

            <dl class="spec-sheet scroll-reveal">
                <div class="spec-row">
                    <dt>Keadilan Pekerjaan</dt>
                    <dd>Proses yang adil, telus dan bebas diskriminasi.</dd>
                </div>
                <div class="spec-row">
                    <dt>Perlindungan Ahli</dt>
                    <dd>Menentang eksploitasi dan memastikan pematuhan undang-undang buruh.</dd>
                </div>
                <div class="spec-row">
                    <dt>Piawaian Global</dt>
                    <dd>Menyelaraskan polisi dengan amalan antarabangsa terbaik.</dd>
                </div>
                <div class="spec-row">
                    <dt>Kesejahteraan Holistik</dt>
                    <dd>Kempen kesedaran kesihatan mental dan profesionalisme di tempat kerja.</dd>
                </div>
            </dl>
        </div>
    </section>

    <section id="galeri" class="py-24">
        <div class="max-w-7xl mx-auto px-6">
            <div class="mb-10 scroll-reveal flex items-start justify-between gap-6 flex-wrap">
                <div>
                    <span class="head-numbered__label">05 — Galeri</span>
                    <h2 class="mt-2">Galeri Foto</h2>
                    <p class="mt-2 font-sans text-sm text-nb-ink-muted">Momen aktiviti dan program NBBEU.</p>
                </div>
                <a href="{{ route('gallery.index') }}" class="cta-text">Lihat Semua Galeri →</a>
            </div>

            @if ($galleryPreview->isEmpty())
                <p class="text-nb-ink-muted font-sans text-sm">Belum ada foto galeri.</p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 scroll-reveal">
                    @foreach ($galleryPreview as $item)
                        <a href="{{ route('gallery.index') }}">
                            <div class="block aspect-square rounded-md overflow-hidden bg-nb-paper-final">
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('cloudinary')->url($item->image) }}" alt="{{ $item->title }}" class="w-full h-full object-cover hover:opacity-90 transition">
                            </div>
                            @if ($item->title || $item->category)
                                <div class="mt-2">
                                    @if ($item->title)
                                        <p class="text-xs font-medium text-nb-ink truncate">{{ $item->title }}</p>
                                    @endif
                                    @if ($item->category)
                                        <p class="text-xs text-nb-ink-muted truncate">{{ $item->category }}</p>
                                    @endif
                                </div>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section id="blog" class="py-24 bg-nb-paper-final">
        <div class="max-w-7xl mx-auto px-6">
            <div class="mb-10 scroll-reveal">
                <span class="head-numbered__label">06 — Penerbitan</span>
                <h2 class="mt-2">Berita &amp; Penerbitan</h2>
                <p class="mt-2 font-sans text-sm text-nb-ink-muted">Analisis Industri, Kenyataan Rasmi, Buletin NBBEU dan Laporan Tahunan.</p>
            </div>

            @if ($latestPosts->isEmpty())
                <p class="text-nb-ink-muted font-sans text-sm">Belum ada artikel diterbitkan.</p>
            @else
                <div class="index-list scroll-reveal">
                    @foreach ($latestPosts as $post)
                        <article class="index-list__row">
                            <div class="index-list__thumb">
                                @if ($post->cover_image)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('cloudinary')->url($post->cover_image) }}" alt="{{ $post->title }}">
                                @else
                                    Photo 16:10
                                @endif
                            </div>
                            <div>
                                <h3><a href="{{ route('blog.show', $post) }}">{{ $post->title }}</a></h3>
                                <p>{{ $post->excerpt }}</p>
                                <span class="index-list__date">{{ $post->published_at?->format('F Y') }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            <div class="section-tail flex items-center justify-between flex-wrap gap-4">
                <h2 class="text-xl">Analisis &amp; Berita Terkini</h2>
                <a href="{{ route('blog.index') }}" class="cta-text">Lihat semua berita &amp; penerbitan →</a>
            </div>
        </div>
    </section>

    <section id="cta-final" class="py-24 bg-nb-paper-final">
        <div class="max-w-7xl mx-auto px-6 text-center scroll-reveal">
            <h2>Bersatu Demi Masa Depan yang Adil dan Sejahtera</h2>
            <p class="font-sans text-nb-ink mt-4 max-w-xl mx-auto leading-relaxed">Mari kita perkukuhkan standard profesionalisme, integriti dan masa depan kepimpinan kewangan di Sabah bersama-sama.</p>
            <a href="{{ route('registration.create') }}" class="cta-outline mt-8 inline-flex">Mohon Keahlian Hari Ini</a>
        </div>
    </section>
</x-site-layout>
BLADE

# ------------------------------------------------------------
# 9) Site layout — nav/footer "Galeri" links
# ------------------------------------------------------------
mkdir -p resources/views/components
cat > resources/views/components/site-layout.blade.php <<'BLADE'
@props([
    'title' => 'NBBEU — North Borneo Banking Executive Union',
    'description' => "NBBEU upholds the highest professional standards, facilitates strategic dialogue, and protects the collective interests of banking industry leaders across North Borneo.",
    'hideNav' => false,
])
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500&family=IBM+Plex+Sans:wght@400;500&family=Spectral:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'nb-primary': 'oklch(31.5% 0.085 260.2)',
                        'nb-primary-active': 'oklch(26.3% 0.068 259.1)',
                        'nb-paper': 'oklch(97.6% 0.003 264.5)',
                        'nb-paper-raised': 'oklch(100% 0 0)',
                        'nb-paper-final': 'oklch(93.9% 0.006 255.5)',
                        'nb-ink': 'oklch(28.2% 0.019 254.8)',
                        'nb-ink-muted': 'oklch(55.1% 0.023 264.4)',
                        'nb-rule': 'oklch(89.3% 0.011 261.8)',
                        'nb-accent': 'oklch(69.0% 0.115 206.7)',
                        'sig-navy': 'oklch(23.4% 0.061 259.5)',
                        'sig-teal': 'oklch(41.4% 0.067 210.9)',
                        'sig-cream': 'oklch(94.3% 0.025 83.4)',
                    },
                    fontFamily: {
                        sans: ['"IBM Plex Sans"', 'sans-serif'],
                        serif: ['Spectral', 'serif'],
                        mono: ['"IBM Plex Mono"', 'monospace'],
                    },
                }
            }
        }
    </script>
    <link rel="stylesheet" href="{{ asset('assets/site/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/site/style.css') }}">
</head>
<body class="bg-nb-paper font-sans text-nb-ink antialiased selection:bg-nb-accent/20">

    @unless ($hideNav)
    <header id="navbar" class="masthead sticky top-0 z-50">
        <div class="masthead-top">
            <div class="masthead-top__inner">
                <p class="masthead-kicker">Ditubuhkan 2024 · North Borneo Banking Executive Union</p>
                <div class="masthead-wordmark-row">
                    <a href="{{ route('home') }}" class="masthead-wordmark-link">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="" class="masthead-logo">
                        <span class="masthead-wordmark">NBBEU</span>
                    </a>
                </div>
                <div class="masthead-rule masthead-rule--double"></div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-6">
            <div class="masthead-nav-row">
                <button id="mobile-menu-toggle" class="masthead-toggle lg:hidden text-nb-primary" aria-label="Toggle Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <nav class="masthead-links hidden lg:flex">
                    <a href="{{ route('home') }}" class="masthead-compact-logo-link">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="NBBEU" class="masthead-compact-logo">
                    </a>
                    <a href="{{ route('home') }}#tentang-kami">Tentang Kami</a>
                    <a href="{{ route('home') }}#program">Program</a>
                    <a href="{{ route('org-structure') }}">Struktur Organisasi</a>
                    <a href="{{ route('blog.index') }}">Berita</a>
                    <a href="{{ route('gallery.index') }}">Galeri</a>
                    <a href="{{ route('home') }}#contact">Hubungi Kami</a>
                </nav>
                <div class="masthead-actions hidden lg:flex">
                    @auth
                        <div class="user-menu">
                            <button type="button" id="user-menu-toggle" class="user-menu__trigger" aria-haspopup="true" aria-expanded="false">
                                {{ auth()->user()->name }}
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div id="user-menu-dropdown" class="user-menu__dropdown hidden">
                                <a href="{{ route('dashboard') }}">Dashboard</a>
                                <a href="{{ route('profile.edit') }}">Profil</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit">Log Keluar</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" id="login-link">Log Masuk Ahli</a>
                        <a href="{{ route('registration.create') }}" id="nav-join-btn" class="btn-primary cta-fill">Sertai Keahlian</a>
                    @endauth
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="hidden lg:hidden bg-nb-paper">
            <div class="px-6 py-4 flex flex-col">
                <a href="{{ route('home') }}#tentang-kami" class="mobile-link">Tentang Kami</a>
                <a href="{{ route('home') }}#program" class="mobile-link">Program</a>
                <a href="{{ route('org-structure') }}" class="mobile-link">Struktur Organisasi</a>
                <a href="{{ route('blog.index') }}" class="mobile-link">Berita</a>
                <a href="{{ route('gallery.index') }}" class="mobile-link">Galeri</a>
                <a href="{{ route('home') }}#contact" class="mobile-link">Hubungi Kami</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="mobile-link">Dashboard</a>
                    <a href="{{ route('profile.edit') }}" class="mobile-link">Profil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-primary cta-fill w-full mt-3 justify-center">Log Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="mobile-link">Log Masuk Ahli</a>
                    <a href="{{ route('registration.create') }}" class="btn-primary cta-fill w-full mt-3 justify-center">Sertai Keahlian</a>
                @endauth
            </div>
        </div>
    </header>
    @endunless

    {{ $slot }}

    <footer id="contact" class="bg-sig-navy text-white/80 py-16">
        <div class="max-w-7xl mx-auto px-6 colophon">
            <p class="text-white"><strong>NORTH BORNEO BANKING EXECUTIVE UNION (NBBEU)</strong></p>
            <p class="mt-6">
                Lot 1, Block A, Jalan Ikan Juara 1, Tingkat 2, Sadong Jaya, 88818 Kota Kinabalu, Sabah.<br>
                E-mel: nbbeusabah@gmail.com · Telefon: +60165830034<br>
                Waktu Pejabat: Isnin - Jumaat, 9.00 pagi - 5.00 petang
            </p>
            <p class="mt-6 colophon__links">
                <a href="{{ route('home') }}#tentang-kami">Tentang Kami</a>
                <a href="{{ route('home') }}#program">Program</a>
                <a href="{{ route('org-structure') }}">Struktur Organisasi</a>
                <a href="{{ route('blog.index') }}">Berita</a>
                <a href="{{ route('gallery.index') }}">Galeri</a>
                <a href="{{ route('privacy') }}">Dasar Privasi</a>
                <a href="{{ route('terms') }}">Terma &amp; Syarat</a>
            </p>
            <p class="mt-8 text-white/50">© {{ now()->year }} North Borneo Banking Executive Union (NBBEU). Hak Cipta Terpelihara.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/site/script.js') }}"></script>
    @stack('scripts')
</body>
</html>
BLADE

# ------------------------------------------------------------
# 10) Migrate + clear caches
# ------------------------------------------------------------
php artisan migrate --force
php artisan optimize:clear

echo "Done: Gallery feature added (admin CRUD, public /gallery page with filter+lightbox, landing preview, nav links, video embed)."

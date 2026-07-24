#!/usr/bin/env bash
# Run from anywhere inside the Laravel project: bash scripts/adjust-public-site-copy.sh
# Rewrites the public marketing site to the client's Malay copy brief:
#   Laman Utama, Tentang Kami, Profil Ahli, Program & Aktiviti,
#   Berita & Penerbitan, Hak & Advokasi, Hubungi Kami — all mapped onto the
#   existing single-page landing site (anchor sections), plus real
#   Struktur Kepimpinan 2025-2027 leadership data.
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
# 1) Landing page — full rewrite
# ------------------------------------------------------------
mkdir -p resources/views/public
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

            <div class="supporting-stats mt-6 scroll-reveal">
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

    <section id="blog" class="py-24 bg-nb-paper-final">
        <div class="max-w-7xl mx-auto px-6">
            <div class="mb-10 scroll-reveal">
                <span class="head-numbered__label">05 — Penerbitan</span>
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
# 2) Site layout — nav labels + real Sabah contact info in footer
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
# 3) Org structure page — Malay copy
# ------------------------------------------------------------
cat > resources/views/public/org-structure.blade.php <<'BLADE'
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
BLADE

# ------------------------------------------------------------
# 4) Real 2025-2027 leadership data (idempotent seeder)
# ------------------------------------------------------------
mkdir -p database/seeders
cat > database/seeders/LeadershipSeeder.php <<'PHP'
<?php

namespace Database\Seeders;

use App\Models\OrgChart;
use Illuminate\Database\Seeder;

/**
 * Replaces org_chart with the real 2025-2027 leadership structure.
 * Idempotent (keyed by name via updateOrCreate) — safe to rerun.
 *
 * Run standalone: php artisan db:seed --class=LeadershipSeeder
 */
class LeadershipSeeder extends Seeder
{
    public function run(): void
    {
        // Clear placeholder/stale rows first (avoid duplicate rows from
        // DatabaseSeeder's dummy org chart or old admin-panel edits).
        OrgChart::query()->update(['parent_id' => null]);
        OrgChart::query()->delete();

        $president = OrgChart::updateOrCreate(
            ['name' => 'Hassanuddin Bin Iskandar'],
            ['position' => 'Presiden', 'parent_id' => null, 'display_order' => 1, 'is_active' => true],
        );

        $members = [
            ['name' => 'Christine John William', 'position' => 'Timbalan Presiden'],
            ['name' => 'Dzunaidah Binti Sahadan', 'position' => 'Setiausaha Agung'],
            ['name' => 'Ruzina Binti Lamau', 'position' => 'Timbalan Setiausaha Agung'],
            ['name' => 'Emmanuel Sylvester', 'position' => 'Bendahari Agung'],
            ['name' => 'Zahaibuddin Bin Cappi', 'position' => 'Timbalan Bendahari Agung'],
            ['name' => 'Din Bin Badaruddin', 'position' => 'Ahli Jawatankuasa'],
        ];

        foreach ($members as $index => $member) {
            OrgChart::updateOrCreate(
                ['name' => $member['name']],
                [
                    'position' => $member['position'],
                    'parent_id' => $president->id,
                    'display_order' => $index + 2,
                    'is_active' => true,
                ],
            );
        }
    }
}
PHP

# ------------------------------------------------------------
# 5) Run the leadership seeder + clear caches
# ------------------------------------------------------------
php artisan db:seed --class=LeadershipSeeder --force
php artisan optimize:clear

echo "Done: public site copy updated to the client's Malay brief, real 2025-2027 leadership data seeded."

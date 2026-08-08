@props([
    'title' => 'NBBEU | Banking Executive Union Sabah, Malaysia',
    'description' => 'NBBEU is the union for banking executives in Sabah, Malaysia, working for members rights, welfare and professional development.',
    'canonical' => url()->current(),
    'ogTitle' => 'NBBEU | North Borneo Banking Executive Union, Sabah',
    'ogDescription' => 'NBBEU is the union for banking executives in Sabah, Malaysia — advocating for members rights, welfare and professional development.',
    'ogImage' => 'https://nbbeu.org/assets/images/og-image.jpg',
    'hideNav' => false,
])
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ $canonical }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:title" content="{{ $ogTitle }}">
    <meta property="og:description" content="{{ $ogDescription }}">
    <meta property="og:image" content="{{ $ogImage ?: 'https://nbbeu.org/assets/images/og-image.jpg' }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:site_name" content="North Borneo Banking Executive Union">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ogTitle }}">
    <meta name="twitter:description" content="{{ $ogDescription }}">
    <meta name="twitter:image" content="{{ $ogImage ?: 'https://nbbeu.org/assets/images/og-image.jpg' }}">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "@id": "{{ url('/') }}#organization",
        "name": "North Borneo Banking Executive Union",
        "alternateName": "NBBEU",
        "url": "{{ url('/') }}",
        "logo": { "@type": "ImageObject", "url": "{{ asset('assets/images/logo.png') }}" },
        "foundingDate": "2024",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "Lot 1, Block A, Jalan Ikan Juara 1, Tingkat 2, Sadong Jaya",
            "postalCode": "88818",
            "addressLocality": "Kota Kinabalu",
            "addressRegion": "Sabah",
            "addressCountry": "MY"
        },
        "email": "nbbeusabah@gmail.com",
        "telephone": "+60-165830034"
    }
    </script>
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
                <p class="masthead-kicker">Established 2024 · North Borneo Banking Executive Union</p>
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
                    <a href="{{ route('home') }}#tentang-kami">About</a>
                    <a href="{{ route('home') }}#program">Programs</a>
                    <a href="{{ route('org-structure') }}">Struktur Organisasi</a>
                    <a href="{{ route('blog.index') }}">News</a>
                    <a href="{{ route('gallery.index') }}">Gallery</a>
                    <a href="{{ route('collective-agreement') }}">Collective Agreement</a>
                    <a href="{{ route('home') }}#contact">Contact Us</a>
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
                                    <button type="submit">Log Out</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" id="login-link">Member Login</a>
                        <a href="{{ route('registration.create') }}" id="nav-join-btn" class="btn-primary cta-fill">Join Membership</a>
                    @endauth
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="hidden lg:hidden bg-nb-paper">
            <div class="px-6 py-4 flex flex-col">
                <a href="{{ route('home') }}#tentang-kami" class="mobile-link">About</a>
                <a href="{{ route('home') }}#program" class="mobile-link">Programs</a>
                <a href="{{ route('org-structure') }}" class="mobile-link">Struktur Organisasi</a>
                <a href="{{ route('blog.index') }}" class="mobile-link">News</a>
                <a href="{{ route('gallery.index') }}" class="mobile-link">Gallery</a>
                <a href="{{ route('collective-agreement') }}" class="mobile-link">Collective Agreement</a>
                <a href="{{ route('home') }}#contact" class="mobile-link">Contact Us</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="mobile-link">Dashboard</a>
                    <a href="{{ route('profile.edit') }}" class="mobile-link">Profil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-primary cta-fill w-full mt-3 justify-center">Log Out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="mobile-link">Member Login</a>
                    <a href="{{ route('registration.create') }}" class="btn-primary cta-fill w-full mt-3 justify-center">Join Membership</a>
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
                Email: nbbeusabah@gmail.com · Phone: +60165830034<br>
                Office Hours: Monday - Friday, 9:00 am - 5:00 pm
            </p>
            <p class="mt-6 colophon__links">
                <a href="{{ route('home') }}#tentang-kami">About</a>
                <a href="{{ route('home') }}#program">Programs</a>
                <a href="{{ route('org-structure') }}">Struktur Organisasi</a>
                <a href="{{ route('blog.index') }}">News</a>
                <a href="{{ route('gallery.index') }}">Gallery</a>
                <a href="{{ route('collective-agreement') }}">Collective Agreement</a>
                <a href="{{ route('privacy') }}">Privacy Policy</a>
                <a href="{{ route('terms') }}">Terms &amp; Conditions</a>
            </p>
            <p class="mt-8 text-white/50">© {{ now()->year }} North Borneo Banking Executive Union (NBBEU). All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/site/script.js') }}"></script>
    @stack('scripts')
</body>
</html>

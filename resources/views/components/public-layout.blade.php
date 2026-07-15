<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name', 'NBBEU') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Spectral:wght@400;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-nbbeu-ink antialiased bg-nbbeu-bg">
        <header class="bg-white text-nbbeu-navy fixed top-0 left-0 right-0 z-50">
            <nav class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-display text-lg font-semibold">
                    <span class="inline-flex">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="NBBEU" class="h-8 w-8">
                    </span>
                    NBBEU
                </a>

                <div class="flex items-center gap-6 text-sm">
                    <a href="{{ route('home') }}" class="hover:text-nbbeu-teal">Home</a>
                    <a href="{{ route('blog.index') }}" class="hover:text-nbbeu-teal">Blog</a>
                    <a href="{{ route('registration.status') }}" class="hover:text-nbbeu-teal">Check Status</a>
                    <a href="{{ route('registration.create') }}" class="px-4 py-2.5 rounded-sm bg-nbbeu-navy text-white font-medium transition">
                        Become a Member
                    </a>
                </div>
            </nav>
        </header>

        <main>
            {{ $slot }}
        </main>

        <x-arc-divider color="navy" />

        <footer class="bg-nbbeu-navy-deep text-gray-300">
            <div class="max-w-6xl mx-auto px-6 py-10 grid gap-8 md:grid-cols-2 text-sm">
                <div>
                    <div class="flex items-center gap-2 font-display text-white text-lg font-semibold">
                        <span class="inline-flex bg-white rounded-full p-1">
                            <img src="{{ asset('assets/images/logo.png') }}" alt="NBBEU" class="h-8 w-8">
                        </span>
                        NBBEU
                    </div>
                    <p class="mt-2 max-w-sm">North Borneo Banking Executive Union — uniting North Borneo's banking executives.</p>
                </div>
                <div class="md:text-right">
                    <p>Est. 2024</p>
                    <p class="mt-1">&copy; {{ now()->year }} NBBEU. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </body>
</html>

@props([
    'title' => 'NBBEU - North Borneo Banking Executive Union',
    'description' => "Uniting North Borneo's banking executives for collaboration, advocacy, and professional development.",
    'ogType' => 'website',
])
<!doctype html>
<html
    data-wf-domain="coverly-template.webflow.io"
    data-wf-page="684f91df71b424da63a64dea"
    data-wf-site="684f91df71b424da63a64df2"
    lang="en"
>
    <head>
        <meta charset="utf-8" />
        <title>{{ $title }}</title>
        <meta content="{{ $title }}" property="og:title" />
        <meta content="{{ $title }}" name="twitter:title" />
        <meta content="{{ $description }}" name="description" />
        <meta content="{{ $description }}" property="og:description" />
        <meta content="{{ $description }}" name="twitter:description" />
        <meta property="og:type" content="{{ $ogType }}" />
        <meta content="summary_large_image" name="twitter:card" />
        <meta content="width=device-width, initial-scale=1" name="viewport" />
        <link
            href="{{ asset('webflow/cdn.prod.website-files.com/684f91df71b424da63a64df2/css/coverly-template.webflow.shared.df85c18f3.css') }}"
            rel="stylesheet"
            type="text/css"
        />
        <style>
            /* NBBEU brand theme override — see docs/DESIGN.md */
            :root {
                --base--black: #232a33;
                --base--grey: #5b6472;
                --base--beige: #f6f7f9;
                --base--orange: #16305c;
                --base--white: #fff;
                /* Typography per docs/DESIGN.md: Spectral for headlines, IBM Plex Sans for body/nav/forms */
                --_typography---font-family--primary: Spectral, serif;
                --_typography---font-family--secondary: "IBM Plex Sans", sans-serif;
            }

            .w-webflow-badge {
                display: none !important;
            }

            /* Spectral is reserved for headline/hero text per DESIGN.md — the
               Coverly stylesheet also applies the "primary" font to .text-xl
               (nav labels, card/accordion titles) and a few accent classes,
               which mixed serif into body-level UI text. Force those to the
               body font so only h1-h6/.h3 use Spectral. */
            .text-xl,
            .accordion_icon,
            .font-primary {
                font-family: "IBM Plex Sans", sans-serif !important;
            }
            .accordion_number {
                font-family: "IBM Plex Mono", monospace !important;
            }

            /* Buttons: navy, square corners */
            .button {
                background-color: #16305c !important;
                border-radius: 0 !important;
            }
            .button:hover {
                background-color: #0b1d3a !important;
            }

            /* Navbar: solid white background */
            .nav {
                background-color: #fff !important;
            }

            /* Footer: white background */
            .section_footer {
                background-color: #fff !important;
                color: #232a33 !important;
            }
            .section_footer .link.no-link,
            .section_footer .link {
                color: #232a33 !important;
            }

            /* Cards: white background */
            .card.is-link,
            .card.bg-secondary,
            .tabs_menu {
                background-color: #fff !important;
            }
            .card.is-link:hover {
                background-color: #f6f7f9 !important;
            }
            .card.bg-secondary {
                color: #232a33 !important;
            }
            .tabs_menu {
                justify-content: center !important;
            }
            .js-accordion-toggle {
                cursor: pointer;
            }
            .accordion_list {
                border-bottom: 0.06rem solid #d7dbe0 !important;
            }
            .accordion {
                border-top: 0.06rem solid #d7dbe0 !important;
            }

            /* Utility/data font (no. ahli, tanggal, statistik) per docs/DESIGN.md */
            .text-number,
            .batch .text-sm {
                font-family: "IBM Plex Mono", monospace;
            }

            /* Mobile menu open/close (replaces the removed IX2 interaction) */
            .menu_content {
                transform: translate(0, -100%);
            }
            .nav.is-menu-open .menu_content {
                transform: translate(0, 0) !important;
            }
            .nav_overlay {
                display: none;
            }
            .nav.is-menu-open .nav_overlay {
                display: block !important;
                opacity: 1 !important;
            }

            /* No border-radius anywhere */
            *,
            *::before,
            *::after {
                border-radius: 0 !important;
            }

            /* Static design — disable all animation/transition/reveal effects */
            *,
            *::before,
            *::after {
                animation: none !important;
                transition: none !important;
                scroll-behavior: auto !important;
            }
            [style*="opacity: 0"] {
                opacity: 1 !important;
            }
            .hero_img .img-wrapper,
            .hero_img .img,
            .statistic_img .img-wrapper,
            .statistic_img .img,
            .button,
            .button:hover {
                transform: none !important;
                filter: none !important;
            }
        </style>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Spectral:wght@400;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap"
            rel="stylesheet"
        />
        <script type="text/javascript">
            !(function (o, c) {
                var n = c.documentElement,
                    t = " w-mod-";
                ((n.className += t + "js"),
                    ("ontouchstart" in o ||
                        (o.DocumentTouch && c instanceof DocumentTouch)) &&
                        (n.className += t + "touch"));
            })(window, document);
        </script>
        <link
            href="{{ asset('assets/images/logo.png') }}"
            rel="shortcut icon"
            type="image/x-icon"
        />
        <link
            href="{{ asset('assets/images/logo.png') }}"
            rel="apple-touch-icon"
        />
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const menuButton = document.querySelector(".js-menu-toggle");
                const overlay = document.querySelector(".nav_overlay");
                const nav = document.querySelector(".nav");

                function toggleMenu() {
                    nav.classList.toggle("is-menu-open");
                }

                if (menuButton && nav) {
                    menuButton.addEventListener("click", toggleMenu);
                }
                if (overlay && nav) {
                    overlay.addEventListener("click", toggleMenu);
                }
            });
        </script>
    </head>
    <body>
        <div class="page-wrapper">
            <div class="global-styles w-embed">
                <style>
                    body {
                        -webkit-font-smoothing: antialiased;
                        -moz-osx-font-smoothing: grayscale;
                        font-smoothing: antialiased;
                        text-rendering: optimizeLegibility;
                    }

                    *[tabindex]:focus-visible,
                    input[type="file"]:focus-visible {
                        outline: 0.125rem solid #4d65ff;
                        outline-offset: 0.125rem;
                    }

                    .inherit-color * {
                        color: inherit;
                    }

                    .w-richtext > :not(div):first-child,
                    .w-richtext > div:first-child > :first-child {
                        margin-top: 0 !important;
                    }

                    .w-richtext > :last-child,
                    .w-richtext ol li:last-child,
                    .w-richtext ul li:last-child {
                        margin-bottom: 0 !important;
                    }

                    .container-medium,
                    .container-small,
                    .container-large {
                        margin-right: auto !important;
                        margin-left: auto !important;
                    }

                    .text-style-3lines {
                        display: -webkit-box;
                        overflow: hidden;
                        -webkit-line-clamp: 3;
                        -webkit-box-orient: vertical;
                    }

                    .text-style-2lines {
                        display: -webkit-box;
                        overflow: hidden;
                        -webkit-line-clamp: 2;
                        -webkit-box-orient: vertical;
                    }

                    .display-inlineflex {
                        display: inline-flex;
                    }

                    .hide {
                        display: none !important;
                    }

                    @media screen and (max-width: 991px) {
                        .hide,
                        .hide-tablet {
                            display: none !important;
                        }
                    }
                    @media screen and (max-width: 767px) {
                        .hide-mobile-landscape {
                            display: none !important;
                        }
                    }
                    @media screen and (max-width: 479px) {
                        .hide-mobile {
                            display: none !important;
                        }
                    }

                    .margin-0 {
                        margin: 0rem !important;
                    }

                    .padding-0 {
                        padding: 0rem !important;
                    }

                    .spacing-clean {
                        padding: 0rem !important;
                        margin: 0rem !important;
                    }

                    .margin-top {
                        margin-right: 0rem !important;
                        margin-bottom: 0rem !important;
                        margin-left: 0rem !important;
                    }

                    .padding-top {
                        padding-right: 0rem !important;
                        padding-bottom: 0rem !important;
                        padding-left: 0rem !important;
                    }

                    .margin-right {
                        margin-top: 0rem !important;
                        margin-bottom: 0rem !important;
                        margin-left: 0rem !important;
                    }

                    .padding-right {
                        padding-top: 0rem !important;
                        padding-bottom: 0rem !important;
                        padding-left: 0rem !important;
                    }

                    .margin-bottom {
                        margin-top: 0rem !important;
                        margin-right: 0rem !important;
                        margin-left: 0rem !important;
                    }

                    .padding-bottom {
                        padding-top: 0rem !important;
                        padding-right: 0rem !important;
                        padding-left: 0rem !important;
                    }

                    .margin-left {
                        margin-top: 0rem !important;
                        margin-right: 0rem !important;
                        margin-bottom: 0rem !important;
                    }

                    .padding-left {
                        padding-top: 0rem !important;
                        padding-right: 0rem !important;
                        padding-bottom: 0rem !important;
                    }

                    .margin-horizontal {
                        margin-top: 0rem !important;
                        margin-bottom: 0rem !important;
                    }

                    .padding-horizontal {
                        padding-top: 0rem !important;
                        padding-bottom: 0rem !important;
                    }

                    .margin-vertical {
                        margin-right: 0rem !important;
                        margin-left: 0rem !important;
                    }

                    .padding-vertical {
                        padding-right: 0rem !important;
                        padding-left: 0rem !important;
                    }

                    .sidebar::-webkit-scrollbar {
                        display: none;
                    }

                    .sidebar {
                        -ms-overflow-style: none;
                        scrollbar-width: none;
                    }

                    input,
                    textarea,
                    select {
                        border: none !important;
                        outline: none;
                    }

                    .spacer * {
                        padding: 0;
                    }

                    .tabs_link.w--current {
                        color: var(--text-color--text-primary) !important;
                    }

                    .tabs_link.w--current .point-wrap {
                        border-color: var(--bg-color--bg-secondary) !important;
                    }

                    .tabs_link.w--current .point {
                        background-color: var(--bg-color--bg-secondary) !important;
                        opacity: 1 !important;
                    }

                    .accordion:hover {
                        border-color: var(--grey--900) !important;
                    }

                    .accordion:hover .accordion_number {
                        color: var(--grey--900) !important;
                    }

                    .accordion:hover .circle {
                        opacity: 1 !important;
                    }
                </style>
            </div>
            <div
                data-animation="default"
                data-collapse="none"
                data-duration="500"
                data-easing="ease-out"
                data-easing2="ease-in-back"
                data-no-scroll="1"
                role="banner"
                class="nav w-nav"
            >
                <div class="nav_bar">
                    <div class="nav_container">
                        <div class="menu js-menu-toggle">
                            <nav role="navigation" class="menu_wrap w-nav-menu">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="100%"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    class="icon-1x1-medium"
                                >
                                    <path
                                        d="M4 6H20M4 12H20M4 18H20"
                                        stroke="black"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                    ></path>
                                </svg>
                            </nav>
                        </div>
                        <a href="{{ route('home') }}" class="home_logo-link w-nav-brand" style="padding-top: 0.75rem; padding-bottom: 0.75rem;"
                            ><img
                                loading="lazy"
                                src="{{ asset('assets/images/logo.png') }}"
                                alt="NBBEU"
                                class="home_logo"
                                style="height: 2.5rem; width: auto;"
                        /></a>
                        <div class="nav_button">
                            @if (request()->routeIs('home'))
                                <a
                                    href="{{ route('login') }}"
                                    class="w-inline-block"
                                    style="font-weight: 700; color: #16305c; text-decoration: none;"
                                    >LOGIN</a
                                >
                            @else
                                <a
                                    href="{{ route('registration.create') }}"
                                    class="button is-small w-inline-block"
                                    ><div class="button-content">
                                        <div class="button-text is-one">
                                            Become a Member
                                        </div>
                                        <div class="button-text is-two">
                                            Become a Member
                                        </div>
                                    </div></a
                                >
                            @endif
                        </div>
                        <div class="menu_content">
                            <div class="nav_wrap">
                                <div class="w-layout-grid nav_content-grid">
                                    <div>
                                        <div class="link no-link-nav">
                                            <div class="text-xl font-secondary">
                                                Explore
                                            </div>
                                        </div>
                                        <a
                                            href="{{ route('home') }}"
                                            @if (request()->routeIs('home')) aria-current="page" @endif
                                            class="nav_link w-inline-block @if (request()->routeIs('home')) w--current @endif"
                                            ><div class="clip">
                                                <div>Home</div>
                                                <div class="line is-black"></div></div></a
                                        ><a
                                            href="{{ request()->routeIs('home') ? '#benefits' : route('home').'#benefits' }}"
                                            class="nav_link w-inline-block"
                                            ><div class="clip">
                                                <div>Membership Benefits</div>
                                                <div class="line is-black"></div></div></a
                                        ><a
                                            href="{{ request()->routeIs('home') ? '#how-it-works' : route('home').'#how-it-works' }}"
                                            class="nav_link w-inline-block"
                                            ><div class="clip">
                                                <div>How to Join</div>
                                                <div class="line is-black"></div></div
                                        ></a>
                                    </div>
                                    <div>
                                        <div class="link no-link-nav">
                                            <div class="text-xl font-secondary">
                                                Resources
                                            </div>
                                        </div>
                                        <a
                                            href="{{ route('blog.index') }}"
                                            @if (request()->routeIs('blog.*')) aria-current="page" @endif
                                            class="nav_link w-inline-block @if (request()->routeIs('blog.*')) w--current @endif"
                                            ><div class="clip">
                                                <div>Blog</div>
                                                <div class="line is-black"></div></div></a
                                        ><a
                                            href="{{ route('registration.status') }}"
                                            class="nav_link w-inline-block"
                                            ><div class="clip">
                                                <div>Application Status</div>
                                                <div class="line is-black"></div></div></a
                                        ><a
                                            href="{{ route('registration.create') }}"
                                            class="nav_link w-inline-block"
                                            ><div class="clip">
                                                <div>Become a Member</div>
                                                <div class="line is-black"></div></div
                                        ></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="nav_overlay"></div>
            </div>
            <div class="main-wrapper">
                {{ $slot }}
                <section class="section_footer">
                    <div class="padding-section-small">
                        <div class="padding-global">
                            <div>
                                <div class="w-layout-grid footer_grid">
                                    <div class="footer_content">
                                        <a
                                            href="{{ route('home') }}"
                                            class="home_logo-link w-nav-brand"
                                            style="padding-top: 0.75rem; padding-bottom: 0.75rem; display: inline-block;"
                                            ><img
                                                loading="lazy"
                                                src="{{ asset('assets/images/logo.png') }}"
                                                alt="NBBEU"
                                                class="home_logo"
                                                style="height: 2.5rem; width: auto;"
                                        /></a>
                                        <div class="">
                                            NBBEU unites banking executives
                                            across North Borneo through
                                            networking, advocacy, and
                                            professional development.
                                        </div>
                                    </div>
                                    <div class="w-layout-grid links_grid">
                                        <div>
                                            <div class="link no-link">
                                                <div class="text-xl font-secondary">
                                                    Explore
                                                </div>
                                            </div>
                                            <a
                                                href="{{ route('home') }}"
                                                @if (request()->routeIs('home')) aria-current="page" @endif
                                                class="link w-inline-block @if (request()->routeIs('home')) w--current @endif"
                                                ><div class="clip">
                                                    <div>Home</div>
                                                    <div class="line"></div></div></a
                                            ><a
                                                href="{{ request()->routeIs('home') ? '#benefits' : route('home').'#benefits' }}"
                                                class="link w-inline-block"
                                                ><div class="clip">
                                                    <div>Membership Benefits</div>
                                                    <div class="line"></div></div></a
                                            ><a
                                                href="{{ request()->routeIs('home') ? '#how-it-works' : route('home').'#how-it-works' }}"
                                                class="link w-inline-block"
                                                ><div class="clip">
                                                    <div>How to Join</div>
                                                    <div class="line"></div></div
                                            ></a>
                                        </div>
                                        <div>
                                            <div class="link no-link">
                                                <div class="text-xl font-secondary">
                                                    Resources
                                                </div>
                                            </div>
                                            <a
                                                href="{{ route('blog.index') }}"
                                                @if (request()->routeIs('blog.*')) aria-current="page" @endif
                                                class="link w-inline-block @if (request()->routeIs('blog.*')) w--current @endif"
                                                ><div class="clip">
                                                    <div>Blog</div>
                                                    <div class="line"></div></div></a
                                            ><a
                                                href="{{ route('registration.status') }}"
                                                class="link w-inline-block"
                                                ><div class="clip">
                                                    <div>Application Status</div>
                                                    <div class="line"></div></div></a
                                            ><a
                                                href="{{ route('registration.create') }}"
                                                class="link w-inline-block"
                                                ><div class="clip">
                                                    <div>Become a Member</div>
                                                    <div class="line"></div></div
                                            ></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="spacer-xhuge"></div>
                                <div class="line_grey-700"></div>
                                <div class="spacer-xhuge"></div>
                                <div class="footer_medium">
                                    <div class="text-sm">
                                        &copy; {{ now()->year }} NBBEU. All rights reserved.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <script
            src="{{ asset('webflow/d3e54v103j8qbb.cloudfront.net/js/jquery-3.5.1.min.dc5e7f18c8.js') }}"
            type="text/javascript"
        ></script>
        <script
            src="{{ asset('webflow/cdn.prod.website-files.com/684f91df71b424da63a64df2/js/webflow.schunk.36b8fb49256177c8.js') }}"
            type="text/javascript"
        ></script>
        <script
            src="{{ asset('webflow/cdn.prod.website-files.com/684f91df71b424da63a64df2/js/webflow.schunk.c420de7292bd144f.js') }}"
            type="text/javascript"
        ></script>
        <script
            src="{{ asset('webflow/cdn.prod.website-files.com/684f91df71b424da63a64df2/js/webflow.6c3c1598.fd0020846f95ba5b.js') }}"
            type="text/javascript"
        ></script>
        @stack('scripts')
    </body>
</html>

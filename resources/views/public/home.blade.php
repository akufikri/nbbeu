<x-site-layout
    title="NBBEU | Banking Executive Union Sabah, Malaysia"
    description="NBBEU is the union for banking executives in Sabah, Malaysia. We work for members' rights, welfare and professional development. Join today."
>
    <header id="hero" class="hero-marquee" data-count-trigger>
        <div class="max-w-7xl mx-auto px-6">
            <div class="hero-marquee__row scroll-reveal">
                <div class="hero-marquee__top">
                    <p class="hero-stat__eyebrow">North Borneo Banking Executive Union (NBBEU)</p>
                    <h1 class="hero-marquee__headline">Banking Executives Union of Sabah</h1>
                    <p class="hero-marquee__tagline">Solidarity · Professionalism · Welfare</p>
                    <div class="hero-stat__actions mt-4">
                        <a href="#program" class="cta-outline">Join Membership</a>
                        <a href="#tentang-kami" class="cta-text">Learn more →</a>
                    </div>
                </div>
                <div class="hero-marquee__image">
                    <img src="{{ asset('assets/images/hero.png') }}" alt="" srcset="">
                </div>
            </div>

            <div class="hero-marquee__sub scroll-reveal">
                <p class="hero-stat__lede">We are an organisation representing banking sector executives in Malaysia, working for the rights, welfare and professional development of our members. United for a fair and prosperous future.</p>
            </div>

            <div class="supporting-stats" id="stats">
                <div class="stat-row">
                    <div class="stat-cell">
                        <div class="tnum"><span class="count-up" data-target="2024">0</span></div>
                        <p>Year Established</p>
                    </div>
                    <div class="stat-cell">
                        <div class="tnum"><span class="count-up" data-target="{{ $memberCompaniesCount }}">0</span></div>
                        <p>Banking Institutions</p>
                    </div>
                    <div class="stat-cell">
                        <div class="tnum">100%</div>
                        <p>Official Accreditation</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section id="tentang-kami" class="prose-section px-6 scroll-reveal">
        <p class="lede"><span class="head-inline">Our History.</span> The North Borneo Banking Executive Union was established in 2024 as the official platform for banking executives in Malaysia, particularly in Sabah. Our establishment aimed to strengthen the professional voice within the financial industry and to be a strategic partner with management in fostering harmonious and progressive industrial relations.</p>
        <p><span class="head-inline">Vision.</span> To be the foremost, respected banking executive union that is a reference point in advocating members' rights and welfare professionally, ethically and with integrity.</p>
        <p><span class="head-inline">Mission.</span> Defending Rights — guaranteeing employment justice and member welfare at all levels. Developing Professionals — providing continuous training, certification and career development. Promoting Integrity — fostering an inclusive, transparent and highly ethical work culture. Building Partnerships — cooperating with financial institutions, government and professional bodies to advance the industry.</p>
    </section>

    <section id="profil-ahli" class="py-24 bg-nb-paper-final">
        <div class="max-w-7xl mx-auto px-6">
            <div class="scroll-reveal">
                <span class="head-numbered__label">01 — Members</span>
                <h2 class="mt-2">Member Profile</h2>
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
<p>Active Members</p>
                    </div>
                    <div class="stat-cell">
                        <div class="tnum">Sabah</div>
                        <p>Branch Coverage</p>
                    </div>
                    <div class="stat-cell">
                        <div class="tnum"><span class="count-up" data-target="{{ $memberCompaniesCount }}">0</span></div>
                        <p>Banking Institutions</p>
                    </div>
                </div>
            </div>

            <p class="mt-6 font-sans text-sm text-nb-ink-muted max-w-2xl">
                We are proud to have more than {{ $approvedMembersCount }} active members from various banking institutions across the state of Sabah. Our members comprise dedicated professional officers driving excellence in the banking industry.
            </p>
        </div>
    </section>

    <section id="program" class="py-24">
        <div class="max-w-7xl mx-auto px-6">
              <div class="scroll-reveal">
                <span class="head-numbered__label">02 — Programs</span>
                <h2 class="mt-2">Programs &amp; Activities</h2>
                <p class="mt-2 font-sans text-sm">Access to executive career development and the strongest regional network.</p>
            </div>

            <dl class="spec-sheet scroll-reveal">
                <div class="spec-row">
                    <dt><span class="spec-row__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v5c0 4.5-2.9 7.9-7 10-4.1-2.1-7-5.5-7-10V6l7-3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/></svg></span>Professional Development</dt>
                    <dd>Leadership seminars, risk management workshops, banking ethics, and professional certification programs.</dd>
                </div>
                <div class="spec-row">
                    <dt><span class="spec-row__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v3m0 0L6 9m6-3l6 3M4 9l2 5H2l2-5zm16 0l2 5h-4l2-5zM4 9h4m8 0h4M6 14v3a2 2 0 002 2h8a2 2 0 002-2v-3M10 21h4"/></svg></span>Advocacy &amp; Welfare</dt>
                    <dd>Fighting for employment benefits, work-life balance, job security and social protection for members.</dd>
                </div>
                <div class="spec-row">
                    <dt><span class="spec-row__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="8" r="3"/><path stroke-linecap="round" d="M4 20v-1a5 5 0 015-5h0a5 5 0 015 5v1"/><circle cx="17" cy="9" r="2.5"/><path stroke-linecap="round" d="M15.5 14.2A4.5 4.5 0 0120 18.5V20"/></svg></span>Corporate Social Responsibility (CSR)</dt>
                    <dd>Community programs, charitable contributions, and strategic cooperation with financial institutions for community development.</dd>
                </div>
            </dl>
        </div>
    </section>

    <section id="how-to-join" class="py-24 bg-sig-navy text-white">
        <div class="max-w-7xl mx-auto px-6">
            <div class="scroll-reveal">
                <span class="head-numbered__label">03 — Procedure</span>
                <h2 class="mt-2 text-white">Official Registration Procedure</h2>
                <p class="mt-2 text-white/70 font-sans text-sm">A transparent process to maintain the integrity standards of union leadership.</p>
            </div>

            <div class="step-sequence mt-10 scroll-reveal">
                <div class="step">
                    <span class="step__num">01</span>
                    <div>
                        <h4>Prepare Your Sponsors</h4>
                        <p>You must be nominated by 2 active NBBEU members (Proposer and Seconder) as part of your application.</p>
                    </div>
                </div>
                <div class="step">
                    <span class="step__num">02</span>
                    <div>
                        <h4>Complete the Form</h4>
                        <p>Complete your executive profile, career record, and institutional verification online.</p>
                    </div>
                </div>
                <div class="step">
                    <span class="step__num">03</span>
                    <div>
                        <h4>Registration Fee</h4>
                        <p>Settle the annual union administrative fee through fast and secure bank transfer.</p>
                    </div>
                </div>
                <div class="step">
                    <span class="step__num">04</span>
                    <div>
                        <h4>Admin Review</h4>
                        <p>The NBBEU Honorary Board will verify your eligibility within 5-7 working days.</p>
                    </div>
                </div>
                <div class="step">
                    <span class="step__num">05</span>
                    <div>
                        <h4>Certification</h4>
                        <p>Receive your physical and digital Member Card and a formally recognised Honorary Certificate.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="org-structure" class="py-24">
        <div class="max-w-7xl mx-auto px-6">
            <div class="mb-10 scroll-reveal flex items-start justify-between gap-6 flex-wrap">
                <div>
                    <h2>Leadership Structure 2025-2027</h2>
                    <p class="mt-2 text-nb-ink-muted font-sans text-sm">Led by dedicated professionals guiding the direction of the union.</p>
                </div>
                <a href="{{ route('org-structure') }}" class="cta-text">View Full Organisation Structure →</a>
            </div>

            @if ($orgChart->isEmpty())
                <p class="text-nb-ink-muted font-sans text-sm">The organisation structure will be updated soon.</p>
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
<span class="head-numbered__label">04 — Advocacy</span>
                <h2 class="mt-2">Rights &amp; Advocacy</h2>
                <p class="mt-2 font-sans text-sm text-nb-ink-muted">NBBEU is committed to fighting for the following for every member.</p>
            </div>

            <dl class="spec-sheet scroll-reveal">
                <div class="spec-row">
                    <dt>Employment Justice</dt>
                    <dd>Fair, transparent and discrimination-free processes.</dd>
                </div>
                <div class="spec-row">
                    <dt>Member Protection</dt>
                    <dd>Standing against exploitation and ensuring labour law compliance.</dd>
                </div>
                <div class="spec-row">
                    <dt>Global Standards</dt>
                    <dd>Aligning policies with best international practice.</dd>
                </div>
                <div class="spec-row">
                    <dt>Holistic Wellbeing</dt>
                    <dd>Awareness campaigns on mental health and professionalism at work.</dd>
                </div>
            </dl>
        </div>
    </section>

    <section id="galeri" class="py-24">
        <div class="max-w-7xl mx-auto px-6">
            <div class="mb-10 scroll-reveal flex items-start justify-between gap-6 flex-wrap">
                <div>
                    <span class="head-numbered__label">05 — Gallery</span>
                    <h2 class="mt-2">Photo Gallery</h2>
                    <p class="mt-2 font-sans text-sm text-nb-ink-muted">Moments from NBBEU activities and programs.</p>
                </div>
                <a href="{{ route('gallery.index') }}" class="cta-text">View All Gallery →</a>
            </div>

            @if ($galleryPreview->isEmpty())
                <p class="text-nb-ink-muted font-sans text-sm">No gallery photos yet.</p>
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
                <span class="head-numbered__label">06 — Publications</span>
                <h2 class="mt-2">News &amp; Publications</h2>
                <p class="mt-2 font-sans text-sm text-nb-ink-muted">Industry Analysis, Official Statements, NBBEU Bulletins and Annual Reports.</p>
            </div>

            @if ($latestPosts->isEmpty())
                <p class="text-nb-ink-muted font-sans text-sm">No articles published yet.</p>
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
                <h2 class="text-xl">Latest Analysis &amp; News</h2>
                <a href="{{ route('blog.index') }}" class="cta-text">View all news &amp; publications →</a>
            </div>
        </div>
    </section>

    <section id="cta-final" class="py-24 bg-nb-paper-final">
        <div class="max-w-7xl mx-auto px-6 text-center scroll-reveal">
            <h2>United for a Fair and Prosperous Future</h2>
            <p class="font-sans text-nb-ink mt-4 max-w-xl mx-auto leading-relaxed">Join us in strengthening the standards of professionalism, integrity and the future of financial leadership in Sabah.</p>
            <a href="{{ route('registration.create') }}" class="cta-outline mt-8 inline-flex">Apply for Membership Today</a>
        </div>
    </section>
</x-site-layout>

<x-webflow-layout
    title="NBBEU - North Borneo Banking Executive Union"
    description="Uniting North Borneo's banking executives for collaboration, advocacy, and professional development."
>
    <section class="section_hero">
        <div class="padding-global is-hero">
            <div class="container-large">
                <div class="hero_layout">
                    <div class="max-width-medium">
                        <div
                            class="tag"
                        >
                            NBBEU Membership
                        </div>
                        <div class="spacer-medium"></div>
                        <h1
                        >
                            Uniting North Borneo's Banking
                            Executives
                        </h1>
                    </div>
                    <div class="max-content is-26rem">
                        <div
                            class="text-color-secondary"
                        >
                            Join a professional community of banking
                            executives across North Borneo — collaborate,
                            grow, and lead together.
                        </div>
                        <div class="spacer-xxlarge"></div>
                        <div
                            class="button-wrapper"
                        >
                            <a
                                data-wf--button--variant="base"
                                href="{{ route('registration.create') }}"
                                class="button w-inline-block"
                                ><div class="button-content">
                                    <div class="button-text is-one">
                                        Become a Member
                                    </div>
                                    <div class="button-text is-two">
                                        Become a Member
                                    </div>
                                </div></a
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div
            class="hero_img"
        >
            <div
                class="img-wrapper"
            >
                <img
                    src="{{ asset('webflow/cdn.prod.website-files.com/684f91df71b424da63a64df2/684f9eb0d0b3b34f086009cc_hero_igm.webp') }}"
                    loading="lazy"
                    sizes="(max-width: 1920px) 100vw, 1920px"
                    alt="NBBEU members collaborating"
                    class="img"
                />
            </div>
        </div>
    </section>
    <section class="section_statistic">
        <div class="padding-global padding-section-medium">
            <div class="container-large">
                <div class="releted_codes w-embed w-script">
                    <script
                        defer
                        src="{{ asset('webflow/cdn.jsdelivr.net/npm/@finsweet/attributes-numbercount@1/numbercount.js') }}"
                    ></script>
                </div>
                <div class="w-layout-grid grid-3gap">
                    <div class="content">
                        <div class="">
                            <div class="tag">Statistic</div>
                            <div class="spacer-medium"></div>
                            <h2 class="">
                                A Growing Network, <br />Real Impact
                            </h2>
                            <div class="spacer-medium"></div>
                            <div
                                class="text-color-secondary"
                            >
                                NBBEU brings together banking
                                executives from across North Borneo
                                to collaborate, share expertise, and
                                grow together. Our numbers reflect a
                                community that keeps expanding.
                            </div>
                        </div>
                        <div class="w-layout-grid statistic_grid">
                            <div class="statistic_item">
                                <div class="">
                                    Active Members
                                </div>
                                <div
                                    class="statistic_group"
                                >
                                    <div
                                        fs-numbercount-duration="2000"
                                        fs-numbercount-element="number"
                                        fs-numbercount-start="0"
                                        fs-numbercount-end="{{ $approvedMembersCount }}"
                                        class="text-number"
                                    >
                                        {{ $approvedMembersCount }}
                                    </div>
                                </div>
                            </div>
                            <div class="statistic_item">
                                <div class="">
                                    Member Companies
                                </div>
                                <div
                                    class="statistic_group"
                                >
                                    <div
                                        fs-numbercount-duration="2000"
                                        fs-numbercount-element="number"
                                        fs-numbercount-start="0"
                                        fs-numbercount-end="{{ $memberCompaniesCount }}"
                                        class="text-number"
                                    >
                                        {{ $memberCompaniesCount }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="statistic_img"
                    >
                        <div
                            class="img-wrapper"
                        >
                            <img
                                src="{{ asset('webflow/cdn.prod.website-files.com/684f91df71b424da63a64df2/6855af7c1c943dabc6cb86cd_img2.png') }}"
                                loading="lazy"
                                sizes="(max-width: 1128px) 100vw, 1128px"
                                alt=""
                                class="img"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="benefits" class="section_service">
        <div class="padding-global padding-section-medium">
            <div class="container-large">
                <div>
                    <div class="tag">
                        Membership Benefits
                    </div>
                    <div class="spacer-medium"></div>
                    <h2 class="">
                        Your Partner in Professional, <br />Growth
                        &amp; Recognition
                    </h2>
                    <div class="spacer-medium"></div>
                    <div class="max-description-large">
                        <div
                            class="text-color-secondary"
                        >
                            NBBEU exists to strengthen the banking
                            profession across North Borneo — through
                            networking, advocacy, professional
                            development, and recognition for the
                            executives who lead the industry
                            forward.
                        </div>
                    </div>
                    <div class="spacer-huge"></div>
                    <div class="button-wrapper">
                        <a
                            data-wf--button--variant="base"
                            href="{{ route('registration.create') }}"
                            class="button w-inline-block"
                            ><div class="button-content">
                                <div class="button-text is-one">
                                    Become a Member
                                </div>
                                <div class="button-text is-two">
                                    Become a Member
                                </div>
                            </div></a
                        >
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
                <div class="service_grid">
                    <div class="service_img">
                        <div class="img-wrapper">
                            <img
                                src="{{ asset('webflow/cdn.prod.website-files.com/684f91df71b424da63a64df2/6855afa02ca5297f1daa0f53_image.png') }}"
                                loading="lazy"
                                sizes="(max-width: 767px) 100vw, 768px"
                                alt=""
                                class="img"
                            />
                        </div>
                    </div>
                    <div class="">
                        <div class="w-dyn-list">
                            <div role="list" class="w-dyn-items">
                                <div
                                    role="listitem"
                                    class="w-dyn-item"
                                >
                                    <div
                                        class="card is-link w-inline-block"
                                        ><div>
                                            <div class="icon">
                                                <img
                                                    src="{{ asset('webflow/cdn.prod.website-files.com/684f91df71b424da63a64e18/6852d2fe35a6e0b8d804e13c_mdi_heart.svg') }}"
                                                    loading="lazy"
                                                    alt=""
                                                    class="icon-1x1-medium"
                                                />
                                            </div>
                                            <div
                                                class="spacer-xlarge"
                                            ></div>
                                            <div class="text-xl">
                                                Networking &amp; Collaboration
                                            </div>
                                        </div>
                                        <div class="spacer">
                                            <div
                                                style="
                                                    height: 12.5rem;
                                                "
                                                class="spacer-desktop"
                                            ></div>
                                            <div
                                                style="height: 5rem"
                                                class="spacer-tablet"
                                            ></div>
                                            <div
                                                style="height: 2rem"
                                                class="spacer-mobile"
                                            ></div>
                                        </div>
                                        <div class="align-auto">
                                            <div
                                                class="text-color-secondary"
                                            >
                                                Connect with fellow
                                                banking executives
                                                across North Borneo
                                                to share ideas and
                                                build relationships
                                            </div>
                                        </div></div
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="">
                        <div class="w-dyn-list">
                            <div role="list" class="w-dyn-items">
                                <div
                                    role="listitem"
                                    class="w-dyn-item"
                                >
                                    <div
                                        class="card is-link w-inline-block"
                                        ><div>
                                            <div class="icon">
                                                <img
                                                    src="{{ asset('webflow/cdn.prod.website-files.com/684f91df71b424da63a64e18/6852d2c3aa3c3d27c8a040a1_tabler_home-filled.svg') }}"
                                                    loading="lazy"
                                                    alt=""
                                                    class="icon-1x1-medium"
                                                />
                                            </div>
                                            <div
                                                class="spacer-xlarge"
                                            ></div>
                                            <div class="text-xl">
                                                Industry Advocacy
                                            </div>
                                        </div>
                                        <div class="spacer">
                                            <div
                                                style="
                                                    height: 12.5rem;
                                                "
                                                class="spacer-desktop"
                                            ></div>
                                            <div
                                                style="height: 5rem"
                                                class="spacer-tablet"
                                            ></div>
                                            <div
                                                style="height: 2rem"
                                                class="spacer-mobile"
                                            ></div>
                                        </div>
                                        <div class="align-auto">
                                            <div
                                                class="text-color-secondary"
                                            >
                                                A unified voice
                                                representing the
                                                interests of banking
                                                executives to
                                                regulators and industry
                                                bodies
                                            </div>
                                        </div></div
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="">
                        <div class="w-dyn-list">
                            <div role="list" class="w-dyn-items">
                                <div
                                    role="listitem"
                                    class="w-dyn-item"
                                >
                                    <div
                                        class="card is-link w-inline-block"
                                        ><div>
                                            <div class="icon">
                                                <img
                                                    src="{{ asset('webflow/cdn.prod.website-files.com/684f91df71b424da63a64e18/6852d2a243fa4127661f1de5_material-symbols_hiking.svg') }}"
                                                    loading="lazy"
                                                    alt=""
                                                    class="icon-1x1-medium"
                                                />
                                            </div>
                                            <div
                                                class="spacer-xlarge"
                                            ></div>
                                            <div class="text-xl">
                                                Professional Development
                                            </div>
                                        </div>
                                        <div class="spacer">
                                            <div
                                                style="
                                                    height: 12.5rem;
                                                "
                                                class="spacer-desktop"
                                            ></div>
                                            <div
                                                style="height: 5rem"
                                                class="spacer-tablet"
                                            ></div>
                                            <div
                                                style="height: 2rem"
                                                class="spacer-mobile"
                                            ></div>
                                        </div>
                                        <div class="align-auto">
                                            <div
                                                class="text-color-secondary"
                                            >
                                                Grow your career
                                                with resources,
                                                events, and training
                                                built for banking
                                                leaders
                                            </div>
                                        </div></div
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="">
                        <div class="w-dyn-list">
                            <div role="list" class="w-dyn-items">
                                <div
                                    role="listitem"
                                    class="w-dyn-item"
                                >
                                    <div
                                        class="card is-link w-inline-block"
                                        ><div>
                                            <div class="icon">
                                                <img
                                                    src="{{ asset('webflow/cdn.prod.website-files.com/684f91df71b424da63a64e18/6852d27b825d5d8fd604bb45_basil_bag-solid.svg') }}"
                                                    loading="lazy"
                                                    alt=""
                                                    class="icon-1x1-medium"
                                                />
                                            </div>
                                            <div
                                                class="spacer-xlarge"
                                            ></div>
                                            <div class="text-xl">
                                                Recognition &amp; Certification
                                            </div>
                                        </div>
                                        <div class="spacer">
                                            <div
                                                style="
                                                    height: 12.5rem;
                                                "
                                                class="spacer-desktop"
                                            ></div>
                                            <div
                                                style="height: 5rem"
                                                class="spacer-tablet"
                                            ></div>
                                            <div
                                                style="height: 2rem"
                                                class="spacer-mobile"
                                            ></div>
                                        </div>
                                        <div class="align-auto">
                                            <div
                                                class="text-color-secondary"
                                            >
                                                Be recognized for
                                                your leadership and
                                                contribution to the
                                                banking community
                                            </div>
                                        </div></div
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="service_img">
                        <div class="img-wrapper">
                            <img
                                src="{{ asset('webflow/cdn.prod.website-files.com/684f91df71b424da63a64df2/6855af9f35a779340c720572_image-1.png') }}"
                                loading="lazy"
                                sizes="(max-width: 767px) 100vw, 768px"
                                alt=""
                                class="img"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="section_trust">
        <div class="padding-global padding-section-medium">
            <div class="container-large">
                <div class="w-layout-grid grid-3gap">
                    <div
                        class="statistic_img"
                    >
                        <div class="img-wrapper">
                            <img
                                src="{{ asset('webflow/cdn.prod.website-files.com/684f91df71b424da63a64df2/685340716f3c59e2d43f2226_image-large.webp') }}"
                                loading="lazy"
                                sizes="(max-width: 1128px) 100vw, 1128px, 100vw"
                                alt=""
                                class="img"
                            />
                        </div>
                    </div>
                    <div
                        id="w-node-db77a3fc-85cd-1cf5-6ef4-8b2e1d14e6cf-1d14e6c8"
                        class="content"
                    >
                        <div>
                            <div class="tag">
                                Why NBBEU
                            </div>
                            <div class="spacer-medium"></div>
                            <h2 class="">Why join us?</h2>
                            <div class="spacer-medium"></div>
                            <div
                                class="text-color-secondary"
                            >
                                We are committed to strengthening the
                                banking profession in North Borneo.
                                Our dedicated leadership is here to
                                support your growth every step of
                                the way.
                            </div>
                        </div>
                        <div class="w-layout-grid list_grid">
                            <div
                                class="list_item"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="100%"
                                    viewBox="0 0 28 28"
                                    fill="none"
                                    class="icon-1x1-large"
                                >
                                    <path
                                        d="M12.3673 16.1007L9.85899 13.5923C9.6451 13.3784 9.37287 13.2715 9.04232 13.2715C8.71176 13.2715 8.43954 13.3784 8.22565 13.5923C8.01176 13.8062 7.90482 14.0784 7.90482 14.409C7.90482 14.7395 8.01176 15.0118 8.22565 15.2257L11.5507 18.5506C11.784 18.784 12.0562 18.9007 12.3673 18.9007C12.6784 18.9007 12.9507 18.784 13.184 18.5506L19.7757 11.959C19.9895 11.7451 20.0965 11.4729 20.0965 11.1423C20.0965 10.8118 19.9895 10.5395 19.7757 10.3257C19.5618 10.1118 19.2895 10.0048 18.959 10.0048C18.6284 10.0048 18.3562 10.1118 18.1423 10.3257L12.3673 16.1007ZM14.0007 25.6673C12.3868 25.6673 10.8701 25.3609 9.45065 24.748C8.03121 24.1351 6.79649 23.304 5.74649 22.2548C4.69649 21.2056 3.86543 19.9709 3.25332 18.5506C2.64121 17.1304 2.33476 15.6138 2.33399 14.0007C2.33321 12.3875 2.63965 10.8709 3.25332 9.45065C3.86699 8.03043 4.69804 6.79571 5.74649 5.74648C6.79493 4.69726 8.02965 3.86621 9.45065 3.25332C10.8717 2.64043 12.3883 2.33398 14.0007 2.33398C15.613 2.33398 17.1297 2.64043 18.5507 3.25332C19.9717 3.86621 21.2064 4.69726 22.2548 5.74648C23.3033 6.79571 24.1347 8.03043 24.7492 9.45065C25.3636 10.8709 25.6697 12.3875 25.6673 14.0007C25.665 15.6138 25.3585 17.1304 24.748 18.5506C24.1374 19.9709 23.3064 21.2056 22.2548 22.2548C21.2033 23.304 19.9685 24.1355 18.5507 24.7491C17.1328 25.3628 15.6161 25.6689 14.0007 25.6673Z"
                                        fill="#0A0A0A"
                                    ></path>
                                </svg>
                                <div>
                                    <div class="text-xl">
                                        A Trusted Union
                                    </div>
                                    <div
                                        class="spacer-medium"
                                    ></div>
                                    <div
                                        class="text-color-secondary"
                                    >
                                        From onboarding to
                                        leadership opportunities, we
                                        guide members every step of
                                        the way.
                                    </div>
                                </div>
                            </div>
                            <div
                                class="list_item"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="100%"
                                    viewBox="0 0 28 28"
                                    fill="none"
                                    class="icon-1x1-large"
                                >
                                    <path
                                        d="M12.3673 16.1007L9.85899 13.5923C9.6451 13.3784 9.37287 13.2715 9.04232 13.2715C8.71176 13.2715 8.43954 13.3784 8.22565 13.5923C8.01176 13.8062 7.90482 14.0784 7.90482 14.409C7.90482 14.7395 8.01176 15.0118 8.22565 15.2257L11.5507 18.5506C11.784 18.784 12.0562 18.9007 12.3673 18.9007C12.6784 18.9007 12.9507 18.784 13.184 18.5506L19.7757 11.959C19.9895 11.7451 20.0965 11.4729 20.0965 11.1423C20.0965 10.8118 19.9895 10.5395 19.7757 10.3257C19.5618 10.1118 19.2895 10.0048 18.959 10.0048C18.6284 10.0048 18.3562 10.1118 18.1423 10.3257L12.3673 16.1007ZM14.0007 25.6673C12.3868 25.6673 10.8701 25.3609 9.45065 24.748C8.03121 24.1351 6.79649 23.304 5.74649 22.2548C4.69649 21.2056 3.86543 19.9709 3.25332 18.5506C2.64121 17.1304 2.33476 15.6138 2.33399 14.0007C2.33321 12.3875 2.63965 10.8709 3.25332 9.45065C3.86699 8.03043 4.69804 6.79571 5.74649 5.74648C6.79493 4.69726 8.02965 3.86621 9.45065 3.25332C10.8717 2.64043 12.3883 2.33398 14.0007 2.33398C15.613 2.33398 17.1297 2.64043 18.5507 3.25332C19.9717 3.86621 21.2064 4.69726 22.2548 5.74648C23.3033 6.79571 24.1347 8.03043 24.7492 9.45065C25.3636 10.8709 25.6697 12.3875 25.6673 14.0007C25.665 15.6138 25.3585 17.1304 24.748 18.5506C24.1374 19.9709 23.3064 21.2056 22.2548 22.2548C21.2033 23.304 19.9685 24.1355 18.5507 24.7491C17.1328 25.3628 15.6161 25.6689 14.0007 25.6673Z"
                                        fill="#0A0A0A"
                                    ></path>
                                </svg>
                                <div>
                                    <div class="text-xl">
                                        A Community, Not Just a
                                        Membership
                                    </div>
                                    <div
                                        class="spacer-medium"
                                    ></div>
                                    <div
                                        class="text-color-secondary"
                                    >
                                        We tailor engagement to fit
                                        your career stage, from
                                        rising executives to
                                        seasoned board members.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="how-it-works" class="section_solutions">
        <div class="padding-global padding-section-medium">
            <div class="container-large">
                <div
                    style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem;"
                >
                    <h2 class="h3" style="margin: 0;">
                        A Simple Path to <br />NBBEU Membership
                    </h2>
                    <div
                        class="button-wrapper"
                    >
                        <a
                            data-wf--button--variant="base"
                            href="{{ route('registration.create') }}"
                            class="button w-inline-block"
                            ><div class="button-content">
                                <div class="button-text is-one">
                                    Become a Member
                                </div>
                                <div class="button-text is-two">
                                    Become a Member
                                </div>
                            </div></a
                        >
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
                <div class="accordion_list">
                    <div class="accordion is-active">
                        <div class="accordion_toggle js-accordion-toggle">
                            <div class="accordion_title">
                                <div class="accordion_number">01</div>
                                <div class="text-xl">Register Online</div>
                            </div>
                            <div class="accordion_icon">+</div>
                        </div>
                        <div class="accordion_content">
                            <div class="accordion_text">
                                <div class="spacer-small"></div>
                                <div class="text-color-secondary">
                                    Fill in your details through our simple online
                                    registration form — it only takes a few minutes.
                                </div>
                                <div class="spacer-medium"></div>
                            </div>
                        </div>
                    </div>
                    <div class="accordion">
                        <div class="accordion_toggle js-accordion-toggle">
                            <div class="accordion_title">
                                <div class="accordion_number">02</div>
                                <div class="text-xl">Pay Membership Fee</div>
                            </div>
                            <div class="accordion_icon">+</div>
                        </div>
                        <div class="accordion_content" style="display: none;">
                            <div class="accordion_text">
                                <div class="spacer-small"></div>
                                <div class="text-color-secondary">
                                    Complete your membership payment securely online
                                    via Toyyibpay.
                                </div>
                                <div class="spacer-medium"></div>
                            </div>
                        </div>
                    </div>
                    <div class="accordion">
                        <div class="accordion_toggle js-accordion-toggle">
                            <div class="accordion_title">
                                <div class="accordion_number">03</div>
                                <div class="text-xl">Admin Review &amp; Approval</div>
                            </div>
                            <div class="accordion_icon">+</div>
                        </div>
                        <div class="accordion_content" style="display: none;">
                            <div class="accordion_text">
                                <div class="spacer-small"></div>
                                <div class="text-color-secondary">
                                    Our team reviews your application and approves
                                    qualified applicants, activating your official
                                    NBBEU membership.
                                </div>
                                <div class="spacer-medium"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="blog" class="section_blog">
        <div class="padding-global padding-section-medium">
            <div class="container-large">
                <div
                    style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem;"
                >
                    <h2 class="h3" style="margin: 0;">
                        News &amp; Insights
                    </h2>
                    <div
                        class="button-wrapper"
                    >
                        <a
                            data-wf--button--variant="base"
                            href="{{ route('blog.index') }}"
                            class="button w-inline-block"
                            ><div class="button-content">
                                <div class="button-text is-one">
                                    View all
                                </div>
                                <div class="button-text is-two">
                                    View all
                                </div>
                            </div></a
                        >
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
                @if ($latestPosts->isEmpty())
                    <div class="text-color-secondary">
                        No posts yet.
                    </div>
                @else
                    <div class="w-dyn-list">
                        <div role="list" class="blog_list w-dyn-items">
                            @foreach ($latestPosts as $post)
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
                @endif
            </div>
        </div>
    </section>
    <section class="section_cta">
        <div class="padding-global is-cta">
            <div class="container-large">
                <div class="cta_wrap">
                    <div class="card bg-secondary">
                        <div>
                            <img
                                src="{{ asset('assets/images/logo.png') }}"
                                loading="lazy"
                                alt="NBBEU"
                                style="height: 3rem; width: auto;"
                            />
                            <div class="spacer-xlarge"></div>
                            <h2 class="h3">Ready to Join NBBEU?</h2>
                            <div class="spacer-xlarge"></div>
                            <div>
                                Take the next step in your career —
                                become a member of North Borneo's
                                banking executive community today.
                            </div>
                        </div>
                        <div class="spacer">
                            <div
                                style="height: 14.5rem"
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
                        <div class="button-wrapper">
                            <a
                                data-wf--button--variant="base"
                                href="{{ route('registration.create') }}"
                                class="button w-inline-block"
                                ><div class="button-content">
                                    <div class="button-text is-one">
                                        Become a Member
                                    </div>
                                    <div class="button-text is-two">
                                        Become a Member
                                    </div>
                                </div></a
                            >
                        </div>
                        <div class="spacer-medium"></div>
                        <div>
                            <a
                                href="{{ route('registration.status') }}"
                                class="text-link"
                                >Check Application Status</a
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                document.querySelectorAll(".js-accordion-toggle").forEach(function (toggle) {
                    toggle.addEventListener("click", function () {
                        const item = toggle.closest(".accordion");
                        const content = item.querySelector(".accordion_content");
                        const icon = item.querySelector(".accordion_icon");
                        const isOpen = item.classList.contains("is-active");

                        document.querySelectorAll(".accordion.is-active").forEach(function (open) {
                            if (open !== item) {
                                open.classList.remove("is-active");
                                open.querySelector(".accordion_content").style.display = "none";
                                open.querySelector(".accordion_icon").textContent = "+";
                            }
                        });

                        item.classList.toggle("is-active", !isOpen);
                        content.style.display = isOpen ? "none" : "block";
                        icon.textContent = isOpen ? "+" : "−";
                    });
                });
            });
        </script>
    @endpush
</x-webflow-layout>

/**
 * NBBEU — site interactivity
 * jQuery 3.7.1
 */

$(document).ready(function() {

    // ponytail: single-threshold hysteresis via rAF, no ResizeObserver
    const $navbar = $('#navbar');
    const $navbarRow = $('.masthead-nav-row');
    let isCompact = false;
    let ticking = false;

    function applyNavbarState() {
        const scrollTop = $(window).scrollTop();
        const shouldBeCompact = isCompact ? scrollTop > 32 : scrollTop > 80;
        if (shouldBeCompact !== isCompact) {
            isCompact = shouldBeCompact;
            $navbar.toggleClass('shadow-[0_4px_20px_rgba(35,42,51,0.05)] border-transparent is-compact', isCompact);
        }
        ticking = false;
    }

    $(window).on('scroll.navbar', function() {
        if (!ticking) {
            window.requestAnimationFrame(applyNavbarState);
            ticking = true;
        }
    });

    // Mobile menu
    const $mobileToggle = $('#mobile-menu-toggle');
    const $mobileMenu = $('#mobile-menu');

    $mobileToggle.on('click', function(e) {
        e.stopPropagation();
        $mobileMenu.slideToggle(250);
    });

    $('.mobile-link').on('click', function() {
        $mobileMenu.slideUp(200);
    });

    $(document).on('click', function(e) {
        if (!$mobileMenu.is(e.target) && $mobileMenu.has(e.target).length === 0 && !$mobileToggle.is(e.target)) {
            $mobileMenu.slideUp(200);
        }
    });

    // User menu dropdown
    const $userMenuToggle = $('#user-menu-toggle');
    const $userMenuDropdown = $('#user-menu-dropdown');

    $userMenuToggle.on('click', function(e) {
        e.stopPropagation();
        const isOpen = !$userMenuDropdown.hasClass('hidden');
        $userMenuDropdown.toggleClass('hidden', isOpen);
        $userMenuToggle.attr('aria-expanded', String(!isOpen));
    });

    $(document).on('click', function(e) {
        if (!$userMenuDropdown.is(e.target) && $userMenuDropdown.has(e.target).length === 0 && !$userMenuToggle.is(e.target)) {
            $userMenuDropdown.addClass('hidden');
            $userMenuToggle.attr('aria-expanded', 'false');
        }
    });

    // Smooth scroll
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const targetAttr = this.getAttribute('href');
        if (targetAttr === '#') return;
        const $targetElement = $(targetAttr);
        if ($targetElement.length) {
            const offset = $navbarRow.outerHeight() || 80;
            $('html, body').animate({ scrollTop: $targetElement.offset().top - offset }, 600);
        }
    });

    // Count-up
    function initializeCountUp($el) {
        const target = parseInt($el.attr('data-target'), 10);
        let start = null;
        const dur = 1800;
        function step(ts) {
            if (!start) start = ts;
            const p = Math.min((ts - start) / dur, 1);
            $el.text(Math.floor((p * (2 - p)) * target));
            if (p < 1) window.requestAnimationFrame(step);
            else $el.text(target);
        }
        window.requestAnimationFrame(step);
    }

    // Scroll reveal
    const $reveal = $('.scroll-reveal');
    const $stats = $('[data-count-trigger]');
    let statsDone = false;

    function checkViewport() {
        const bottom = $(window).scrollTop() + $(window).height();
        $reveal.each(function() {
            const $el = $(this);
            if (!$el.hasClass('revealed') && bottom > ($el.offset().top + 40)) {
                $el.addClass('revealed');
            }
        });
        if (!statsDone && $stats.length && bottom > ($stats.offset().top + 100)) {
            statsDone = true;
            $('.count-up').each(function() { initializeCountUp($(this)); });
        }
    }

    $(window).on('scroll.reveal resize.reveal', checkViewport);
    checkViewport();

    // Blog filter + pagination
    const $blogList = $('#blog-list');
    if ($blogList.length) {
        const $search = $('#blog-search');
        const $chips = $('.filter-chip');
        const $empty = $('#blog-empty');
        const $pagination = $('#blog-pagination');
        const $articles = $blogList.find('.index-list__row');
        const PAGE_SIZE = 3;
        let activeCat = 'all';
        let currentPage = 1;

        function matchingArticles() {
            const q = $search.val().trim().toLowerCase();
            return $articles.filter(function() {
                const $a = $(this);
                return (activeCat === 'all' || $a.attr('data-category') === activeCat) &&
                       (!q || ($a.attr('data-search') || '').indexOf(q) !== -1);
            });
        }

        function renderPagination(totalPages) {
            $pagination.empty();
            if (totalPages <= 1) return;

            function addButton(label, page, opts) {
                opts = opts || {};
                const $btn = $('<button type="button"></button>').text(label);
                if (opts.active) $btn.addClass('is-active');
                if (opts.ellipsis) $btn.addClass('pagination__ellipsis').prop('disabled', true);
                if (opts.disabled) $btn.prop('disabled', true);
                if (!opts.ellipsis) {
                    $btn.on('click', function() {
                        currentPage = page;
                        applyBlogFilter();
                    });
                }
                $pagination.append($btn);
            }

            addButton('←', currentPage - 1, { disabled: currentPage === 1 });
            for (let page = 1; page <= totalPages; page++) {
                if (page === 1 || page === totalPages || Math.abs(page - currentPage) <= 1) {
                    addButton(String(page), page, { active: page === currentPage });
                } else if (Math.abs(page - currentPage) === 2) {
                    addButton('…', page, { ellipsis: true });
                }
            }
            addButton('→', currentPage + 1, { disabled: currentPage === totalPages });
        }

        function applyBlogFilter() {
            const $matches = matchingArticles();
            const totalPages = Math.max(1, Math.ceil($matches.length / PAGE_SIZE));
            currentPage = Math.min(currentPage, totalPages);

            $articles.hide();
            $matches.slice((currentPage - 1) * PAGE_SIZE, currentPage * PAGE_SIZE).show();

            $empty.prop('hidden', $matches.length > 0);
            renderPagination(totalPages);
        }

        $search.on('input', function() {
            currentPage = 1;
            applyBlogFilter();
        });
        $chips.on('click', function() {
            $chips.removeClass('is-active');
            $(this).addClass('is-active');
            activeCat = $(this).attr('data-filter');
            currentPage = 1;
            applyBlogFilter();
        });

        applyBlogFilter();
    }

    // Placeholder click log
    $('.btn-primary, #login-link').on('click', function(e) {
        const id = $(this).attr('id') || 'Button';
        console.log(`[NBBEU] ${id}: "${$(this).text().trim()}" clicked`);
    });
});
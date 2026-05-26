(function () {
    'use strict';

    function initMobileNav() {
        var sidebar  = document.getElementById('main-sidebar');
        var overlay  = document.getElementById('mob-overlay');
        var openBtn  = document.querySelector('.mob-menu-btn');
        var closeBtn = document.querySelector('.sidebar-close-btn');

        if (!sidebar || !overlay || !openBtn) return;

        function openDrawer() {
            sidebar.classList.add('is-open');
            overlay.classList.add('is-open');
            document.body.classList.add('mob-nav-open');
            openBtn.setAttribute('aria-expanded', 'true');
            overlay.setAttribute('aria-hidden', 'false');
            // Focus the close button so keyboard users can dismiss immediately
            if (closeBtn) closeBtn.focus();
        }

        function closeDrawer() {
            sidebar.classList.remove('is-open');
            overlay.classList.remove('is-open');
            document.body.classList.remove('mob-nav-open');
            openBtn.setAttribute('aria-expanded', 'false');
            overlay.setAttribute('aria-hidden', 'true');
            openBtn.focus();
        }

        openBtn.addEventListener('click', function () {
            sidebar.classList.contains('is-open') ? closeDrawer() : openDrawer();
        });

        overlay.addEventListener('click', closeDrawer);

        if (closeBtn) {
            closeBtn.addEventListener('click', closeDrawer);
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('is-open')) {
                closeDrawer();
            }
        });

        // NOTE: Removed the nav link auto-close listener.
        // Nav links navigate to a new page — the drawer resets naturally on load.
        // Auto-closing was causing the hamburger button to lose focus and appear broken.

        // Focus trap
        sidebar.addEventListener('keydown', function (e) {
            if (e.key !== 'Tab' || !sidebar.classList.contains('is-open')) return;

            var focusable = Array.from(
                sidebar.querySelectorAll('a, button, [tabindex]:not([tabindex="-1"])')
            ).filter(function (el) {
                return !el.disabled && el.offsetParent !== null;
            });

            if (!focusable.length) return;

            var first = focusable[0];
            var last  = focusable[focusable.length - 1];

            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault(); last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault(); first.focus();
            }
        });

        // Resize guard
        window.addEventListener('resize', function () {
            if (window.innerWidth > 860 && sidebar.classList.contains('is-open')) {
                sidebar.classList.remove('is-open');
                overlay.classList.remove('is-open');
                document.body.classList.remove('mob-nav-open');
                openBtn.setAttribute('aria-expanded', 'false');
                overlay.setAttribute('aria-hidden', 'true');
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobileNav);
    } else {
        initMobileNav();
    }
})();
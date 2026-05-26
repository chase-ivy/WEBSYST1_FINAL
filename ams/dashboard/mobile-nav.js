/**
 * mobile-nav.js  —  Gibraltar AMS
 * Shared mobile drawer behaviour for Admin, Teacher, Student portals.
 *
 * Requires in HTML (added by each portal's nav PHP):
 *   1. A <button class="mob-menu-btn" aria-label="Open menu" aria-expanded="false"
 *               aria-controls="main-sidebar"> inside .topbar
 *   2. A <div class="mob-overlay" id="mob-overlay" aria-hidden="true"></div>
 *      placed just before </body>
 *   3. The .sidebar element must have id="main-sidebar"
 *   4. A <button class="sidebar-close-btn" aria-label="Close menu"> inside
 *      .sidebar-brand (added by nav PHP files)
 *
 * No external dependencies.
 */

(function () {
    'use strict';

    function initMobileNav() {
        var sidebar     = document.getElementById('main-sidebar');
        var overlay     = document.getElementById('mob-overlay');
        var openBtn     = document.querySelector('.mob-menu-btn');
        var closeBtn    = document.querySelector('.sidebar-close-btn');

        // Guard: if any required element is missing, bail silently
        if (!sidebar || !overlay || !openBtn) return;

        /* ── helpers ─────────────────────────────────────────── */

        function openDrawer() {
            sidebar.classList.add('is-open');
            overlay.classList.add('is-open');
            document.body.classList.add('mob-nav-open');
            openBtn.setAttribute('aria-expanded', 'true');
            overlay.setAttribute('aria-hidden', 'false');
            // Move focus into drawer for keyboard/screen-reader users
            var firstLink = sidebar.querySelector('a, button');
            if (firstLink) firstLink.focus();
        }

        function closeDrawer() {
            sidebar.classList.remove('is-open');
            overlay.classList.remove('is-open');
            document.body.classList.remove('mob-nav-open');
            openBtn.setAttribute('aria-expanded', 'false');
            overlay.setAttribute('aria-hidden', 'true');
            // Return focus to the trigger that opened the drawer
            openBtn.focus();
        }

        /* ── event listeners ─────────────────────────────────── */

        // Open via hamburger button
        openBtn.addEventListener('click', function () {
            var isOpen = sidebar.classList.contains('is-open');
            if (isOpen) {
                closeDrawer();
            } else {
                openDrawer();
            }
        });

        // Close via overlay click
        overlay.addEventListener('click', closeDrawer);

        // Close via × button inside drawer
        if (closeBtn) {
            closeBtn.addEventListener('click', closeDrawer);
        }

        // Close on Escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('is-open')) {
                closeDrawer();
            }
        });

        // Close drawer automatically when a nav link is tapped
        // (navigates to new page — drawer closes on any link click inside sidebar)
        sidebar.querySelectorAll('nav a').forEach(function (link) {
            link.addEventListener('click', function () {
                // Only auto-close on mobile (sidebar is a drawer)
                if (window.innerWidth <= 860) {
                    closeDrawer();
                }
            });
        });

        /* ── focus trap inside drawer ────────────────────────── */
        /*
         * When the drawer is open, Tab and Shift+Tab cycle only within it.
         * This satisfies WCAG 2.1 AA focus trap requirement for off-canvas panels.
         */
        sidebar.addEventListener('keydown', function (e) {
            if (e.key !== 'Tab') return;
            if (!sidebar.classList.contains('is-open')) return;

            var focusable = Array.from(
                sidebar.querySelectorAll('a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])')
            ).filter(function (el) {
                return !el.disabled && el.offsetParent !== null;
            });

            if (focusable.length === 0) return;

            var first = focusable[0];
            var last  = focusable[focusable.length - 1];

            if (e.shiftKey) {
                // Shift+Tab: wrap from first → last
                if (document.activeElement === first) {
                    e.preventDefault();
                    last.focus();
                }
            } else {
                // Tab: wrap from last → first
                if (document.activeElement === last) {
                    e.preventDefault();
                    first.focus();
                }
            }
        });

        /* ── resize guard ────────────────────────────────────── */
        /*
         * If the user rotates to landscape / resizes to desktop width
         * while the drawer is open, clean up the open state so the
         * sidebar just shows normally without the overlay.
         */
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

    // Run after DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMobileNav);
    } else {
        initMobileNav();
    }
})();

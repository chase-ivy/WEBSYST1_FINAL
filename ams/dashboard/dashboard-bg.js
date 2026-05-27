/**
 * GIBRALTAR AMES — Professional Dashboard Theme Toggle
 * ──────────────────────────────────────────────────────
 * Manages light/dark mode for admin, teacher, and student dashboards.
 *
 * FEATURES
 *   - Reads saved preference from localStorage on page load (no flash)
 *   - Injects a sun/moon toggle button into the topbar
 *   - Respects system prefers-color-scheme as the initial default
 *   - Smooth CSS transitions handle the visual switch
 *
 * USAGE
 *   Place this script before </body> on every dashboard page:
 *   <script src="../dashboard-theme.js"></script>
 *
 *   No PHP changes. No dependencies.
 */

(function () {
    'use strict';

    var STORAGE_KEY = 'gibames-theme';
    var DARK_CLASS  = 'dark';      /* data-theme value */

    /* ── 1. Resolve initial theme (runs before DOM paint) ── */
    var savedTheme  = null;
    try { savedTheme = localStorage.getItem(STORAGE_KEY); } catch (e) {}

    var prefersDark = window.matchMedia &&
                      window.matchMedia('(prefers-color-scheme: dark)').matches;

    /* Priority: saved > system preference > light default */
    var isDark = savedTheme === 'dark' || (!savedTheme && prefersDark);

    /* Apply immediately to <html> — before any paint — prevents flash */
    if (isDark) {
        document.documentElement.setAttribute('data-theme', 'dark');
    }

    /* ── 2. Inject toggle button into topbar ── */
    function buildToggleBtn(currentlyDark) {
        var btn = document.createElement('button');
        btn.className    = 'theme-toggle-btn mob-menu-btn';  /* reuse mob sizing */
        btn.className    = 'theme-toggle-btn';
        btn.type         = 'button';
        btn.setAttribute('aria-label', currentlyDark ? 'Switch to light mode' : 'Switch to dark mode');
        btn.setAttribute('aria-pressed', String(currentlyDark));
        btn.title        = currentlyDark ? 'Light mode' : 'Dark mode';

        /* Sun SVG (shown in light mode → click to go dark) */
        var sunSvg = [
            '<svg class="icon-sun" viewBox="0 0 24 24" aria-hidden="true">',
            '  <circle cx="12" cy="12" r="5"/>',
            '  <line x1="12" y1="1"  x2="12" y2="3"/>',
            '  <line x1="12" y1="21" x2="12" y2="23"/>',
            '  <line x1="4.22" y1="4.22"   x2="5.64" y2="5.64"/>',
            '  <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>',
            '  <line x1="1"  y1="12" x2="3"  y2="12"/>',
            '  <line x1="21" y1="12" x2="23" y2="12"/>',
            '  <line x1="4.22" y1="19.78"  x2="5.64" y2="18.36"/>',
            '  <line x1="18.36" y1="5.64"  x2="19.78" y2="4.22"/>',
            '</svg>'
        ].join('');

        /* Moon SVG (shown in dark mode → click to go light) */
        var moonSvg = [
            '<svg class="icon-moon" viewBox="0 0 24 24" aria-hidden="true">',
            '  <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>',
            '</svg>'
        ].join('');

        btn.innerHTML = sunSvg + moonSvg;
        return btn;
    }

    function injectToggleBtn() {
        var topbar = document.querySelector('.topbar');
        if (!topbar || topbar.querySelector('.theme-toggle-btn')) return;

        var currentlyDark = document.documentElement.getAttribute('data-theme') === 'dark';
        var btn = buildToggleBtn(currentlyDark);

        /* Insert before topbar-label (right side of bar) */
        var label = topbar.querySelector('.topbar-label');
        if (label) {
            topbar.insertBefore(btn, label);
        } else {
            topbar.appendChild(btn);
        }

        btn.addEventListener('click', toggleTheme);
    }

    /* ── 3. Toggle function ── */
    function toggleTheme() {
        var html       = document.documentElement;
        var isDarkNow  = html.getAttribute('data-theme') === 'dark';
        var nextDark   = !isDarkNow;

        /* Apply */
        if (nextDark) {
            html.setAttribute('data-theme', 'dark');
        } else {
            html.removeAttribute('data-theme');
        }

        /* Persist */
        try {
            localStorage.setItem(STORAGE_KEY, nextDark ? 'dark' : 'light');
        } catch (e) {}

        /* Update button aria */
        var btn = document.querySelector('.theme-toggle-btn');
        if (btn) {
            btn.setAttribute('aria-label', nextDark ? 'Switch to light mode' : 'Switch to dark mode');
            btn.setAttribute('aria-pressed', String(nextDark));
            btn.title = nextDark ? 'Light mode' : 'Dark mode';
        }
    }

    /* ── 4. Wire up on DOM ready ── */
    function init() {
        injectToggleBtn();

        /* Re-apply theme in case html attr was stripped during PHP render */
        var saved = null;
        try { saved = localStorage.getItem(STORAGE_KEY); } catch (e) {}
        if (saved === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    /* ── 5. Listen for OS-level theme change (optional quality-of-life) ── */
    try {
        window.matchMedia('(prefers-color-scheme: dark)')
              .addEventListener('change', function (e) {
            /* Only auto-switch if user hasn't manually set a preference */
            var stored = null;
            try { stored = localStorage.getItem(STORAGE_KEY); } catch (ex) {}
            if (!stored) {
                if (e.matches) {
                    document.documentElement.setAttribute('data-theme', 'dark');
                } else {
                    document.documentElement.removeAttribute('data-theme');
                }
                var btn = document.querySelector('.theme-toggle-btn');
                if (btn) {
                    btn.setAttribute('aria-pressed', String(e.matches));
                    btn.setAttribute('aria-label', e.matches ? 'Switch to light mode' : 'Switch to dark mode');
                }
            }
        });
    } catch (e) {}

})();
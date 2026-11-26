(() => {
    'use strict';

    const root = document.documentElement;
    const storageKey = 'twfood-admin-theme';
    const themeButtons = [...document.querySelectorAll('[data-theme-choice]')];
    const systemPreference = window.matchMedia('(prefers-color-scheme: dark)');

    function resolveTheme(preference) {
        return preference === 'system' ? (systemPreference.matches ? 'dark' : 'light') : preference;
    }

    function applyTheme(preference, persist = true) {
        const safePreference = ['light', 'dark', 'system'].includes(preference) ? preference : 'system';
        const theme = resolveTheme(safePreference);
        root.dataset.theme = theme;
        root.dataset.themePreference = safePreference;
        root.setAttribute('data-bs-theme', theme);
        themeButtons.forEach((button) => {
            const active = button.dataset.themeChoice === safePreference;
            button.setAttribute('aria-pressed', String(active));
        });
        if (persist) {
            try { localStorage.setItem(storageKey, safePreference); } catch (_) { /* storage may be blocked */ }
        }
    }

    let initialPreference = 'system';
    try { initialPreference = localStorage.getItem(storageKey) || root.dataset.themePreference || 'system'; } catch (_) {}
    applyTheme(initialPreference, false);

    themeButtons.forEach((button) => {
        button.addEventListener('click', () => applyTheme(button.dataset.themeChoice));
    });
    systemPreference.addEventListener?.('change', () => {
        if ((root.dataset.themePreference || 'system') === 'system') applyTheme('system', false);
    });

    const toggle = document.getElementById('adminSidebarToggle');
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('adminSidebarBackdrop');

    function closeSidebar() {
        document.body.classList.remove('admin-sidebar-open');
        toggle?.setAttribute('aria-expanded', 'false');
        if (backdrop) backdrop.hidden = true;
    }
    function openSidebar() {
        document.body.classList.add('admin-sidebar-open');
        toggle?.setAttribute('aria-expanded', 'true');
        if (backdrop) backdrop.hidden = false;
    }
    toggle?.addEventListener('click', () => {
        document.body.classList.contains('admin-sidebar-open') ? closeSidebar() : openSidebar();
    });
    backdrop?.addEventListener('click', closeSidebar);
    sidebar?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => {
        if (window.matchMedia('(max-width: 900px)').matches) closeSidebar();
    }));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeSidebar();
    });
    window.addEventListener('resize', () => {
        if (!window.matchMedia('(max-width: 900px)').matches) closeSidebar();
    });

    // Subtle entrance effect; no dependency on a framework or external CDN.
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (!reduceMotion) {
        const targets = document.querySelectorAll('.page-content > .row, .page-content > .card, .page-content > .dash-hero');
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('admin-reveal');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.06 });
            targets.forEach((target) => observer.observe(target));
        } else {
            targets.forEach((target) => target.classList.add('admin-reveal'));
        }
    }
})();

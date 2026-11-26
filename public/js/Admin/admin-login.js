(function () {
    'use strict';

    var root = document.documentElement;
    var themeButton = document.getElementById('login-theme-toggle');
    var storageKey = 'twfood-admin-login-theme';

    function applyTheme(theme) {
        var isLight = theme === 'light';
        root.setAttribute('data-theme', isLight ? 'light' : 'dark');
        if (!themeButton) return;
        themeButton.setAttribute('aria-pressed', String(isLight));
        themeButton.setAttribute('aria-label', isLight ? 'Aktifkan tema gelap' : 'Aktifkan tema terang');
        themeButton.innerHTML = isLight
            ? '<i class="ti ti-moon" aria-hidden="true"></i><span class="theme-toggle-label">Mode gelap</span>'
            : '<i class="ti ti-sun" aria-hidden="true"></i><span class="theme-toggle-label">Mode terang</span>';
    }

    var initialTheme = root.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
    applyTheme(initialTheme);

    if (themeButton) {
        themeButton.addEventListener('click', function () {
            var nextTheme = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            applyTheme(nextTheme);
            try { localStorage.setItem(storageKey, nextTheme); } catch (error) { /* storage may be unavailable */ }
        });
    }

    document.querySelectorAll('[data-password-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            var inputId = button.getAttribute('data-password-toggle');
            var input = document.getElementById(inputId);
            if (!input) return;

            var showPassword = input.type === 'password';
            input.type = showPassword ? 'text' : 'password';
            button.setAttribute('aria-pressed', String(showPassword));
            button.setAttribute('aria-label', showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            var icon = button.querySelector('i');
            if (icon) icon.className = showPassword ? 'ti ti-eye-off' : 'ti ti-eye';
        });
    });
})();

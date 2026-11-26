(() => {
  'use strict';
  const root = document.documentElement;
  const themeButton = document.querySelector('[data-theme-toggle]');
  const themeLabel = document.querySelector('[data-theme-label]');
  const themeIcon = document.querySelector('[data-theme-icon]');
  const media = window.matchMedia('(prefers-color-scheme: light)');
  const getSaved = () => { try { return localStorage.getItem('twfood-theme') || 'system'; } catch (_) { return 'system'; } };
  let preference = getSaved();
  const applyTheme = () => {
    const actual = preference === 'system' ? (media.matches ? 'light' : 'dark') : preference;
    root.dataset.theme = actual;
    const labels = { system: 'Tema: Sistem', dark: 'Tema: Gelap', light: 'Tema: Terang' };
    if (themeLabel) themeLabel.textContent = labels[preference];
    if (themeIcon) themeIcon.textContent = actual === 'dark' ? '☾' : '☀';
    if (themeButton) themeButton.setAttribute('aria-label', `Tema saat ini ${labels[preference].replace('Tema: ', '').toLowerCase()}. Klik untuk mengganti tema.`);
    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) meta.setAttribute('content', actual === 'dark' ? '#17120D' : '#FAF7F1');
  };
  applyTheme();
  themeButton?.addEventListener('click', () => {
    preference = preference === 'system' ? 'dark' : preference === 'dark' ? 'light' : 'system';
    try { localStorage.setItem('twfood-theme', preference); } catch (_) {}
    applyTheme();
  });
  media.addEventListener?.('change', () => { if (preference === 'system') applyTheme(); });

  const menu = document.querySelector('.tw-menu');
  const menuButton = document.querySelector('.tw-mobile-toggle');
  menuButton?.addEventListener('click', () => {
    const expanded = menuButton.getAttribute('aria-expanded') === 'true';
    menuButton.setAttribute('aria-expanded', String(!expanded));
    menuButton.setAttribute('aria-label', expanded ? 'Buka menu navigasi' : 'Tutup menu navigasi');
    menu?.classList.toggle('is-open', !expanded);
  });
  menu?.querySelectorAll('a').forEach(link => link.addEventListener('click', () => {
    menu.classList.remove('is-open');
    menuButton?.setAttribute('aria-expanded', 'false');
  }));

  const revealTargets = [
    '.tw-hero > *', '.tw-sec > *', '.produk-head', '.produk-grid > *', '.resep-grid > *',
    '.outlet-grid > *', '.bahan-grid > *', '.grosir-langkah > *', '.content-list > *',
    '.produk-info', '.grosir-tabel-wrap', '.resep-detail'
  ].flatMap(selector => Array.from(document.querySelectorAll(selector)));
  const uniqueTargets = [...new Set(revealTargets)].filter(el => !el.closest('.tw-nav,.tw-footer'));
  uniqueTargets.forEach(el => el.setAttribute('data-reveal', ''));
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  if (window.gsap && window.ScrollTrigger) {
    gsap.registerPlugin(ScrollTrigger);
    uniqueTargets.forEach((el, index) => {
      gsap.fromTo(el, { autoAlpha: 0, y: 22 }, {
        autoAlpha: 1, y: 0, duration: .65, delay: (index % 3) * .06,
        ease: 'power2.out', clearProps: 'transform',
        scrollTrigger: { trigger: el, start: 'top 92%', once: true }
      });
    });
    gsap.fromTo('.tw-hero-img img', { scale: 1.06 }, { scale: 1, duration: 1.1, ease: 'power2.out' });
  } else {
    uniqueTargets.forEach(el => el.classList.add('is-visible'));
  }
})();

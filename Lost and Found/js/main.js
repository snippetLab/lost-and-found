/* ==========================================
   Campus Lost & Found Portal - Shared JS
   ========================================== */

(function() {
  'use strict';

  // --- Mobile Navigation ---
  const hamburger = document.querySelector('.header-hamburger');
  const nav = document.querySelector('.header-nav');
  if (hamburger && nav) {
    hamburger.addEventListener('click', () => {
      nav.classList.toggle('open');
      const icon = hamburger.querySelector('.material-symbols-outlined');
      if (icon) icon.textContent = nav.classList.contains('open') ? 'close' : 'menu';
    });
    document.addEventListener('click', (e) => {
      if (!hamburger.contains(e.target) && !nav.contains(e.target)) {
        nav.classList.remove('open');
        const icon = hamburger.querySelector('.material-symbols-outlined');
        if (icon) icon.textContent = 'menu';
      }
    });
  }

  // --- Mobile Navigation (Report Found header variant) ---
  const rfHamburger = document.querySelector('.mobile-menu-btn');
  const rfNav = document.querySelector('.header .nav');
  if (rfHamburger && rfNav) {
    rfHamburger.addEventListener('click', () => {
      rfNav.classList.toggle('open');
      const icon = rfHamburger.querySelector('.material-symbols-outlined');
      if (icon) icon.textContent = rfNav.classList.contains('open') ? 'close' : 'menu';
    });
    document.addEventListener('click', (e) => {
      if (!rfHamburger.contains(e.target) && !rfNav.contains(e.target)) {
        rfNav.classList.remove('open');
        const icon = rfHamburger.querySelector('.material-symbols-outlined');
        if (icon) icon.textContent = 'menu';
      }
    });
  }

  // --- Set Active Nav ---
  const currentPage = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.header-nav a, .footer-nav a').forEach(link => {
    const href = link.getAttribute('href');
    if (href === currentPage || (currentPage === '' && href === 'index.html')) {
      link.classList.add('active');
    }
  });

  // --- Password Toggle ---
  document.querySelectorAll('[data-toggle-password]').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = document.getElementById(btn.dataset.togglePassword);
      if (!target) return;
      const isPassword = target.type === 'password';
      target.type = isPassword ? 'text' : 'password';
      const icon = btn.querySelector('.material-symbols-outlined');
      if (icon) icon.textContent = isPassword ? 'visibility_off' : 'visibility';
    });
  });

  // --- Modal Functions ---
  window.openModal = function(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.add('active');
  };
  window.closeModal = function(id) {
    const modal = document.getElementById(id);
    if (modal) modal.classList.remove('active');
  };
  document.querySelectorAll('.modal-overlay').forEach(modal => {
    modal.addEventListener('click', (e) => {
      if (e.target === modal) modal.classList.remove('active');
    });
  });

  // --- Color Swatch Selection ---
  document.querySelectorAll('.color-swatch-group').forEach(group => {
    group.querySelectorAll('.color-swatch').forEach(swatch => {
      swatch.addEventListener('click', () => {
        group.querySelectorAll('.color-swatch').forEach(s => s.classList.remove('active'));
        swatch.classList.add('active');
      });
    });
  });

  // --- Radio Card Selection ---
  document.querySelectorAll('.radio-card-group').forEach(group => {
    group.querySelectorAll('.radio-card').forEach(card => {
      card.addEventListener('click', () => {
        group.querySelectorAll('.radio-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        const radio = card.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
      });
    });
  });

  // --- Time Period Selector ---
  document.querySelectorAll('.time-selector').forEach(group => {
    group.querySelectorAll('button').forEach(btn => {
      btn.addEventListener('click', () => {
        group.querySelectorAll('button').forEach(b => {
          b.classList.remove('bg-primary', 'text-on-primary', 'shadow-sm');
          b.classList.add('bg-surface-ground', 'text-on-surface');
        });
        btn.classList.remove('bg-surface-ground', 'text-on-surface');
        btn.classList.add('bg-primary', 'text-on-primary', 'shadow-sm');
      });
    });
  });

  // --- Admin dropdowns (notifications / profile) ---
  const dropToggles = document.querySelectorAll('[data-drop-toggle]');
  if (dropToggles.length) {
    dropToggles.forEach(toggle => {
      toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        const drop = toggle.closest('.admin-drop');
        if (!drop) return;
        const isOpen = drop.classList.contains('is-open');
        document.querySelectorAll('.admin-drop.is-open').forEach(d => d.classList.remove('is-open'));
        if (!isOpen) drop.classList.add('is-open');
      });
    });
    document.addEventListener('click', () => {
      document.querySelectorAll('.admin-drop.is-open').forEach(d => d.classList.remove('is-open'));
    });
  }

  // --- Smooth Scroll to Top ---
  window.scrollToTop = function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  // --- Theme Switcher (Light / Dark) ---
  const THEME_KEY = 'foubdly-theme';

  function themeFromSystem() {
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

  function getSavedTheme() {
    try {
      const t = localStorage.getItem(THEME_KEY);
      return (t === 'light' || t === 'dark') ? t : null;
    } catch (e) {
      return null;
    }
  }

  function applyTheme(theme, animate) {
    const root = document.documentElement;
    if (animate) {
      root.classList.add('theme-anim');
      clearTimeout(window.__foubdlyThemeAnimTimer);
      window.__foubdlyThemeAnimTimer = setTimeout(function() {
        root.classList.remove('theme-anim');
      }, 350);
    }
    root.setAttribute('data-theme', theme);
    if ('style' in root && 'colorScheme' in root.style) root.style.colorScheme = theme;

    const next = theme === 'dark' ? 'light' : 'dark';
    document.querySelectorAll('[data-theme-toggle]').forEach(function(btn) {
      btn.setAttribute('aria-label', 'Switch to ' + next + ' mode');
      btn.setAttribute('title', 'Switch to ' + next + ' mode');
      btn.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
    });

    document.dispatchEvent(new CustomEvent('foubdly:themechange', { detail: { theme: theme } }));
  }

  // Exposed for charts / analytics that may want to re-render on theme change.
  window.foubdlyTheme = {
    get: function() { return document.documentElement.getAttribute('data-theme') || 'light'; },
    set: function(theme, persist) {
      if (theme !== 'light' && theme !== 'dark') return;
      if (persist) { try { localStorage.setItem(THEME_KEY, theme); } catch (e) {} }
      applyTheme(theme, true);
    }
  };

  // Wire up the toggle buttons (present on student + admin headers).
  document.querySelectorAll('[data-theme-toggle]').forEach(function(btn) {
    btn.addEventListener('click', function() {
      const root = document.documentElement;
      const current = root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
      const next = current === 'dark' ? 'light' : 'dark';
      try { localStorage.setItem(THEME_KEY, next); } catch (e) {}
      applyTheme(next, true);
    });
  });

  // If the user has not explicitly chosen a theme, follow OS changes live.
  if (!getSavedTheme() && window.matchMedia) {
    const mediaDark = window.matchMedia('(prefers-color-scheme: dark)');
    const onSystemChange = function(e) { applyTheme(e.matches ? 'dark' : 'light', true); };
    if (mediaDark.addEventListener) mediaDark.addEventListener('change', onSystemChange);
    else if (mediaDark.addListener) mediaDark.addListener(onSystemChange);
  }

  // Sync toggle labels with whatever theme the head script applied.
  applyTheme(getSavedTheme() || themeFromSystem(), false);

})();

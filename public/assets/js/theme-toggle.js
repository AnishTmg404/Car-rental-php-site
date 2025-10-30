// theme-toggle.js - robust theme toggle using data-bs-theme
(function () {
  const STORAGE_KEY = 'site-theme'; // 'light' or 'dark'
  const html = document.documentElement;
  const BTN_ID = 'themeToggle'; // ensure button uses this id

  function applyTheme(theme) {
    if (theme === 'dark') {
      html.setAttribute('data-bs-theme', 'dark');
    } else {
      html.setAttribute('data-bs-theme', 'light');
    }
  }

  function initTheme() {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved === 'dark' || saved === 'light') {
      applyTheme(saved);
      return;
    }
    // no saved theme: use system preference
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      applyTheme('dark');
    } else {
      applyTheme('light');
    }
  }

  function updateButtonIcon(btn) {
    if (!btn) return;
    const current = html.getAttribute('data-bs-theme');
    if (current === 'dark') {
      btn.innerHTML = '☀️';
      btn.title = 'Switch to light mode';
    } else {
      btn.innerHTML = '🌙';
      btn.title = 'Switch to dark mode';
    }
  }

  function toggleTheme(btn) {
    const current = html.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    applyTheme(next);
    localStorage.setItem(STORAGE_KEY, next);
    updateButtonIcon(btn);
    // optional visual feedback
    if (btn) {
      btn.classList.add('spin');
      setTimeout(() => btn.classList.remove('spin'), 400);
    }
  }

  function init() {
    // Apply theme first
    initTheme();
    
    // Wait a bit for DOM to be fully ready
    setTimeout(() => {
      const btn = document.getElementById(BTN_ID);
      if (btn) {
        updateButtonIcon(btn);
        btn.addEventListener('click', () => toggleTheme(btn));
      }
    }, 100);
    
    // keep theme in sync with system preference changes (optional)
    if (window.matchMedia) {
      const mql = window.matchMedia('(prefers-color-scheme: dark)');
      mql.addEventListener?.('change', (e) => {
        // only change theme if user hasn't explicitly chosen one
        if (!localStorage.getItem(STORAGE_KEY)) {
          applyTheme(e.matches ? 'dark' : 'light');
          updateButtonIcon(document.getElementById(BTN_ID));
        }
      });
    }
  }

  // Make toggleTheme globally available
  window.toggleTheme = function() {
    const btn = document.getElementById(BTN_ID);
    if (btn) {
      toggleTheme(btn);
    }
  };

  // Initialize immediately and also on DOM ready
  init();
  
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  }
})();
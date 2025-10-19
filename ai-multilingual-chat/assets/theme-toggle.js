(function () {
  const THEME_KEY = 'aic_theme_mode';
  const root = document.documentElement;
  
  function prefersDark() {
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  }
  
  function applyTheme(mode) {
    if (!mode || mode === 'auto') {
      root.setAttribute('data-theme', prefersDark() ? 'dark' : 'light');
    } else {
      root.setAttribute('data-theme', mode);
    }
  }
  
  function saveMode(mode) {
    try { 
      localStorage.setItem(THEME_KEY, mode); 
    } catch (e) {
      console.warn('Could not save theme preference:', e);
    }
    applyTheme(mode);
  }
  
  function loadMode() {
    try { 
      return localStorage.getItem(THEME_KEY) || 'auto'; 
    } catch (e) { 
      return 'auto'; 
    }
  }
  
  // Apply theme on page load
  const current = loadMode();
  applyTheme(current);
  
  // Handle theme toggle buttons
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-aic-theme-toggle]');
    if (!btn) return;
    
    const mode = btn.getAttribute('data-aic-theme-toggle');
    saveMode(mode);
    
    // Update button states
    document.querySelectorAll('[data-aic-theme-toggle]').forEach(function(button) {
      const buttonMode = button.getAttribute('data-aic-theme-toggle');
      button.setAttribute('aria-pressed', buttonMode === mode ? 'true' : 'false');
    });
  });
  
  // Handle theme select dropdown
  document.addEventListener('change', function (e) {
    if (e.target && e.target.id === 'aic_theme_mode') {
      saveMode(e.target.value);
    }
  });
  
  // Listen for system theme changes
  if (window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
      const m = loadMode();
      if (m === 'auto') {
        applyTheme('auto');
      }
    });
  }
})();

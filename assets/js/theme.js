(function () {
  var KEY = 'sck_theme';

  function preferred() {
    var saved = null;
    try { saved = localStorage.getItem(KEY); } catch (e) { /* Speicher evtl. blockiert */ }
    if (saved === 'dark' || saved === 'light') return saved;
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

  function apply(theme) {
    document.documentElement.setAttribute('data-bs-theme', theme);
  }

  apply(preferred());

  document.addEventListener('DOMContentLoaded', function () {
    var buttons = document.querySelectorAll('[data-theme-toggle]');
    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        try { localStorage.setItem(KEY, next); } catch (e) { /* Speicher evtl. blockiert */ }
        apply(next);
      });
    });
  });
})();

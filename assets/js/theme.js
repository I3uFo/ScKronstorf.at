(function () {
  function apply(theme) {
    document.documentElement.setAttribute('data-bs-theme', theme);
  }

  var query = window.matchMedia('(prefers-color-scheme: dark)');
  apply(query.matches ? 'dark' : 'light');

  // Reagiert live, falls die Systemeinstellung geändert wird, während die Seite offen ist.
  query.addEventListener('change', function (e) {
    apply(e.matches ? 'dark' : 'light');
  });
})();

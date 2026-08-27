document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('voting-form');
  if (!form) {
    return;
  }

  var selects = Array.prototype.slice.call(document.querySelectorAll('.platz-select'));
  var hinweis = document.getElementById('voting-hinweis');
  var maxPlatz = parseInt(form.dataset.maxPlatz, 10) || 0;

  function aktualisiereZeilen() {
    selects.forEach(function (select) {
      var row = select.closest('.voting-row');
      if (row) {
        row.classList.toggle('ist-belegt', select.value !== '');
      }
    });
  }

  function anzahlVergeben() {
    return selects.filter(function (s) { return s.value !== ''; }).length;
  }

  function zeigeHinweis() {
    if (!hinweis) {
      return;
    }
    hinweis.classList.remove('text-danger');
    hinweis.textContent = anzahlVergeben() + ' von ' + maxPlatz + ' Plätzen vergeben.';
  }

  selects.forEach(function (select) {
    select.addEventListener('change', function () {
      var gewaehlterWert = select.value;
      if (gewaehlterWert !== '') {
        selects.forEach(function (anderer) {
          if (anderer !== select && anderer.value === gewaehlterWert) {
            anderer.value = '';
          }
        });
      }
      aktualisiereZeilen();
      zeigeHinweis();
    });
  });

  form.addEventListener('submit', function (e) {
    if (anzahlVergeben() !== maxPlatz) {
      e.preventDefault();
      if (hinweis) {
        hinweis.classList.add('text-danger');
        hinweis.textContent = 'Bitte vergib zuerst alle ' + maxPlatz + ' Plätze.';
      }
      return;
    }
    var bestaetigt = window.confirm(
      'Achtung: Nach dem Absenden sind keine Änderungen mehr möglich. Stimme jetzt endgültig abschicken?'
    );
    if (!bestaetigt) {
      e.preventDefault();
    }
  });

  aktualisiereZeilen();
  zeigeHinweis();
});

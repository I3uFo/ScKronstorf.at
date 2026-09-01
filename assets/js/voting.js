document.addEventListener('DOMContentLoaded', function () {
  // Info-Button zur Punkteverteilung: per Tap ein-/ausklappbar, damit er auch auf
  // Touch-Geräten ohne echten :hover-Zustand zuverlässig funktioniert.
  var infoTooltip = document.querySelector('.info-tooltip');
  if (infoTooltip) {
    var infoBtn = infoTooltip.querySelector('.info-btn');
    var infoPanel = infoTooltip.querySelector('.info-tooltip-panel');
    if (infoBtn && infoPanel) {
      infoBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        infoPanel.classList.toggle('is-open');
      });
      document.addEventListener('click', function (e) {
        if (!infoTooltip.contains(e.target)) {
          infoPanel.classList.remove('is-open');
        }
      });
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
          infoPanel.classList.remove('is-open');
        }
      });
    }
  }

  var form = document.getElementById('voting-form');
  if (!form) {
    return;
  }

  var selects = Array.prototype.slice.call(document.querySelectorAll('.interpret-select'));
  var hinweis = document.getElementById('voting-hinweis');
  var submitButton = document.getElementById('voting-submit');
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

  function aktualisiereButton() {
    if (submitButton) {
      submitButton.disabled = anzahlVergeben() !== maxPlatz;
    }
  }

  // Sperrt bereits an anderer Stelle gewählte Interpreten in allen Dropdowns
  // (ausgegraut, mit Hinweis auf den belegten Platz), statt sie einfach zurückzusetzen.
  function aktualisiereOptionen() {
    var vergebenAn = {};
    selects.forEach(function (s) {
      if (s.value !== '') {
        vergebenAn[s.value] = s.dataset.platz;
      }
    });

    selects.forEach(function (select) {
      var eigenerWert = select.value;
      Array.prototype.forEach.call(select.options, function (option) {
        if (option.value === '') {
          return;
        }
        if (option.dataset.basistext === undefined) {
          option.dataset.basistext = option.textContent.trim();
        }
        var basistext = option.dataset.basistext;
        if (option.value === eigenerWert) {
          option.disabled = false;
          option.textContent = basistext;
        } else if (Object.prototype.hasOwnProperty.call(vergebenAn, option.value)) {
          option.disabled = true;
          option.textContent = basistext + ' (bereits Platz ' + vergebenAn[option.value] + ')';
        } else {
          option.disabled = false;
          option.textContent = basistext;
        }
      });
    });
  }

  selects.forEach(function (select) {
    select.addEventListener('change', function () {
      aktualisiereOptionen();
      aktualisiereZeilen();
      zeigeHinweis();
      aktualisiereButton();
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

  aktualisiereOptionen();
  aktualisiereZeilen();
  zeigeHinweis();
  aktualisiereButton();
});

(function () {
  var container = document.querySelector('[data-version-id]');
  if (!container) {
    return;
  }
  var versionId = container.dataset.versionId;
  var liste = document.getElementById('beamer-liste');
  if (!liste) {
    return;
  }
  var zeilen = {};

  function baueZeile(item) {
    var row = document.createElement('div');
    row.className = 'beamer-balken-row';
    row.innerHTML =
      '<div class="beamer-balken-platz">' + item.platz + '.</div>' +
      '<div class="beamer-balken-track"><div class="beamer-balken-fill"></div></div>' +
      '<div class="beamer-balken-summe"></div>';
    return row;
  }

  function aktualisiereZeile(row, item, maxSumme) {
    var fill = row.querySelector('.beamer-balken-fill');
    var summeEl = row.querySelector('.beamer-balken-summe');
    var breite = maxSumme > 0 ? Math.max(4, (item.summe / maxSumme) * 100) : 0;
    fill.style.width = breite + '%';
    fill.classList.toggle('enthuellt', item.enthuellt);
    fill.textContent = item.enthuellt ? item.name : item.anonym_label;
    summeEl.textContent = item.summe + ' Pkt.';
  }

  function laden() {
    fetch('beamer_state.php?v=' + encodeURIComponent(versionId), { cache: 'no-store' })
      .then(function (antwort) { return antwort.json(); })
      .then(function (daten) {
        if (!daten || daten.fehler || !daten.ergebnisse) {
          return;
        }
        daten.ergebnisse.forEach(function (item) {
          var row = zeilen[item.platz];
          if (!row) {
            row = baueZeile(item);
            zeilen[item.platz] = row;
            liste.appendChild(row);
          }
          aktualisiereZeile(row, item, daten.max_summe);
        });
      })
      .catch(function () {
        /* Beamer bleibt beim letzten bekannten Stand, bis der nächste Poll wieder klappt. */
      });
  }

  laden();
  setInterval(laden, 2500);
})();

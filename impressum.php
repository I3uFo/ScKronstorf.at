<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$pdo = db();
require __DIR__ . '/includes/header.php';
?>
<div class="mx-auto" style="max-width: 760px;">
  <h1 class="mb-4">Impressum</h1>

  <h2 class="h5 mt-4">Medieninhaber und Herausgeber</h2>
  <p>
    SC Kronstorf<br>
    Hargelsbergerstr. 4<br>
    4484 Kronstorf<br>
    Österreich
  </p>

  <h2 class="h5 mt-4">Vereinsdaten</h2>
  <p>
    ZVR-Zahl: 168556429<br>
    E-Mail: <a href="mailto:office@sckronstorf.at">office@sckronstorf.at</a>
  </p>

  <h2 class="h5 mt-4">Vertretungsbefugtes Organ</h2>
  <p>Obmann: Stefan Frühwirth</p>

  <h2 class="h5 mt-4">Vereinszweck</h2>
  <p><?= e(getText($pdo, 'impressum', 'vereinszweck', 'Förderung des Sports im Rahmen der Vereinstätigkeit.')) ?></p>

  <h2 class="h5 mt-4">Kontakt</h2>
  <p>
    Bei Fragen zu dieser Website oder zum Songcontest wenden Sie sich bitte an
    <a href="mailto:office@sckronstorf.at">office@sckronstorf.at</a>.
  </p>

  <h2 class="h5 mt-4">Haftungsausschluss</h2>
  <p>
    Trotz sorgfältiger inhaltlicher Kontrolle übernehmen wir keine Haftung für die Inhalte externer Links.
    Für den Inhalt der verlinkten Seiten sind ausschließlich deren Betreiber verantwortlich.
  </p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>

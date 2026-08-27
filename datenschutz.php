<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$pdo = db();
require __DIR__ . '/includes/header.php';
?>
<div class="mx-auto" style="max-width: 760px;">
  <h1 class="mb-4">Datenschutzerklärung</h1>

  <p><?= e(getText($pdo, 'datenschutz', 'einleitung', 'Der SC Kronstorf nimmt den Schutz personenbezogener Daten ernst.')) ?></p>

  <h2 class="h5 mt-4">Verantwortlicher</h2>
  <p>
    SC Kronstorf<br>
    Hargelsbergerstr. 4, 4484 Kronstorf<br>
    E-Mail: <a href="mailto:office@sckronstorf.at">office@sckronstorf.at</a>
  </p>

  <h2 class="h5 mt-4">Geräte-Token beim Voting</h2>
  <p>
    Um Mehrfachabstimmungen zu verhindern, wird bei der Nutzung der Voting-Seite ein zufällig erzeugter,
    nicht personenbezogener Geräte-Token in einem Cookie in Ihrem Browser gespeichert. Dieser Token enthält
    keine Rückschlüsse auf Ihre Identität und wird ausschließlich verwendet, um zu erkennen, ob von diesem
    Gerät für die aktuelle Songcontest-Ausgabe bereits eine Stimme abgegeben wurde. Eine Zuordnung zu einer
    bestimmten Person findet nicht statt.
  </p>

  <h2 class="h5 mt-4">Speicherdauer</h2>
  <p>
    Die Stimmabgaben werden je Songcontest-Ausgabe (Voting-Version) gespeichert und für die Auszählung sowie
    die anschließende Ergebnispräsentation verwendet. Der Geräte-Token wird für die Dauer von rund 13 Monaten
    im Cookie gespeichert, um wiederholte Mehrfachabstimmungen bei künftigen Ausgaben technisch auszuschließen.
  </p>

  <h2 class="h5 mt-4">Ihre Rechte</h2>
  <p>
    Sie haben das Recht auf Auskunft, Berichtigung, Löschung und Einschränkung der Verarbeitung Ihrer Daten
    sowie das Recht auf Beschwerde bei der österreichischen Datenschutzbehörde. Kontaktieren Sie uns dazu unter
    <a href="mailto:office@sckronstorf.at">office@sckronstorf.at</a>.
  </p>

  <h2 class="h5 mt-4">Hosting</h2>
  <p>
    Diese Website wird auf einem in Österreich/der EU betriebenen Webserver gehostet. Es werden serverseitig
    lediglich technisch notwendige Daten (z. B. Zugriffsprotokolle des Hosting-Anbieters) verarbeitet.
  </p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>

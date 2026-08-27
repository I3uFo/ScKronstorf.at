<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$pdo = db();
$aktivNav = 'voting';

$version = aktuelleVotingVersion($pdo, 'offen');
$fehler = null;
$hatAbgestimmt = false;
$interpreten = [];

if ($version !== null) {
    $deviceToken = getOrCreateDeviceToken();
    $deviceTokenId = deviceTokenId($pdo, $deviceToken);

    $stmt = $pdo->prepare('SELECT 1 FROM stimmabgaben WHERE voting_version_id = ? AND device_token_id = ?');
    $stmt->execute([$version['id'], $deviceTokenId]);
    $hatAbgestimmt = $stmt->fetchColumn() !== false;

    $stmt = $pdo->prepare('SELECT * FROM interpreten WHERE voting_version_id = ? ORDER BY reihenfolge ASC');
    $stmt->execute([$version['id']]);
    $interpreten = $stmt->fetchAll();

    $maxPlatz = min(maxPlatzierung(), count($interpreten));

    if (!$hatAbgestimmt && $_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrfCheck()) {
            $fehler = 'Sicherheitsprüfung fehlgeschlagen. Bitte lade die Seite neu und versuche es erneut.';
        } else {
            $vergeben = [];
            foreach ($interpreten as $interpret) {
                $wert = $_POST['platz_' . $interpret['id']] ?? '';
                if ($wert !== '') {
                    if (!ctype_digit((string)$wert)) {
                        $fehler = 'Ungültige Eingabe.';
                        break;
                    }
                    $vergeben[(int)$interpret['id']] = (int)$wert;
                }
            }

            if ($fehler === null) {
                $platzWerte = array_values($vergeben);
                if (count($vergeben) !== $maxPlatz) {
                    $fehler = 'Bitte vergib alle Plätze von 1 bis ' . $maxPlatz . '.';
                } elseif (count(array_unique($platzWerte)) !== count($platzWerte)) {
                    $fehler = 'Jeder Platz darf nur einmal vergeben werden.';
                } else {
                    sort($platzWerte);
                    if ($platzWerte !== range(1, $maxPlatz)) {
                        $fehler = 'Bitte vergib die Plätze lückenlos von 1 bis ' . $maxPlatz . '.';
                    }
                }
            }

            if ($fehler === null) {
                try {
                    $pdo->beginTransaction();

                    // Der UNIQUE-Index auf (voting_version_id, device_token_id) verhindert
                    // zuverlässig ein doppeltes Absenden, auch bei gleichzeitigen Anfragen.
                    $insertAbgabe = $pdo->prepare(
                        'INSERT INTO stimmabgaben (voting_version_id, device_token_id) VALUES (?, ?)'
                    );
                    $insertAbgabe->execute([$version['id'], $deviceTokenId]);

                    $insertVote = $pdo->prepare(
                        'INSERT INTO votes (voting_version_id, device_token_id, interpret_id, punkte) VALUES (?, ?, ?, ?)'
                    );
                    foreach ($vergeben as $interpretId => $platz) {
                        $insertVote->execute([$version['id'], $deviceTokenId, $interpretId, punkteFuerPlatz($platz)]);
                    }

                    $pdo->commit();

                    header('Location: voting.php');
                    exit;
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    $hatAbgestimmt = true;
                }
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<?php if ($version === null): ?>
  <div class="text-center py-5">
    <h1 class="mb-3"><?= e(getText($pdo, 'voting', 'titel', 'Jetzt abstimmen')) ?></h1>
    <p class="lead"><?= e(getText($pdo, 'voting', 'kein_voting_aktiv', 'Aktuell ist kein Voting geöffnet.')) ?></p>
  </div>

<?php elseif ($hatAbgestimmt): ?>
  <?php
  $sponsoren = $pdo->query('SELECT * FROM sponsoren ORDER BY reihenfolge ASC')->fetchAll();
  $anzahlSponsoren = max(1, count($sponsoren));
  ?>
  <div class="danke-screen">
    <div>
      <h1 class="display-6 fw-bold"><?= e(getText($pdo, 'danke', 'titel', 'Danke für deine Stimme!')) ?></h1>
      <?php if (empty($sponsoren)): ?>
        <p class="lead mt-3" style="white-space: pre-line;"><?= e(getText($pdo, 'danke', 'text', 'Deine Stimme wurde gezählt.')) ?></p>
      <?php else: ?>
        <div class="sponsor-ring" style="--sponsor-count: <?= (int)$anzahlSponsoren ?>;">
          <div class="sponsor-ring-center">
            <p class="lead" style="white-space: pre-line;"><?= e(getText($pdo, 'danke', 'text', 'Deine Stimme wurde gezählt.')) ?></p>
          </div>
          <?php foreach ($sponsoren as $i => $sponsor): ?>
            <?php $ziel = $sponsor['link'] !== '' ? $sponsor['link'] : '#'; ?>
            <a class="sponsor-ring-item" style="--i: <?= (int)$i ?>;" href="<?= e($ziel) ?>" target="_blank" rel="noopener">
              <img src="assets/uploads/<?= e($sponsor['logo_datei']) ?>" alt="<?= e($sponsor['name']) ?>">
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

<?php else: ?>
  <h1 class="mb-2"><?= e(getText($pdo, 'voting', 'titel', 'Jetzt abstimmen')) ?></h1>
  <p class="text-muted mb-4"><?= e(getText($pdo, 'voting', 'anleitung', '')) ?></p>

  <?php if ($fehler !== null): ?>
    <div class="alert alert-danger"><?= e($fehler) ?></div>
  <?php endif; ?>

  <?php if (empty($interpreten)): ?>
    <p>Für dieses Voting wurden noch keine Interpreten hinterlegt.</p>
  <?php else: ?>
    <form method="post" id="voting-form" novalidate data-max-platz="<?= (int)$maxPlatz ?>">
      <?= csrfField() ?>
      <div id="voting-rows">
        <?php foreach ($interpreten as $interpret): ?>
          <div class="voting-row">
            <div class="startnummer">#<?= (int)$interpret['reihenfolge'] ?></div>
            <div class="titel">
              <div class="interpret-name"><?= e($interpret['name']) ?></div>
              <div class="songtitel">
                „<?= e($interpret['songtitel']) ?>“<?= $interpret['originalinterpret'] !== '' ? ' (Original: ' . e($interpret['originalinterpret']) . ')' : '' ?>
              </div>
            </div>
            <select class="form-select platz-select" name="platz_<?= (int)$interpret['id'] ?>" data-interpret-id="<?= (int)$interpret['id'] ?>">
              <option value="">– Platz –</option>
              <?php for ($p = 1; $p <= $maxPlatz; $p++): ?>
                <option value="<?= $p ?>"><?= $p ?>. Platz</option>
              <?php endfor; ?>
            </select>
          </div>
        <?php endforeach; ?>
      </div>
      <div id="voting-hinweis" class="text-muted small mb-3"></div>
      <button type="submit" class="btn btn-primary btn-lg" id="voting-submit">Stimme absenden</button>
    </form>
  <?php endif; ?>
  <script src="assets/js/voting.js"></script>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>

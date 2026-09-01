<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$pdo = db();
$aktivNav = 'voting';
$robots = 'noindex,nofollow';

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
            $gueltigeIds = array_map('intval', array_column($interpreten, 'id'));

            // $vergeben ist platzweise: Platz => gewählte Interpret-ID.
            $vergeben = [];
            for ($p = 1; $p <= $maxPlatz; $p++) {
                $wert = $_POST['interpret_platz_' . $p] ?? '';
                if ($wert === '') {
                    continue;
                }
                if (!ctype_digit((string)$wert) || !in_array((int)$wert, $gueltigeIds, true)) {
                    $fehler = 'Ungültige Eingabe.';
                    break;
                }
                $vergeben[$p] = (int)$wert;
            }

            if ($fehler === null) {
                $interpretIds = array_values($vergeben);
                if (count($vergeben) !== $maxPlatz) {
                    $fehler = 'Bitte wähle für alle Plätze von 1 bis ' . $maxPlatz . ' einen Interpreten aus.';
                } elseif (count(array_unique($interpretIds)) !== count($interpretIds)) {
                    $fehler = 'Jeder Interpret darf nur einmal ausgewählt werden.';
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
                    foreach ($vergeben as $platz => $interpretId) {
                        $insertVote->execute([$version['id'], $deviceTokenId, $interpretId, punkteFuerPlatz($platz)]);
                    }

                    $pdo->commit();

                    header('Location: abstimmen.php');
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
    <h1 class="mb-3">
      <?= e(getText($pdo, 'voting', 'titel', 'Jetzt Abstimmen')) ?><br>
      <span class="voting-subtitle"><?= e(getText($pdo, 'voting', 'titel_zeile2', 'Beim Songcontest Publikums-Voting')) ?></span>
    </h1>
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
  <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
    <div>
      <h1 class="mb-2">
        <?= e(getText($pdo, 'voting', 'titel', 'Jetzt Abstimmen')) ?><br>
        <span class="voting-subtitle"><?= e(getText($pdo, 'voting', 'titel_zeile2', 'Beim Songcontest Publikums-Voting')) ?></span>
      </h1>
      <p class="text-muted mb-1"><?= e(getText($pdo, 'voting', 'anleitung', 'Wähle für jeden Platz von 1 bis 11 deinen Favoriten aus.')) ?></p>
      <p class="text-muted mb-0 text-decoration-underline"><?= e(getText($pdo, 'voting', 'anleitung_hinweis', 'Jeder Interpret kann nur einmal ausgewählt werden.')) ?></p>
    </div>
    <div class="info-tooltip flex-shrink-0">
      <button type="button" class="info-btn" aria-label="Punkteverteilung anzeigen">i</button>
      <div class="info-tooltip-panel">
        <strong>Punkteverteilung</strong>
        <ul>
          <?php for ($p = 1; $p <= $maxPlatz; $p++): ?>
            <li><span>Platz <?= $p ?></span><span><?= punkteFuerPlatz($p) ?> Punkt<?= punkteFuerPlatz($p) === 1 ? '' : 'e' ?></span></li>
          <?php endfor; ?>
        </ul>
      </div>
    </div>
  </div>

  <?php if ($fehler !== null): ?>
    <div class="alert alert-danger"><?= e($fehler) ?></div>
  <?php endif; ?>

  <?php if (empty($interpreten)): ?>
    <p>Für dieses Voting wurden noch keine Interpreten hinterlegt.</p>
  <?php else: ?>
    <form method="post" id="voting-form" novalidate data-max-platz="<?= (int)$maxPlatz ?>">
      <?= csrfField() ?>
      <div id="voting-rows">
        <?php for ($p = 1; $p <= $maxPlatz; $p++): ?>
          <div class="voting-row">
            <div class="startnummer">Platz <?= $p ?></div>
            <select class="form-select flex-grow-1 interpret-select" name="interpret_platz_<?= $p ?>" data-platz="<?= $p ?>">
              <option value="">– Interpret wählen –</option>
              <?php foreach ($interpreten as $interpret): ?>
                <option value="<?= (int)$interpret['id'] ?>">
                  #<?= (int)$interpret['reihenfolge'] ?> <?= e($interpret['name']) ?> – „<?= e($interpret['songtitel']) ?>“
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php endfor; ?>
      </div>
      <div id="voting-hinweis" class="text-muted small mb-3"></div>
      <button type="submit" class="btn btn-primary btn-lg" id="voting-submit" disabled>Stimme absenden</button>
    </form>
  <?php endif; ?>
  <script src="assets/js/voting.js"></script>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>

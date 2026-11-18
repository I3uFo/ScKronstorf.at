<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$pdo = db();
$aktivNav = 'voting';
$robots = 'noindex,nofollow';
$heroBg = true;
$heroBgVariant = 'mic';

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
    <h1 class="mb-3 voting-headline">
      <?= e(getText($pdo, 'voting', 'titel', 'Jetzt Abstimmen')) ?><br>
      <span class="voting-subtitle"><?= e(getText($pdo, 'voting', 'titel_zeile2', 'Beim Songcontest Publikums-Voting')) ?></span>
    </h1>
    <p class="lead"><?= e(getText($pdo, 'voting', 'kein_voting_aktiv', 'Aktuell ist kein Voting geöffnet.')) ?></p>
  </div>

<?php elseif ($hatAbgestimmt): ?>
  <?php $sponsoren = $pdo->query('SELECT * FROM sponsoren WHERE auf_danke_seite = 1 ORDER BY reihenfolge ASC')->fetchAll(); ?>
  <div class="danke-screen">
    <div>
      <h1 class="display-6 fw-bold voting-headline"><?= e(getText($pdo, 'danke', 'titel', 'Danke für deine Stimme!')) ?></h1>
      <p class="lead mt-3" style="white-space: pre-line;"><?= e(getText($pdo, 'danke', 'text', "Ihre Stimme wurde erfolgreich gezählt.\nWir bedanken uns herzlich bei Ihnen sowie bei unseren Sponsoren\nund wünschen Ihnen weiterhin gute Unterhaltung!")) ?></p>
      <?php if (!empty($sponsoren)): ?>
        <div class="sponsor-grid">
          <?php foreach ($sponsoren as $sponsor): ?>
            <?php $ziel = $sponsor['link'] !== '' ? $sponsor['link'] : '#'; ?>
            <a class="sponsor-tile" href="<?= e($ziel) ?>" target="_blank" rel="noopener">
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
      <h1 class="mb-2 voting-headline">
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
    <form method="post" id="voting-form" novalidate data-max-platz="<?= (int)$maxPlatz ?>" class="position-relative">
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

      <div id="confirm-overlay" class="confirm-overlay" role="alertdialog" aria-modal="true" aria-labelledby="confirm-title" hidden>
        <div class="confirm-card">
          <svg class="confirm-icon" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.565zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
          </svg>
          <h2 class="confirm-title" id="confirm-title"><?= e(getText($pdo, 'voting', 'bestaetigung_titel', 'Stimme jetzt absenden?')) ?></h2>
          <p class="confirm-text"><?= e(getText($pdo, 'voting', 'bestaetigung_text', 'Achtung: Nach dem Absenden sind keine Änderungen mehr möglich.')) ?></p>
          <div class="confirm-actions">
            <button type="button" id="confirm-cancel" class="btn btn-outline-theme"><?= e(getText($pdo, 'voting', 'bestaetigung_abbrechen', 'Abbrechen')) ?></button>
            <button type="button" id="confirm-ok" class="btn btn-primary"><?= e(getText($pdo, 'voting', 'bestaetigung_bestaetigen', 'Jetzt absenden')) ?></button>
          </div>
        </div>
      </div>
    </form>
  <?php endif; ?>
  <script src="assets/js/voting.js"></script>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>

<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
requireAdminLogin();

$pdo = db();
$aktivAdminNav = 'beamer';
$fehler = null;

$versionId = (int)($_GET['v'] ?? $_POST['v'] ?? 0);
if ($versionId === 0) {
    $letzte = $pdo->query("SELECT * FROM voting_versionen WHERE status IN ('geschlossen','offen') ORDER BY id DESC LIMIT 1")->fetch();
    $versionId = $letzte !== false ? (int)$letzte['id'] : 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $versionId > 0) {
    if (!csrfCheck()) {
        $fehler = 'Sicherheitsprüfung fehlgeschlagen.';
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM interpreten WHERE voting_version_id = ?');
        $stmt->execute([$versionId]);
        $gesamt = (int)$stmt->fetchColumn();

        $aktion = (string)($_POST['aktion'] ?? '');
        if ($aktion === 'weiter') {
            $pdo->prepare('UPDATE voting_versionen SET beamer_freigabe_platz = MIN(beamer_freigabe_platz + 1, ?) WHERE id = ?')
                ->execute([$gesamt, $versionId]);
        } elseif ($aktion === 'zurueck') {
            $pdo->prepare('UPDATE voting_versionen SET beamer_freigabe_platz = MAX(beamer_freigabe_platz - 1, 0) WHERE id = ?')
                ->execute([$versionId]);
        }
        header('Location: beamer_control.php?v=' . $versionId);
        exit;
    }
}

$versionen = $pdo->query("SELECT * FROM voting_versionen WHERE status IN ('geschlossen','offen') ORDER BY id DESC")->fetchAll();

$version = null;
$ergebnisse = [];
if ($versionId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM voting_versionen WHERE id = ?');
    $stmt->execute([$versionId]);
    $version = $stmt->fetch() ?: null;
    if ($version !== null) {
        $ergebnisse = berechneErgebnisse($pdo, $versionId);
    }
}

require __DIR__ . '/includes/admin_header.php';
?>
<h1 class="h3 mb-4">Beamer-Steuerung</h1>

<?php if ($fehler !== null): ?><div class="alert alert-danger"><?= e($fehler) ?></div><?php endif; ?>

<div class="admin-card mb-4">
  <form method="get" class="d-flex gap-2 align-items-end flex-wrap">
    <div>
      <label class="form-label">Voting-Version</label>
      <select name="v" class="form-select" onchange="this.form.submit()">
        <?php foreach ($versionen as $v): ?>
          <option value="<?= (int)$v['id'] ?>" <?= $versionId === (int)$v['id'] ? 'selected' : '' ?>>
            <?= e($v['name']) ?> (<?= e($v['status']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>
</div>

<?php if ($version === null): ?>
  <p class="text-muted">Keine geeignete Voting-Version gefunden. Ein Voting muss geschlossen sein, um es auszuzählen.</p>
<?php else: ?>
  <?php
  $gesamt = count($ergebnisse);
  $revealed = (int)$version['beamer_freigabe_platz'];
  ?>
  <div class="admin-card mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
      <div>
        <div class="fw-bold"><?= e($version['name']) ?></div>
        <div class="text-muted small"><?= $revealed ?> von <?= $gesamt ?> Plätzen enthüllt (Reihenfolge: letzter Platz zuerst)</div>
      </div>
      <div class="d-flex gap-2">
        <form method="post">
          <?= csrfField() ?>
          <input type="hidden" name="v" value="<?= $versionId ?>">
          <input type="hidden" name="aktion" value="zurueck">
          <button type="submit" class="btn btn-outline-secondary" <?= $revealed <= 0 ? 'disabled' : '' ?>>« Enthüllung zurücknehmen</button>
        </form>
        <form method="post">
          <?= csrfField() ?>
          <input type="hidden" name="v" value="<?= $versionId ?>">
          <input type="hidden" name="aktion" value="weiter">
          <button type="submit" class="btn btn-success" <?= $revealed >= $gesamt ? 'disabled' : '' ?>>Nächsten Platz enthüllen »</button>
        </form>
        <a href="../beamer.php?v=<?= $versionId ?>" target="_blank" class="btn btn-outline-dark">Beamer-Ansicht öffnen</a>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table align-middle">
        <thead><tr><th>Platz</th><th>Interpret</th><th>Songtitel</th><th>Punkte</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($ergebnisse as $index => $row): ?>
            <?php
            $platz = $index + 1;
            $platzVonHinten = $gesamt - $platz + 1;
            $istEnthuellt = $platzVonHinten <= $revealed;
            ?>
            <tr class="<?= $istEnthuellt ? 'table-success' : '' ?>">
              <td><?= $platz ?></td>
              <td><?= e($row['name']) ?></td>
              <td><?= e($row['songtitel']) ?></td>
              <td><?= (int)$row['summe'] ?></td>
              <td><?= $istEnthuellt ? 'enthüllt' : 'anonymisiert' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>

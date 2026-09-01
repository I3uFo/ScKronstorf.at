<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
requireAdminLogin();

$pdo = db();
$aktivAdminNav = 'dashboard';

$anzahlVersionen = (int)$pdo->query('SELECT COUNT(*) FROM voting_versionen')->fetchColumn();
$offeneVersion = aktuelleVotingVersion($pdo, 'offen');
$anzahlSponsoren = (int)$pdo->query('SELECT COUNT(*) FROM sponsoren')->fetchColumn();

// Für die Live-Auszählung: bevorzugt das aktuell offene Voting, sonst das zuletzt geschlossene.
$auszaehlungsVersion = $offeneVersion
    ?? ($pdo->query("SELECT * FROM voting_versionen WHERE status = 'geschlossen' ORDER BY id DESC LIMIT 1")->fetch() ?: null);

$anzahlStimmen = 0;
$liveErgebnisse = [];
if ($auszaehlungsVersion !== null) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM stimmabgaben WHERE voting_version_id = ?');
    $stmt->execute([$auszaehlungsVersion['id']]);
    $anzahlStimmen = (int)$stmt->fetchColumn();
    $liveErgebnisse = berechneErgebnisse($pdo, (int)$auszaehlungsVersion['id']);
}

require __DIR__ . '/includes/admin_header.php';
?>
<h1 class="h3 mb-4">Dashboard</h1>

<div class="row g-3">
  <div class="col-12 col-md-3">
    <div class="admin-card">
      <div class="text-muted small">Voting-Versionen</div>
      <div class="fs-3 fw-bold"><?= $anzahlVersionen ?></div>
    </div>
  </div>
  <div class="col-12 col-md-3">
    <div class="admin-card">
      <div class="text-muted small">Aktuell offenes Voting</div>
      <div class="fs-5 fw-bold"><?= $offeneVersion !== null ? e($offeneVersion['name']) : 'Keines' ?></div>
    </div>
  </div>
  <div class="col-12 col-md-3">
    <div class="admin-card">
      <div class="text-muted small">Sponsoren</div>
      <div class="fs-3 fw-bold"><?= $anzahlSponsoren ?></div>
    </div>
  </div>
  <div class="col-12 col-md-3">
    <div class="admin-card">
      <div class="text-muted small">
        Abgegebene Stimmen<?= $auszaehlungsVersion !== null ? ' (' . e($auszaehlungsVersion['name']) . ')' : '' ?>
      </div>
      <div class="fs-3 fw-bold"><?= $auszaehlungsVersion !== null ? $anzahlStimmen : '–' ?></div>
    </div>
  </div>
</div>

<div class="row g-3 mt-1">
  <div class="col-12">
    <div class="admin-card">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">
          Live-Punktestand<?= $auszaehlungsVersion !== null ? ' – ' . e($auszaehlungsVersion['name']) : '' ?>
        </h2>
        <span class="text-muted small">unanonymisiert, nur für den Admin-Bereich</span>
      </div>
      <?php if ($auszaehlungsVersion === null || empty($liveErgebnisse)): ?>
        <p class="text-muted mb-0">Keine Daten vorhanden.</p>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead>
              <tr><th>Platz</th><th>Interpret</th><th>Songtitel</th><th class="text-end">Punkte</th></tr>
            </thead>
            <tbody>
              <?php foreach ($liveErgebnisse as $index => $row): ?>
                <tr>
                  <td><?= $index + 1 ?></td>
                  <td><?= e($row['name']) ?></td>
                  <td><?= e($row['songtitel']) ?></td>
                  <td class="text-end fw-bold"><?= (int)$row['summe'] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="mt-4 d-flex flex-wrap gap-2">
  <a href="voting_versionen.php" class="btn btn-outline-secondary">Voting-Versionen verwalten</a>
  <a href="interpreten.php" class="btn btn-outline-secondary">Interpreten importieren</a>
  <a href="sponsoren.php" class="btn btn-outline-secondary">Sponsoren verwalten</a>
  <a href="texte.php" class="btn btn-outline-secondary">Texte bearbeiten</a>
  <a href="beamer_control.php" class="btn btn-outline-secondary">Beamer steuern</a>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>

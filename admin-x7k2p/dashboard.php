<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
requireAdminLogin();

$pdo = db();
$aktivAdminNav = 'dashboard';

$anzahlVersionen = (int)$pdo->query('SELECT COUNT(*) FROM voting_versionen')->fetchColumn();
$offeneVersion = aktuelleVotingVersion($pdo, 'offen');
$anzahlSponsoren = (int)$pdo->query('SELECT COUNT(*) FROM sponsoren')->fetchColumn();

require __DIR__ . '/includes/admin_header.php';
?>
<h1 class="h3 mb-4">Dashboard</h1>

<div class="row g-3">
  <div class="col-12 col-md-4">
    <div class="admin-card">
      <div class="text-muted small">Voting-Versionen</div>
      <div class="fs-3 fw-bold"><?= $anzahlVersionen ?></div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="admin-card">
      <div class="text-muted small">Aktuell offenes Voting</div>
      <div class="fs-5 fw-bold"><?= $offeneVersion !== null ? e($offeneVersion['name']) : 'Keines' ?></div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="admin-card">
      <div class="text-muted small">Sponsoren</div>
      <div class="fs-3 fw-bold"><?= $anzahlSponsoren ?></div>
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

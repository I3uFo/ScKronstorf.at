<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$pdo = db();
$aktivNav = 'voting';
$votingAktiv = aktuelleVotingVersion($pdo, 'offen') !== null;
require __DIR__ . '/includes/header.php';
?>
<div class="text-center py-5">
  <h1 class="display-5 fw-bold mb-3"><?= e(getText($pdo, 'voting', 'titel', 'Jetzt Abstimmen')) ?></h1>
  <p class="lead" style="white-space: pre-line;"><?= e(getText($pdo, 'voting', 'landing_text', "Stimm jetzt für deinen Lieblingsinterpreten ab und entscheide mit,\nwer den Songcontest Kronstorf gewinnt!")) ?></p>
  <div class="mt-10 mb-2">
    Voting-Status:
    <span class="badge <?= $votingAktiv ? 'text-bg-success' : 'text-bg-secondary' ?> fs-6 px-3 py-2">
      <?= $votingAktiv ? 'Aktiv' : 'Geschlossen' ?>
    </span>
  </div>
  <?php if ($votingAktiv): ?>
    <a href="abstimmen.php" class="btn btn-primary btn-lg mt-2">Jetzt abstimmen</a>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>

<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$pdo = db();
$aktivNav = 'start';
require __DIR__ . '/includes/header.php';
?>
<div class="text-center py-5">
  <h1 class="display-5 fw-bold"><?= e(getText($pdo, 'start', 'titel', 'Willkommen beim SC Zauner Group Kronstorf Songcontest')) ?></h1>
  <p class="lead" style="white-space: pre-line;"><?= e(getText($pdo, 'start', 'text', 'Stimm jetzt für deinen Favoriten ab!')) ?></p>

  <div class="save-date-card">
    <div class="save-date-kicker"><?= e(getText($pdo, 'start', 'save_the_date_titel', 'Save the Date')) ?></div>
    <div class="save-date-text"><?= e(getText($pdo, 'start', 'save_the_date_termin', "Samstag, 21. November 2026\nJoseph Heimel Halle Kronstorf")) ?></div>
  </div>

  <a href="voting.php" class="btn btn-primary btn-lg mt-4">Jetzt abstimmen</a>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>

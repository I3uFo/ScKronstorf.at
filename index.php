<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$pdo = db();
$aktivNav = 'start';
require __DIR__ . '/includes/header.php';
?>
<div class="text-center py-5">
  <h1 class="display-5 fw-bold welcome-headline">
    <span class="welcome-headline-line">Willkommen beim</span>
    <span class="welcome-headline-line welcome-headline-line--logo">
      SC
      <img src="assets/img/zauner-group-logo.png" alt="Zauner Group" class="welcome-headline-logo">
      Kronstorf
    </span>
    <span class="welcome-headline-line">Songcontest</span>
  </h1>

  <div class="save-date-card">
    <div class="save-date-kicker"><?= e(getText($pdo, 'start', 'save_the_date_titel', 'Save the Date')) ?></div>
    <div class="save-date-text"><?= e(getText($pdo, 'start', 'save_the_date_termin', "Samstag, 21. November 2026\nJoseph Heimel Halle Kronstorf")) ?></div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>

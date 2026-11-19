<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$pdo = db();
$aktivNav = 'start';
$heroBg = true;
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

  <div class="sd">
    <div class="sd-wordmark">
      <span class="sd-save">Save</span>
      <span class="sd-the">the</span>
      <span class="sd-dateword">Date</span>
    </div>
    <div class="sd-band">
      <span><?= e(seitenText($pdo, 'start', 'save_the_date_edition', '32. Songcontest')) ?></span>
    </div>
    <div class="sd-timebox">
      <div class="sd-datum"><?= e(seitenText($pdo, 'start', 'save_the_date_datum', '21.11.2026')) ?></div>
      <div class="sd-uhrzeit"><?= e(seitenText($pdo, 'start', 'save_the_date_uhrzeit', '20:00 Uhr')) ?></div>
    </div>
    <div class="sd-ort">
      <svg viewBox="0 0 24 24" fill="currentColor" class="sd-ort-icon"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"></path></svg>
      <span><?= e(seitenText($pdo, 'start', 'save_the_date_ort', 'Josef-Heiml-Halle')) ?></span>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>

<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$pdo = db();
$aktivNav = 'sponsoren';
$heroBg = true;
$heroBgVariant = 'mic';
$sponsoren = $pdo->query('SELECT * FROM sponsoren ORDER BY reihenfolge ASC')->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<div class="danke-screen">
  <div>
    <h1 class="display-6 fw-bold voting-headline"><?= e(seitenText($pdo, 'sponsoren', 'titel', 'Danke für eure Unterstützung')) ?></h1>
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
<?php require __DIR__ . '/includes/footer.php'; ?>

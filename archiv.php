<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';

$pdo = db();
$robots = 'noindex,nofollow';
$videos = $pdo->query('SELECT * FROM archiv_videos ORDER BY jahr DESC, reihenfolge ASC')->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<h1 class="mb-2"><?= e(getText($pdo, 'archiv', 'titel', 'Songcontest Archiv')) ?></h1>
<p class="text-muted mb-4"><?= e(getText($pdo, 'archiv', 'text', 'Hier findest du Videos vergangener Songcontest-Abende.')) ?></p>

<?php if (empty($videos)): ?>
  <p>Es sind noch keine Videos hinterlegt.</p>
<?php else: ?>
  <div class="row g-4">
    <?php foreach ($videos as $video): ?>
      <div class="col-12 col-md-6 col-lg-4">
        <div class="ratio ratio-16x9 mb-2">
          <iframe
            src="https://www.youtube-nocookie.com/embed/<?= e($video['youtube_id']) ?>"
            title="<?= e($video['titel']) ?>"
            allowfullscreen
            loading="lazy"
          ></iframe>
        </div>
        <div class="fw-semibold"><?= e($video['titel']) ?></div>
        <div class="text-muted small"><?= (int)$video['jahr'] ?></div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>

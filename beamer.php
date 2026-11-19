<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
requireAdminLogin('admin-x7k2p/index.php');

$pdo = db();
$robots = 'noindex,nofollow';

$versionId = (int)($_GET['v'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM voting_versionen WHERE id = ?');
$stmt->execute([$versionId]);
$version = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="<?= e($robots) ?>">
<title>Beamer – <?= e(SITE_NAME) ?></title>
<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="beamer-body">
<div class="container py-4" data-version-id="<?= $versionId ?>">
  <?php if ($version === false): ?>
    <p class="text-center">Keine Voting-Version ausgewählt.</p>
  <?php else: ?>
    <h1 class="text-center mb-4"><?= e($version['name']) ?></h1>
    <div class="beamer-balken-liste" id="beamer-liste"></div>
  <?php endif; ?>
</div>
<script src="assets/js/beamer.js"></script>
</body>
</html>

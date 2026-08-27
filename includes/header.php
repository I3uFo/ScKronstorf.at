<?php
/** @var PDO $pdo */
$pdo = db();
$aktivNav = $aktivNav ?? '';
$robots = $robots ?? 'index,follow';
?><!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="<?= e($robots) ?>">
<title><?= e(SITE_NAME) ?></title>
<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
  <div class="container d-flex align-items-center gap-3 py-2">
    <a href="index.php" class="d-flex align-items-center gap-2 text-decoration-none site-brand">
      <img src="assets/img/logo.png" alt="Logo SC Kronstorf" class="site-logo">
      <span class="site-name">SC Kronstorf<br>Songcontest</span>
    </a>
    <nav class="ms-auto d-flex gap-1">
      <a class="nav-link-custom<?= $aktivNav === 'start' ? ' active' : '' ?>" href="index.php">Start</a>
      <a class="nav-link-custom<?= $aktivNav === 'voting' ? ' active' : '' ?>" href="voting.php">Voting</a>
    </nav>
  </div>
</header>
<main class="container py-4">

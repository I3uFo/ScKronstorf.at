<?php
/** @var PDO $pdo */
$pdo = db();
$aktivNav = $aktivNav ?? '';
$robots = $robots ?? 'index,follow';
$heroBg = $heroBg ?? false;
$heroBgVariant = $heroBgVariant ?? null;
?><!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="<?= e($robots) ?>">
<title><?= e(SITE_NAME) ?></title>
<script src="assets/js/theme.js"></script>
<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="site-body">
<header class="site-header">
  <div class="container d-flex flex-wrap align-items-center gap-3 py-2">
    <a href="index.php" class="d-flex align-items-center gap-2 text-decoration-none site-brand">
      <img src="assets/img/logo.png" alt="Logo SC Zauner Group Kronstorf" class="site-logo">
      <span class="site-name">SC Zauner Group<br>Kronstorf</span>
    </a>
    <button type="button" class="nav-toggle ms-auto" data-bs-toggle="collapse" data-bs-target="#site-nav" aria-controls="site-nav" aria-expanded="false" aria-label="Menü öffnen/schließen">
      <span class="nav-toggle-bar"></span>
      <span class="nav-toggle-bar"></span>
      <span class="nav-toggle-bar"></span>
    </button>
    <nav class="site-nav collapse ms-md-auto" id="site-nav">
      <a class="nav-link-custom<?= $aktivNav === 'start' ? ' active' : '' ?>" href="index.php">Songcontest</a>
      <a class="nav-link-custom<?= $aktivNav === 'voting' ? ' active' : '' ?>" href="voting.php">Voting</a>
      <a class="nav-link-custom<?= $aktivNav === 'sponsoren' ? ' active' : '' ?>" href="sponsoren.php">Sponsoren</a>
    </nav>
  </div>
</header>
<main class="container py-4<?= $heroBg ? ' hero-bg' . ($heroBgVariant ? ' hero-bg--' . e($heroBgVariant) : '') : '' ?>">

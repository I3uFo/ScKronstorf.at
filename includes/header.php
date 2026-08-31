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
<script src="assets/js/theme.js"></script>
<link rel="stylesheet" href="assets/css/bootstrap.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="site-header">
  <div class="container d-flex align-items-center gap-3 py-2">
    <a href="index.php" class="d-flex align-items-center gap-2 text-decoration-none site-brand">
      <img src="assets/img/logo.png" alt="Logo SC Zauner Group Kronstorf" class="site-logo">
      <span class="site-name">SC Zauner Group<br>Kronstorf Songcontest</span>
    </a>
    <nav class="ms-auto d-flex align-items-center gap-1">
      <a class="nav-link-custom<?= $aktivNav === 'start' ? ' active' : '' ?>" href="index.php">Start</a>
      <a class="nav-link-custom<?= $aktivNav === 'voting' ? ' active' : '' ?>" href="voting.php">Voting</a>
      <button type="button" class="theme-toggle-btn" data-theme-toggle aria-label="Hell-/Dunkelmodus umschalten">
        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"></path></svg>
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
      </button>
    </nav>
  </div>
</header>
<main class="container py-4">

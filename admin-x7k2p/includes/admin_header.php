<?php
/** @var PDO $pdo */
$pdo = db();
$aktivAdminNav = $aktivAdminNav ?? '';

function adminNavKlasse(string $eigene, string $aktiv): string
{
    return $eigene === $aktiv ? 'active' : '';
}
?><!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>Admin – <?= e(SITE_NAME) ?></title>
<script src="../assets/js/theme.js"></script>
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-shell">
<div class="d-flex flex-column flex-md-row">
  <nav class="admin-nav p-3 d-flex flex-row flex-md-column flex-wrap gap-1">
    <div class="text-white fw-bold mb-0 mb-md-3 w-100 d-flex align-items-center justify-content-between gap-2">
      <span>SC Zauner Group Kronstorf Admin</span>
      <button type="button" class="theme-toggle-btn" data-theme-toggle aria-label="Hell-/Dunkelmodus umschalten">
        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"></path></svg>
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
      </button>
    </div>
    <a href="dashboard.php" class="<?= adminNavKlasse('dashboard', $aktivAdminNav) ?>">Dashboard</a>
    <a href="voting_versionen.php" class="<?= adminNavKlasse('voting', $aktivAdminNav) ?>">Voting-Versionen</a>
    <a href="sponsoren.php" class="<?= adminNavKlasse('sponsoren', $aktivAdminNav) ?>">Sponsoren</a>
    <a href="texte.php" class="<?= adminNavKlasse('texte', $aktivAdminNav) ?>">Texte</a>
    <a href="beamer_control.php" class="<?= adminNavKlasse('beamer', $aktivAdminNav) ?>">Beamer-Steuerung</a>
    <a href="logout.php" class="admin-nav-logout">Abmelden</a>
  </nav>
  <div class="flex-fill admin-content p-3 p-md-4">

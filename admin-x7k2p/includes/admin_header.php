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
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-shell">
<div class="d-flex flex-column flex-md-row">
  <nav class="admin-nav p-3 d-flex flex-row flex-md-column flex-wrap gap-1">
    <div class="text-white fw-bold mb-0 mb-md-3 w-100">SC Kronstorf Admin</div>
    <a href="dashboard.php" class="<?= adminNavKlasse('dashboard', $aktivAdminNav) ?>">Dashboard</a>
    <a href="voting_versionen.php" class="<?= adminNavKlasse('voting', $aktivAdminNav) ?>">Voting-Versionen</a>
    <a href="sponsoren.php" class="<?= adminNavKlasse('sponsoren', $aktivAdminNav) ?>">Sponsoren</a>
    <a href="texte.php" class="<?= adminNavKlasse('texte', $aktivAdminNav) ?>">Texte</a>
    <a href="beamer_control.php" class="<?= adminNavKlasse('beamer', $aktivAdminNav) ?>">Beamer-Steuerung</a>
    <a href="logout.php">Abmelden</a>
  </nav>
  <div class="flex-fill p-3 p-md-4">

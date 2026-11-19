<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

$pdo = db();
$fehler = null;

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck()) {
        $fehler = 'Sicherheitsprüfung fehlgeschlagen. Bitte erneut versuchen.';
    } else {
        $_SESSION['login_versuche'] = $_SESSION['login_versuche'] ?? [];
        $jetzt = time();
        $_SESSION['login_versuche'] = array_values(array_filter(
            $_SESSION['login_versuche'],
            static fn ($zeitpunkt) => $zeitpunkt > $jetzt - 300
        ));

        if (count($_SESSION['login_versuche']) >= 5) {
            $fehler = 'Zu viele Fehlversuche. Bitte 5 Minuten warten.';
        } else {
            $username = trim((string)($_POST['username'] ?? ''));
            $passwort = (string)($_POST['passwort'] ?? '');

            $stmt = $pdo->prepare('SELECT * FROM admin_users WHERE username = ?');
            $stmt->execute([$username]);
            $benutzer = $stmt->fetch();

            if ($benutzer !== false && password_verify($passwort, $benutzer['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = $benutzer['id'];
                $_SESSION['admin_last_activity'] = time();
                unset($_SESSION['login_versuche']);
                header('Location: dashboard.php');
                exit;
            }

            $_SESSION['login_versuche'][] = $jetzt;
            $fehler = 'Benutzername oder Passwort ist falsch.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>Admin-Login – <?= e(SITE_NAME) ?></title>
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-shell d-flex align-items-center justify-content-center" style="min-height:100vh;">
<div class="admin-card" style="max-width: 380px; width: 100%;">
  <h1 class="h4 mb-3 text-center">Admin-Login</h1>
  <?php if (!empty($_GET['timeout'])): ?>
    <div class="alert alert-warning">Deine Sitzung ist abgelaufen. Bitte erneut anmelden.</div>
  <?php endif; ?>
  <?php if ($fehler !== null): ?>
    <div class="alert alert-danger"><?= e($fehler) ?></div>
  <?php endif; ?>
  <form method="post">
    <?= csrfField() ?>
    <div class="mb-3">
      <label class="form-label" for="username">Benutzername</label>
      <input class="form-control" type="text" id="username" name="username" autocomplete="username" required>
    </div>
    <div class="mb-3">
      <label class="form-label" for="passwort">Passwort</label>
      <input class="form-control" type="password" id="passwort" name="passwort" autocomplete="current-password" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Anmelden</button>
  </form>
</div>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>

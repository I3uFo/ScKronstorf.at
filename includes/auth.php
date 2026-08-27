<?php
declare(strict_types=1);

function requireAdminLogin(string $loginPfad = 'index.php'): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . $loginPfad);
        exit;
    }
    if (!empty($_SESSION['admin_last_activity']) && (time() - (int)$_SESSION['admin_last_activity']) > ADMIN_SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        header('Location: ' . $loginPfad . '?timeout=1');
        exit;
    }
    $_SESSION['admin_last_activity'] = time();
}

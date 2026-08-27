<?php
declare(strict_types=1);

function getText(PDO $pdo, string $seite, string $schluessel, string $standard = ''): string
{
    static $cache = [];
    $key = $seite . '::' . $schluessel;
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }
    $stmt = $pdo->prepare('SELECT inhalt FROM seiten_texte WHERE seite = ? AND schluessel = ?');
    $stmt->execute([$seite, $schluessel]);
    $wert = $stmt->fetchColumn();
    $cache[$key] = ($wert !== false) ? (string)$wert : $standard;
    return $cache[$key];
}

function alleTexteFuerSeite(PDO $pdo, string $seite): array
{
    $stmt = $pdo->prepare('SELECT schluessel, inhalt FROM seiten_texte WHERE seite = ? ORDER BY schluessel ASC');
    $stmt->execute([$seite]);
    return $stmt->fetchAll();
}

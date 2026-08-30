<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function csrfCheck(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return is_string($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Eurovision-Style Punkteschema: Platz 1 = 12 Punkte, Platz 2 = 10, Platz 3 = 9,
 * danach absteigend bis Platz 11 = 1 Punkt. Ab Platz 12 gibt es 0 Punkte.
 */
function punkteFuerPlatz(int $platz): int
{
    static $tabelle = [1 => 12, 2 => 10, 3 => 9, 4 => 8, 5 => 7, 6 => 6, 7 => 5, 8 => 4, 9 => 3, 10 => 2, 11 => 1];
    return $tabelle[$platz] ?? 0;
}

function maxPlatzierung(): int
{
    return 11;
}

function getOrCreateDeviceToken(): string
{
    $vorhanden = $_COOKIE[DEVICE_TOKEN_COOKIE] ?? '';
    if (is_string($vorhanden) && preg_match('/^[a-f0-9]{48}$/', $vorhanden) === 1) {
        return $vorhanden;
    }
    $token = bin2hex(random_bytes(24));
    setcookie(DEVICE_TOKEN_COOKIE, $token, [
        'expires' => time() + DEVICE_TOKEN_LIFETIME,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $_COOKIE[DEVICE_TOKEN_COOKIE] = $token;
    return $token;
}

function deviceTokenId(PDO $pdo, string $token): int
{
    $stmt = $pdo->prepare('SELECT id FROM device_tokens WHERE token = ?');
    $stmt->execute([$token]);
    $id = $stmt->fetchColumn();
    if ($id !== false) {
        return (int)$id;
    }
    $insert = $pdo->prepare('INSERT INTO device_tokens (token) VALUES (?)');
    $insert->execute([$token]);
    return (int)$pdo->lastInsertId();
}

function aktuelleVotingVersion(PDO $pdo, string $status): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM voting_versionen WHERE status = ? ORDER BY id DESC LIMIT 1');
    $stmt->execute([$status]);
    $row = $stmt->fetch();
    return $row !== false ? $row : null;
}

/**
 * Liefert die Interpreten einer Voting-Version, absteigend nach Gesamtpunkten sortiert
 * (Platz 1 = Sieger = Index 0). Bei Punktegleichstand entscheidet die Auftrittsreihenfolge.
 */
function berechneErgebnisse(PDO $pdo, int $versionId): array
{
    $stmt = $pdo->prepare(
        'SELECT i.id, i.name, i.songtitel, i.originalinterpret, i.reihenfolge,
                COALESCE(SUM(v.punkte), 0) AS summe
         FROM interpreten i
         LEFT JOIN votes v ON v.interpret_id = i.id AND v.voting_version_id = i.voting_version_id
         WHERE i.voting_version_id = ?
         GROUP BY i.id
         ORDER BY summe DESC, i.reihenfolge ASC'
    );
    $stmt->execute([$versionId]);
    return $stmt->fetchAll();
}

/**
 * Liefert die gültigen Werte für beamer_freigabe_platz, in aufsteigender Reihenfolge
 * (0 = nichts enthüllt, letzter Wert = alle enthüllt). Zwischen zwei aufeinanderfolgenden
 * Werten liegt jeweils eine "Enthüllungsgruppe": Interpreten mit gleicher Punktezahl werden
 * immer gemeinsam enthüllt, und Platz 1 wird immer gemeinsam mit Platz 2 enthüllt.
 *
 * @param array $ergebnisse Ergebnis von berechneErgebnisse() (absteigend nach Punkten sortiert)
 * @return int[]
 */
function berechneEnthuellungsSchritte(array $ergebnisse): array
{
    $gesamt = count($ergebnisse);
    if ($gesamt === 0) {
        return [0];
    }

    // Gruppen (Listen von Indizes) von hinten (schwächster Platz) nach vorne (Platz 1) bilden;
    // gleiche Punktzahl landet immer in derselben Gruppe.
    $gruppen = [[$gesamt - 1]];
    for ($i = $gesamt - 2; $i >= 0; $i--) {
        if ((int)$ergebnisse[$i]['summe'] === (int)$ergebnisse[$i + 1]['summe']) {
            $gruppen[count($gruppen) - 1][] = $i;
        } else {
            $gruppen[] = [$i];
        }
    }

    // Platz 1 (Index 0) und Platz 2 (Index 1) werden immer gemeinsam enthüllt.
    $letzterIndex = count($gruppen) - 1;
    if ($gesamt >= 2 && $letzterIndex >= 1 && !in_array(1, $gruppen[$letzterIndex], true)) {
        $gruppen[$letzterIndex - 1] = array_merge($gruppen[$letzterIndex - 1], $gruppen[$letzterIndex]);
        unset($gruppen[$letzterIndex]);
        $gruppen = array_values($gruppen);
    }

    $schritte = [0];
    $anzahl = 0;
    foreach ($gruppen as $gruppe) {
        $anzahl += count($gruppe);
        $schritte[] = $anzahl;
    }
    return $schritte;
}

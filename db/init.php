<?php
declare(strict_types=1);

/**
 * Richtet die SQLite-Datenbank ein: legt die Datei an (falls nicht vorhanden),
 * spielt das Schema ein und seedet Admin-Zugang sowie Standardtexte.
 * Kann gefahrlos mehrfach ausgeführt werden (idempotent).
 *
 * Aufruf per CLI:     php db/init.php
 * Aufruf per Browser: nur einmalig zulassen, danach diese Datei sperren/löschen!
 */

$istCli = PHP_SAPI === 'cli';

if (!$istCli) {
    // Schutz gegen versehentliches erneutes Ausführen über den Browser in Produktion.
    $markerDatei = __DIR__ . '/.initialisiert';
    if (is_file($markerDatei)) {
        http_response_code(403);
        echo 'Die Datenbank wurde bereits initialisiert.';
        exit;
    }
}

$dbPfad = __DIR__ . '/kronstorf.sqlite';
$neuAngelegt = !is_file($dbPfad);

$pdo = new PDO('sqlite:' . $dbPfad);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$schema = file_get_contents(__DIR__ . '/schema.sql');
$pdo->exec($schema);

// Admin-Zugang: Benutzername "admin", Erst-Passwort "Kronstorf#2016".
// Das Passwort wird ausschließlich als bcrypt-Hash gespeichert, niemals im Klartext.
$stmt = $pdo->prepare('SELECT COUNT(*) FROM admin_users');
$stmt->execute();
if ((int)$stmt->fetchColumn() === 0) {
    $hash = password_hash('Kronstorf#2016', PASSWORD_DEFAULT);
    $insert = $pdo->prepare('INSERT INTO admin_users (username, password_hash) VALUES (?, ?)');
    $insert->execute(['admin', $hash]);
}

// Standardtexte für alle Seiten seeden (nur falls noch nicht vorhanden).
$standardTexte = [
    ['start', 'titel', 'Willkommen beim SC Zauner Group Kronstorf Songcontest'],
    ['start', 'text', "Stimm jetzt für deinen Lieblingsinterpreten ab und entscheide mit,\nwer den Songcontest Kronstorf gewinnt!"],
    ['start', 'save_the_date_titel', 'Save the Date'],
    ['start', 'save_the_date_termin', "Samstag, 21. November 2026\nJoseph Heimel Halle Kronstorf"],
    ['voting', 'titel', 'Jetzt abstimmen'],
    ['voting', 'anleitung', 'Vergib die Plätze 1 bis 11 an deine Favoriten. Platz 1 erhält 12 Punkte, Platz 2 erhält 10 Punkte, danach absteigend bis Platz 11 mit 1 Punkt. Jeder Interpret kann nur einen Platz erhalten.'],
    ['voting', 'kein_voting_aktiv', 'Aktuell ist kein Voting geöffnet. Schau später wieder vorbei!'],
    ['voting', 'bereits_abgestimmt', 'Von diesem Gerät wurde bereits abgestimmt.'],
    ['danke', 'titel', 'Danke für deine Stimme!'],
    ['danke', 'text', 'Deine Stimme wurde gezählt. Wir bedanken uns herzlich bei unseren Sponsoren für die Unterstützung des SC Zauner Group Kronstorf Songcontest.'],
    ['archiv', 'titel', 'Songcontest Archiv'],
    ['archiv', 'text', 'Hier findest du Videos vergangener Songcontest-Abende.'],
    ['impressum', 'vereinszweck', 'Förderung des Sports, insbesondere des Fußballsports, im Rahmen der Vereinstätigkeit.'],
    ['datenschutz', 'einleitung', 'Der SC Zauner Group Kronstorf nimmt den Schutz personenbezogener Daten ernst und behandelt diese vertraulich entsprechend den gesetzlichen Datenschutzvorschriften.'],
];

$insertText = $pdo->prepare(
    'INSERT INTO seiten_texte (seite, schluessel, inhalt) VALUES (?, ?, ?)
     ON CONFLICT(seite, schluessel) DO NOTHING'
);
foreach ($standardTexte as [$seite, $schluessel, $inhalt]) {
    $insertText->execute([$seite, $schluessel, $inhalt]);
}

if (!$istCli) {
    file_put_contents(__DIR__ . '/.initialisiert', date('c'));
}

$meldung = $neuAngelegt
    ? 'Datenbank wurde neu angelegt und initialisiert.'
    : 'Datenbank war bereits vorhanden, fehlende Tabellen/Standardwerte wurden ergänzt.';

if ($istCli) {
    fwrite(STDOUT, $meldung . PHP_EOL);
} else {
    header('Content-Type: text/plain; charset=utf-8');
    echo $meldung;
}

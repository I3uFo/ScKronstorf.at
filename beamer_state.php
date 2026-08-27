<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
requireAdminLogin('admin-x7k2p/index.php');

$pdo = db();
header('Content-Type: application/json; charset=utf-8');

$versionId = (int)($_GET['v'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM voting_versionen WHERE id = ?');
$stmt->execute([$versionId]);
$version = $stmt->fetch();

if ($version === false) {
    http_response_code(404);
    echo json_encode(['fehler' => 'Voting-Version nicht gefunden.']);
    exit;
}

$ergebnisse = berechneErgebnisse($pdo, $versionId);
$gesamt = count($ergebnisse);
$revealed = (int)$version['beamer_freigabe_platz'];
$maxSumme = $gesamt > 0 ? (int)$ergebnisse[0]['summe'] : 0;

$ausgabe = [];
foreach ($ergebnisse as $index => $row) {
    $platz = $index + 1;
    $platzVonHinten = $gesamt - $platz + 1;
    $istEnthuellt = $platzVonHinten <= $revealed;
    $ausgabe[] = [
        'platz' => $platz,
        'anonym_label' => 'Interpret ' . chr(64 + $platz),
        'name' => $istEnthuellt ? $row['name'] : null,
        'songtitel' => $istEnthuellt ? $row['songtitel'] : null,
        'summe' => (int)$row['summe'],
        'enthuellt' => $istEnthuellt,
    ];
}

echo json_encode([
    'name' => $version['name'],
    'gesamt' => $gesamt,
    'revealed' => $revealed,
    'max_summe' => $maxSumme,
    'ergebnisse' => $ausgabe,
], JSON_UNESCAPED_UNICODE);

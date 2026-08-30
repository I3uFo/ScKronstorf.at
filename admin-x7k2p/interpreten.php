<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
requireAdminLogin();

$pdo = db();
$aktivAdminNav = 'voting';
$fehler = null;
$erfolg = null;

$versionId = (int)($_GET['v'] ?? $_POST['v'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM voting_versionen WHERE id = ?');
$stmt->execute([$versionId]);
$version = $stmt->fetch();

if ($version === false) {
    require __DIR__ . '/includes/admin_header.php';
    echo '<div class="alert alert-warning">Bitte zuerst eine Voting-Version auswählen.</div>';
    echo '<a href="voting_versionen.php" class="btn btn-outline-secondary">Zu den Voting-Versionen</a>';
    require __DIR__ . '/includes/admin_footer.php';
    exit;
}

$bearbeitbar = $version['status'] === 'vorbereitung';
$sessionKey = 'import_vorschau_' . $versionId;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $bearbeitbar) {
    if (!csrfCheck()) {
        $fehler = 'Sicherheitsprüfung fehlgeschlagen.';
    } else {
        $aktion = (string)($_POST['aktion'] ?? '');

        if ($aktion === 'hochladen' && isset($_FILES['csv_datei'])) {
            if ($_FILES['csv_datei']['error'] !== UPLOAD_ERR_OK) {
                $fehler = 'Datei-Upload fehlgeschlagen.';
            } else {
                $inhalt = (string)file_get_contents($_FILES['csv_datei']['tmp_name']);
                $inhalt = preg_replace('/^\xEF\xBB\xBF/', '', $inhalt) ?? $inhalt;
                $zeilen = preg_split('/\r\n|\r|\n/', trim($inhalt)) ?: [];

                $stmt = $pdo->prepare('SELECT reihenfolge FROM interpreten WHERE voting_version_id = ?');
                $stmt->execute([$versionId]);
                $belegteReihenfolgen = array_flip(array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN)));

                $fehlerListe = [];
                $vorschau = [];
                $gesehen = [];

                foreach ($zeilen as $index => $zeile) {
                    if (trim($zeile) === '') {
                        continue;
                    }
                    if ($index === 0) {
                        continue; // Kopfzeile überspringen
                    }
                    $zeilenNr = $index + 1;
                    $felder = str_getcsv($zeile, ';', '"', '\\');
                    if (count($felder) < 3) {
                        $fehlerListe[] = "Zeile $zeilenNr: zu wenige Spalten (Format: Reihenfolge;Interpret;Songtitel;Originalinterpret).";
                        continue;
                    }
                    $reihenfolge = trim($felder[0] ?? '');
                    $name = trim($felder[1] ?? '');
                    $songtitel = trim($felder[2] ?? '');
                    $original = trim($felder[3] ?? '');

                    if ($reihenfolge === '' || $name === '' || $songtitel === '') {
                        $fehlerListe[] = "Zeile $zeilenNr: Reihenfolge, Interpret und Songtitel sind Pflichtfelder.";
                        continue;
                    }
                    if (!ctype_digit($reihenfolge)) {
                        $fehlerListe[] = "Zeile $zeilenNr: Reihenfolge muss eine ganze Zahl sein.";
                        continue;
                    }
                    if (isset($gesehen[$reihenfolge])) {
                        $fehlerListe[] = "Zeile $zeilenNr: Reihenfolge $reihenfolge kommt in der Datei mehrfach vor.";
                        continue;
                    }
                    if (isset($belegteReihenfolgen[$reihenfolge])) {
                        $fehlerListe[] = "Zeile $zeilenNr: Reihenfolge $reihenfolge ist für diese Voting-Version bereits vergeben.";
                        continue;
                    }
                    $gesehen[$reihenfolge] = true;
                    $vorschau[] = [
                        'reihenfolge' => (int)$reihenfolge,
                        'name' => $name,
                        'songtitel' => $songtitel,
                        'originalinterpret' => $original,
                    ];
                }

                if (empty($vorschau) && empty($fehlerListe)) {
                    $fehler = 'Die Datei enthält keine gültigen Datenzeilen.';
                } else {
                    $_SESSION[$sessionKey] = $vorschau;
                    if (!empty($fehlerListe)) {
                        $fehler = 'Einige Zeilen konnten nicht übernommen werden:<br>' . implode('<br>', array_map('e', $fehlerListe));
                    }
                }
            }
        } elseif ($aktion === 'bestaetigen') {
            $vorschau = $_SESSION[$sessionKey] ?? [];
            if (empty($vorschau)) {
                $fehler = 'Es liegt keine Vorschau zum Bestätigen vor. Bitte Datei erneut hochladen.';
            } else {
                $insert = $pdo->prepare(
                    'INSERT INTO interpreten (voting_version_id, reihenfolge, name, songtitel, originalinterpret)
                     VALUES (?, ?, ?, ?, ?)'
                );
                foreach ($vorschau as $zeile) {
                    $insert->execute([$versionId, $zeile['reihenfolge'], $zeile['name'], $zeile['songtitel'], $zeile['originalinterpret']]);
                }
                unset($_SESSION[$sessionKey]);
                $erfolg = count($vorschau) . ' Interpreten wurden importiert.';
            }
        } elseif ($aktion === 'hinzufuegen') {
            $reihenfolge = trim((string)($_POST['reihenfolge'] ?? ''));
            $name = trim((string)($_POST['name'] ?? ''));
            $songtitel = trim((string)($_POST['songtitel'] ?? ''));
            $original = trim((string)($_POST['originalinterpret'] ?? ''));

            if ($reihenfolge === '' || $name === '' || $songtitel === '') {
                $fehler = 'Reihenfolge, Interpret und Songtitel sind Pflichtfelder.';
            } elseif (!ctype_digit($reihenfolge)) {
                $fehler = 'Reihenfolge muss eine ganze Zahl sein.';
            } else {
                $stmt = $pdo->prepare('SELECT 1 FROM interpreten WHERE voting_version_id = ? AND reihenfolge = ?');
                $stmt->execute([$versionId, $reihenfolge]);
                if ($stmt->fetchColumn() !== false) {
                    $fehler = 'Reihenfolge ' . e($reihenfolge) . ' ist für diese Voting-Version bereits vergeben.';
                } else {
                    $insert = $pdo->prepare(
                        'INSERT INTO interpreten (voting_version_id, reihenfolge, name, songtitel, originalinterpret)
                         VALUES (?, ?, ?, ?, ?)'
                    );
                    $insert->execute([$versionId, (int)$reihenfolge, $name, $songtitel, $original]);
                    $erfolg = 'Interpret "' . $name . '" wurde hinzugefügt.';
                }
            }
        } elseif ($aktion === 'aktualisieren') {
            $id = (int)($_POST['id'] ?? 0);
            $reihenfolge = trim((string)($_POST['reihenfolge'] ?? ''));
            $name = trim((string)($_POST['name'] ?? ''));
            $songtitel = trim((string)($_POST['songtitel'] ?? ''));
            $original = trim((string)($_POST['originalinterpret'] ?? ''));

            if ($reihenfolge === '' || $name === '' || $songtitel === '') {
                $fehler = 'Reihenfolge, Interpret und Songtitel sind Pflichtfelder.';
            } elseif (!ctype_digit($reihenfolge)) {
                $fehler = 'Reihenfolge muss eine ganze Zahl sein.';
            } else {
                $stmt = $pdo->prepare('SELECT 1 FROM interpreten WHERE voting_version_id = ? AND reihenfolge = ? AND id != ?');
                $stmt->execute([$versionId, $reihenfolge, $id]);
                if ($stmt->fetchColumn() !== false) {
                    $fehler = 'Reihenfolge ' . e($reihenfolge) . ' ist für diese Voting-Version bereits vergeben.';
                } else {
                    $update = $pdo->prepare(
                        'UPDATE interpreten SET reihenfolge = ?, name = ?, songtitel = ?, originalinterpret = ?
                         WHERE id = ? AND voting_version_id = ?'
                    );
                    $update->execute([(int)$reihenfolge, $name, $songtitel, $original, $id, $versionId]);
                    $erfolg = 'Interpret "' . $name . '" wurde aktualisiert.';
                }
            }
        } elseif ($aktion === 'verwerfen') {
            unset($_SESSION[$sessionKey]);
        } elseif ($aktion === 'loeschen') {
            $id = (int)($_POST['id'] ?? 0);
            $delete = $pdo->prepare('DELETE FROM interpreten WHERE id = ? AND voting_version_id = ?');
            $delete->execute([$id, $versionId]);
            $erfolg = 'Interpret wurde gelöscht.';
        }
    }
}

$vorschau = $_SESSION[$sessionKey] ?? [];

$stmt = $pdo->prepare('SELECT * FROM interpreten WHERE voting_version_id = ? ORDER BY reihenfolge ASC');
$stmt->execute([$versionId]);
$interpreten = $stmt->fetchAll();

require __DIR__ . '/includes/admin_header.php';
?>
<h1 class="h3 mb-1">Interpreten – <?= e($version['name']) ?></h1>
<p class="text-muted mb-4">Status: <span class="badge text-bg-secondary"><?= e($version['status']) ?></span></p>

<?php if ($fehler !== null): ?><div class="alert alert-danger"><?= $fehler ?></div><?php endif; ?>
<?php if ($erfolg !== null): ?><div class="alert alert-success"><?= e($erfolg) ?></div><?php endif; ?>

<?php if (!$bearbeitbar): ?>
  <div class="alert alert-info">
    Diese Voting-Version ist bereits <?= e($version['status']) ?> und kann nicht mehr bearbeitet werden.
  </div>
<?php else: ?>

  <?php if (!empty($vorschau)): ?>
    <div class="admin-card mb-4">
      <h2 class="h5 mb-3">Vorschau (<?= count($vorschau) ?> Zeilen)</h2>
      <div class="table-responsive mb-3">
        <table class="table">
          <thead><tr><th>#</th><th>Interpret</th><th>Songtitel</th><th>Original</th></tr></thead>
          <tbody>
            <?php foreach ($vorschau as $zeile): ?>
              <tr>
                <td><?= (int)$zeile['reihenfolge'] ?></td>
                <td><?= e($zeile['name']) ?></td>
                <td><?= e($zeile['songtitel']) ?></td>
                <td><?= e($zeile['originalinterpret']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="d-flex gap-2">
        <form method="post">
          <?= csrfField() ?>
          <input type="hidden" name="v" value="<?= $versionId ?>">
          <input type="hidden" name="aktion" value="bestaetigen">
          <button type="submit" class="btn btn-success">Import bestätigen</button>
        </form>
        <form method="post">
          <?= csrfField() ?>
          <input type="hidden" name="v" value="<?= $versionId ?>">
          <input type="hidden" name="aktion" value="verwerfen">
          <button type="submit" class="btn btn-outline-secondary">Verwerfen</button>
        </form>
      </div>
    </div>
  <?php else: ?>
    <div class="admin-card mb-4">
      <h2 class="h5 mb-3">Interpreten importieren (CSV/TXT)</h2>
      <p class="text-muted small">
        Format je Zeile: <code>Reihenfolge;Interpret;Songtitel;Originalinterpret</code>, Trennzeichen Semikolon,
        UTF-8, erste Zeile ist die Kopfzeile.
      </p>
      <form method="post" enctype="multipart/form-data" class="d-flex flex-column flex-md-row gap-2">
        <?= csrfField() ?>
        <input type="hidden" name="v" value="<?= $versionId ?>">
        <input type="hidden" name="aktion" value="hochladen">
        <input type="file" name="csv_datei" accept=".csv,.txt" class="form-control" required>
        <button type="submit" class="btn btn-primary">Hochladen &amp; prüfen</button>
      </form>
    </div>

    <div class="admin-card mb-4">
      <h2 class="h5 mb-3">Interpret einzeln anlegen</h2>
      <form method="post" class="row g-2 align-items-end">
        <?= csrfField() ?>
        <input type="hidden" name="v" value="<?= $versionId ?>">
        <input type="hidden" name="aktion" value="hinzufuegen">
        <div class="col-6 col-md-2">
          <label class="form-label">Reihenfolge</label>
          <input type="number" name="reihenfolge" class="form-control" value="<?= count($interpreten) + 1 ?>" min="1" required>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Interpret</label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Songtitel</label>
          <input type="text" name="songtitel" class="form-control" required>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Originalinterpret (optional)</label>
          <input type="text" name="originalinterpret" class="form-control">
        </div>
        <div class="col-12 col-md-1">
          <button type="submit" class="btn btn-primary w-100">Anlegen</button>
        </div>
      </form>
    </div>
  <?php endif; ?>
<?php endif; ?>

<?php if ($bearbeitbar): ?>
  <div class="d-flex flex-column gap-2">
    <?php foreach ($interpreten as $interpret): ?>
      <form method="post" class="admin-card row g-2 align-items-end">
        <?= csrfField() ?>
        <input type="hidden" name="v" value="<?= $versionId ?>">
        <input type="hidden" name="aktion" value="aktualisieren">
        <input type="hidden" name="id" value="<?= (int)$interpret['id'] ?>">
        <div class="col-6 col-md-1">
          <label class="form-label small text-muted">#</label>
          <input type="number" name="reihenfolge" class="form-control" value="<?= (int)$interpret['reihenfolge'] ?>" min="1" required>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label small text-muted">Interpret</label>
          <input type="text" name="name" class="form-control" value="<?= e($interpret['name']) ?>" required>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label small text-muted">Songtitel</label>
          <input type="text" name="songtitel" class="form-control" value="<?= e($interpret['songtitel']) ?>" required>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label small text-muted">Original</label>
          <input type="text" name="originalinterpret" class="form-control" value="<?= e($interpret['originalinterpret']) ?>">
        </div>
        <div class="col-6 col-md-1">
          <button type="submit" class="btn btn-sm btn-outline-primary w-100">Speichern</button>
        </div>
        <div class="col-6 col-md-1">
          <button type="submit" name="aktion" value="loeschen" class="btn btn-sm btn-outline-danger w-100"
                  onclick="return confirm('Interpret wirklich löschen?');">Löschen</button>
        </div>
      </form>
    <?php endforeach; ?>
    <?php if (empty($interpreten)): ?>
      <p class="text-muted">Noch keine Interpreten angelegt.</p>
    <?php endif; ?>
  </div>
<?php else: ?>
  <div class="table-responsive">
    <table class="table admin-card align-middle">
      <thead>
        <tr><th>#</th><th>Interpret</th><th>Songtitel</th><th>Original</th></tr>
      </thead>
      <tbody>
        <?php foreach ($interpreten as $interpret): ?>
          <tr>
            <td><?= (int)$interpret['reihenfolge'] ?></td>
            <td><?= e($interpret['name']) ?></td>
            <td><?= e($interpret['songtitel']) ?></td>
            <td><?= e($interpret['originalinterpret']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($interpreten)): ?>
          <tr><td colspan="4" class="text-muted">Keine Interpreten vorhanden.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>

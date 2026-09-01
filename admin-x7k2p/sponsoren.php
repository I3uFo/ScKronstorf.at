<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
requireAdminLogin();

$pdo = db();
$aktivAdminNav = 'sponsoren';
$fehler = null;
$erfolg = null;

$uploadOrdner = __DIR__ . '/../assets/uploads/';
$erlaubteEndungen = ['png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'webp' => 'image/webp', 'svg' => 'image/svg+xml'];
$maxDateigroesse = 2 * 1024 * 1024;

function speichereSponsorLogo(array $datei, string $zielOrdner, array $erlaubteEndungen, int $maxGroesse): array
{
    if ($datei['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Datei-Upload fehlgeschlagen.'];
    }
    if ($datei['size'] > $maxGroesse) {
        return [null, 'Die Datei ist zu groß (maximal 2 MB).'];
    }
    $endung = strtolower(pathinfo($datei['name'], PATHINFO_EXTENSION));
    if (!isset($erlaubteEndungen[$endung])) {
        return [null, 'Nur PNG, JPG, WEBP oder SVG sind als Logo erlaubt.'];
    }
    $dateiname = bin2hex(random_bytes(8)) . '.' . $endung;
    if (!move_uploaded_file($datei['tmp_name'], $zielOrdner . $dateiname)) {
        return [null, 'Die Datei konnte nicht gespeichert werden.'];
    }
    return [$dateiname, null];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck()) {
        $fehler = 'Sicherheitsprüfung fehlgeschlagen.';
    } else {
        $aktion = (string)($_POST['aktion'] ?? '');

        if ($aktion === 'anlegen') {
            $name = trim((string)($_POST['name'] ?? ''));
            $link = trim((string)($_POST['link'] ?? ''));
            $reihenfolge = (int)($_POST['reihenfolge'] ?? 0);

            if ($name === '') {
                $fehler = 'Bitte einen Namen angeben.';
            } elseif (empty($_FILES['logo']['name'])) {
                $fehler = 'Bitte ein Logo hochladen.';
            } else {
                [$dateiname, $uploadFehler] = speichereSponsorLogo($_FILES['logo'], $uploadOrdner, $erlaubteEndungen, $maxDateigroesse);
                if ($uploadFehler !== null) {
                    $fehler = $uploadFehler;
                } else {
                    $insert = $pdo->prepare('INSERT INTO sponsoren (name, logo_datei, link, reihenfolge) VALUES (?, ?, ?, ?)');
                    $insert->execute([$name, $dateiname, $link, $reihenfolge]);
                    $erfolg = 'Sponsor "' . $name . '" wurde angelegt.';
                }
            }
        } elseif ($aktion === 'aktualisieren') {
            $id = (int)($_POST['id'] ?? 0);
            $name = trim((string)($_POST['name'] ?? ''));
            $link = trim((string)($_POST['link'] ?? ''));
            $reihenfolge = (int)($_POST['reihenfolge'] ?? 0);

            if ($name === '') {
                $fehler = 'Bitte einen Namen angeben.';
            } else {
                if (!empty($_FILES['logo']['name'])) {
                    [$dateiname, $uploadFehler] = speichereSponsorLogo($_FILES['logo'], $uploadOrdner, $erlaubteEndungen, $maxDateigroesse);
                    if ($uploadFehler !== null) {
                        $fehler = $uploadFehler;
                    } else {
                        $stmt = $pdo->prepare('SELECT logo_datei FROM sponsoren WHERE id = ?');
                        $stmt->execute([$id]);
                        $altesLogo = $stmt->fetchColumn();
                        $update = $pdo->prepare('UPDATE sponsoren SET name = ?, link = ?, reihenfolge = ?, logo_datei = ? WHERE id = ?');
                        $update->execute([$name, $link, $reihenfolge, $dateiname, $id]);
                        if ($altesLogo && is_file($uploadOrdner . $altesLogo)) {
                            @unlink($uploadOrdner . $altesLogo);
                        }
                        $erfolg = 'Sponsor wurde aktualisiert.';
                    }
                } else {
                    $update = $pdo->prepare('UPDATE sponsoren SET name = ?, link = ?, reihenfolge = ? WHERE id = ?');
                    $update->execute([$name, $link, $reihenfolge, $id]);
                    $erfolg = 'Sponsor wurde aktualisiert.';
                }
            }
        } elseif ($aktion === 'loeschen') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $pdo->prepare('SELECT logo_datei FROM sponsoren WHERE id = ?');
            $stmt->execute([$id]);
            $logo = $stmt->fetchColumn();
            $delete = $pdo->prepare('DELETE FROM sponsoren WHERE id = ?');
            $delete->execute([$id]);
            if ($logo && is_file($uploadOrdner . $logo)) {
                @unlink($uploadOrdner . $logo);
            }
            $erfolg = 'Sponsor wurde gelöscht.';
        }
    }
}

$sponsoren = $pdo->query('SELECT * FROM sponsoren ORDER BY reihenfolge ASC, id ASC')->fetchAll();

require __DIR__ . '/includes/admin_header.php';
?>
<h1 class="h3 mb-4">Sponsoren</h1>

<?php if ($fehler !== null): ?><div class="alert alert-danger"><?= e($fehler) ?></div><?php endif; ?>
<?php if ($erfolg !== null): ?><div class="alert alert-success"><?= e($erfolg) ?></div><?php endif; ?>

<div class="admin-card mb-4">
  <h2 class="h5 mb-3">Sponsor hinzufügen</h2>
  <form method="post" enctype="multipart/form-data" class="row g-2 align-items-end">
    <?= csrfField() ?>
    <input type="hidden" name="aktion" value="anlegen">
    <div class="col-12 col-md-4">
      <label class="form-label">Name</label>
      <input type="text" name="name" class="form-control" required>
    </div>
    <div class="col-12 col-md-4">
      <label class="form-label">Link (optional)</label>
      <input type="url" name="link" class="form-control" placeholder="https://...">
    </div>
    <div class="col-6 col-md-2">
      <label class="form-label">Reihenfolge</label>
      <input type="number" name="reihenfolge" class="form-control" value="<?= count($sponsoren) ?>">
    </div>
    <div class="col-12 col-md-2">
      <label class="form-label">Logo</label>
      <input type="file" name="logo" accept=".png,.jpg,.jpeg,.webp,.svg" class="form-control" required>
    </div>
    <div class="col-12">
      <button type="submit" class="btn btn-primary">Hinzufügen</button>
    </div>
  </form>
</div>

<div class="row g-3">
  <?php foreach ($sponsoren as $sponsor): ?>
    <div class="col-12 col-md-6">
      <div class="admin-card">
        <form method="post" enctype="multipart/form-data" class="row g-2 align-items-end">
          <?= csrfField() ?>
          <input type="hidden" name="aktion" value="aktualisieren">
          <input type="hidden" name="id" value="<?= (int)$sponsor['id'] ?>">
          <div class="col-3 text-center">
            <img src="../assets/uploads/<?= e($sponsor['logo_datei']) ?>" alt="<?= e($sponsor['name']) ?>" style="max-width:100%; max-height:60px;">
          </div>
          <div class="col-9">
            <input type="text" name="name" class="form-control mb-2" value="<?= e($sponsor['name']) ?>" required>
            <input type="url" name="link" class="form-control mb-2" value="<?= e($sponsor['link']) ?>" placeholder="https://...">
            <div class="d-flex flex-wrap gap-2 align-items-center">
              <input type="number" name="reihenfolge" class="form-control" style="max-width:6rem;" value="<?= (int)$sponsor['reihenfolge'] ?>">
              <input type="file" name="logo" accept=".png,.jpg,.jpeg,.webp,.svg" class="form-control form-control-sm" style="min-width: 10rem;">
            </div>
          </div>
          <div class="col-12 d-flex gap-2 mt-2">
            <button type="submit" class="btn btn-sm btn-outline-primary">Speichern</button>
          </div>
        </form>
        <form method="post" class="mt-2" onsubmit="return confirm('Sponsor wirklich löschen?');">
          <?= csrfField() ?>
          <input type="hidden" name="aktion" value="loeschen">
          <input type="hidden" name="id" value="<?= (int)$sponsor['id'] ?>">
          <button type="submit" class="btn btn-sm btn-outline-danger">Löschen</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (empty($sponsoren)): ?>
    <p class="text-muted">Noch keine Sponsoren angelegt.</p>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>

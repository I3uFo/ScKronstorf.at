<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
requireAdminLogin();

$pdo = db();
$aktivAdminNav = 'texte';
$erfolg = null;
$fehler = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck()) {
        $fehler = 'Sicherheitsprüfung fehlgeschlagen.';
    } else {
        $texte = $_POST['text'] ?? [];
        $update = $pdo->prepare('UPDATE seiten_texte SET inhalt = ? WHERE id = ?');
        foreach ($texte as $id => $inhalt) {
            $update->execute([(string)$inhalt, (int)$id]);
        }
        $erfolg = 'Texte wurden gespeichert.';
    }
}

$alle = $pdo->query('SELECT * FROM seiten_texte ORDER BY seite ASC, schluessel ASC')->fetchAll();
$gruppiert = [];
foreach ($alle as $zeile) {
    $gruppiert[$zeile['seite']][] = $zeile;
}

require __DIR__ . '/includes/admin_header.php';
?>
<h1 class="h3 mb-4">Texte bearbeiten</h1>

<?php if ($fehler !== null): ?><div class="alert alert-danger"><?= e($fehler) ?></div><?php endif; ?>
<?php if ($erfolg !== null): ?><div class="alert alert-success"><?= e($erfolg) ?></div><?php endif; ?>

<form method="post">
  <?= csrfField() ?>
  <?php foreach ($gruppiert as $seite => $zeilen): ?>
    <div class="admin-card mb-4">
      <h2 class="h5 mb-3 text-capitalize"><?= e($seite) ?></h2>
      <?php foreach ($zeilen as $zeile): ?>
        <div class="mb-3">
          <label class="form-label"><code><?= e($zeile['schluessel']) ?></code></label>
          <textarea name="text[<?= (int)$zeile['id'] ?>]" class="form-control" rows="3"><?= e($zeile['inhalt']) ?></textarea>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
  <button type="submit" class="btn btn-primary">Alle Texte speichern</button>
</form>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>

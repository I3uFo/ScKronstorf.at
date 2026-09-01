<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
requireAdminLogin();

$pdo = db();
$aktivAdminNav = 'texte';
$erfolg = null;
$fehler = null;

$texteSeiten = $pdo->query('SELECT DISTINCT seite FROM seiten_texte ORDER BY seite ASC')->fetchAll(PDO::FETCH_COLUMN);

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

$aktiveTexteSeite = (string)($_GET['seite'] ?? $_POST['seite'] ?? '');
if (!in_array($aktiveTexteSeite, $texteSeiten, true)) {
    $aktiveTexteSeite = $texteSeiten[0] ?? '';
}

$zeilen = [];
if ($aktiveTexteSeite !== '') {
    $stmt = $pdo->prepare('SELECT * FROM seiten_texte WHERE seite = ? ORDER BY schluessel ASC');
    $stmt->execute([$aktiveTexteSeite]);
    $zeilen = $stmt->fetchAll();
}

require __DIR__ . '/includes/admin_header.php';
?>
<h1 class="h3 mb-4">Texte bearbeiten<?= $aktiveTexteSeite !== '' ? ' – ' . e(seitenLabel($aktiveTexteSeite)) : '' ?></h1>

<?php if ($fehler !== null): ?><div class="alert alert-danger"><?= e($fehler) ?></div><?php endif; ?>
<?php if ($erfolg !== null): ?><div class="alert alert-success"><?= e($erfolg) ?></div><?php endif; ?>

<?php if ($aktiveTexteSeite === ''): ?>
  <p class="text-muted">Es sind noch keine Texte hinterlegt.</p>
<?php else: ?>
  <form method="post">
    <?= csrfField() ?>
    <input type="hidden" name="seite" value="<?= e($aktiveTexteSeite) ?>">
    <div class="admin-card mb-4">
      <?php foreach ($zeilen as $zeile): ?>
        <div class="mb-3">
          <label class="form-label"><code><?= e($zeile['schluessel']) ?></code></label>
          <textarea name="text[<?= (int)$zeile['id'] ?>]" class="form-control" rows="3"><?= e($zeile['inhalt']) ?></textarea>
        </div>
      <?php endforeach; ?>
    </div>
    <button type="submit" class="btn btn-primary">Texte speichern</button>
  </form>
<?php endif; ?>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>

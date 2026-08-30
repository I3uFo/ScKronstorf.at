<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
requireAdminLogin();

$pdo = db();
$aktivAdminNav = 'voting';
$fehler = null;
$erfolg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck()) {
        $fehler = 'Sicherheitsprüfung fehlgeschlagen.';
    } else {
        $aktion = (string)($_POST['aktion'] ?? '');

        if ($aktion === 'anlegen') {
            $name = trim((string)($_POST['name'] ?? ''));
            if ($name === '') {
                $fehler = 'Bitte einen Namen für die Voting-Version angeben.';
            } else {
                $stmt = $pdo->prepare('INSERT INTO voting_versionen (name) VALUES (?)');
                $stmt->execute([$name]);
                $erfolg = 'Voting-Version "' . $name . '" wurde angelegt.';
            }
        } elseif ($aktion === 'oeffnen') {
            $id = (int)($_POST['id'] ?? 0);
            $bereitsOffen = aktuelleVotingVersion($pdo, 'offen');
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM interpreten WHERE voting_version_id = ?');
            $stmt->execute([$id]);
            $anzahlInterpreten = (int)$stmt->fetchColumn();

            if ($bereitsOffen !== null && (int)$bereitsOffen['id'] !== $id) {
                $fehler = 'Es ist bereits die Voting-Version "' . $bereitsOffen['name'] . '" geöffnet. Bitte diese zuerst schließen.';
            } elseif ($anzahlInterpreten === 0) {
                $fehler = 'Diese Voting-Version hat noch keine Interpreten. Bitte zuerst Interpreten importieren.';
            } else {
                $update = $pdo->prepare("UPDATE voting_versionen SET status = 'offen' WHERE id = ? AND status = 'vorbereitung'");
                $update->execute([$id]);
                $erfolg = 'Voting wurde geöffnet.';
            }
        } elseif ($aktion === 'schliessen') {
            $id = (int)($_POST['id'] ?? 0);
            $update = $pdo->prepare("UPDATE voting_versionen SET status = 'geschlossen' WHERE id = ? AND status = 'offen'");
            $update->execute([$id]);
            $erfolg = 'Voting wurde geschlossen.';
        } elseif ($aktion === 'loeschen') {
            $id = (int)($_POST['id'] ?? 0);
            $delete = $pdo->prepare("DELETE FROM voting_versionen WHERE id = ? AND status != 'offen'");
            $delete->execute([$id]);
            if ($delete->rowCount() > 0) {
                $erfolg = 'Voting-Version wurde inklusive aller zugehörigen Interpreten und Stimmen gelöscht.';
            } else {
                $fehler = 'Ein geöffnetes Voting kann nicht gelöscht werden. Bitte zuerst schließen.';
            }
        }
    }
}

$versionen = $pdo->query('SELECT * FROM voting_versionen ORDER BY id DESC')->fetchAll();
$interpretenAnzahl = [];
foreach ($versionen as $v) {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM interpreten WHERE voting_version_id = ?');
    $stmt->execute([$v['id']]);
    $interpretenAnzahl[$v['id']] = (int)$stmt->fetchColumn();
}

require __DIR__ . '/includes/admin_header.php';
?>
<h1 class="h3 mb-4">Voting-Versionen</h1>

<?php if ($fehler !== null): ?><div class="alert alert-danger"><?= e($fehler) ?></div><?php endif; ?>
<?php if ($erfolg !== null): ?><div class="alert alert-success"><?= e($erfolg) ?></div><?php endif; ?>

<div class="admin-card mb-4">
  <h2 class="h5 mb-3">Neue Voting-Version anlegen</h2>
  <form method="post" class="d-flex flex-column flex-md-row gap-2">
    <?= csrfField() ?>
    <input type="hidden" name="aktion" value="anlegen">
    <input type="text" name="name" class="form-control" placeholder="z. B. Songcontest 2026" required>
    <button type="submit" class="btn btn-primary">Anlegen</button>
  </form>
</div>

<div class="table-responsive">
  <table class="table admin-card align-middle">
    <thead>
      <tr>
        <th>Name</th>
        <th>Status</th>
        <th>Interpreten</th>
        <th>Erstellt am</th>
        <th>Aktionen</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($versionen as $v): ?>
        <tr>
          <td><?= e($v['name']) ?></td>
          <td><span class="badge text-bg-secondary"><?= e($v['status']) ?></span></td>
          <td><?= $interpretenAnzahl[$v['id']] ?></td>
          <td><?= e($v['erstellt_am']) ?></td>
          <td class="d-flex flex-wrap gap-2">
            <a href="interpreten.php?v=<?= (int)$v['id'] ?>" class="btn btn-sm btn-outline-secondary">Interpreten</a>
            <?php if ($v['status'] === 'vorbereitung'): ?>
              <form method="post" class="d-inline">
                <?= csrfField() ?>
                <input type="hidden" name="aktion" value="oeffnen">
                <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
                <button type="submit" class="btn btn-sm btn-success">Voting öffnen</button>
              </form>
            <?php elseif ($v['status'] === 'offen'): ?>
              <form method="post" class="d-inline">
                <?= csrfField() ?>
                <input type="hidden" name="aktion" value="schliessen">
                <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
                <button type="submit" class="btn btn-sm btn-danger">Voting schließen</button>
              </form>
            <?php else: ?>
              <a href="beamer_control.php?v=<?= (int)$v['id'] ?>" class="btn btn-sm btn-outline-dark">Beamer steuern</a>
            <?php endif; ?>
            <?php if ($v['status'] !== 'offen'): ?>
              <form method="post" class="d-inline">
                <?= csrfField() ?>
                <input type="hidden" name="aktion" value="loeschen">
                <input type="hidden" name="id" value="<?= (int)$v['id'] ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger"
                        onclick="return confirm('Voting-Version „<?= e($v['name']) ?>“ inklusive aller Interpreten und Stimmen unwiderruflich löschen?');">Löschen</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($versionen)): ?>
        <tr><td colspan="5" class="text-muted">Noch keine Voting-Version angelegt.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>

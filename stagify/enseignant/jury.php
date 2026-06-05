<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('enseignant');
$pdo = getDB(); $ensId = $_SESSION['profil_id'];
$soutenances = $pdo->prepare('SELECT sout.*, et.nom, et.prenom, j.membres_jury FROM soutenance sout JOIN stage s ON sout.num_stage = s.num_stage JOIN etudiant et ON s.id_etudiant = et.id_etudiant LEFT JOIN jury j ON sout.num_jury = j.num_jury WHERE et.num_enseignant_ref = ? ORDER BY sout.date');
$soutenances->execute([$ensId]);
$rows = $soutenances->fetchAll();
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Stagify — Jury &amp; Soutenances</title><link rel="stylesheet" href="../css/style.css"/></head>
<body><div class="app-layout">
<aside class="sidebar"><div class="brand">Stagify<br><span class="role-badge">Enseignant</span></div><nav>
<a href="dashboard.php"> Tableau de bord</a><a href="etudiants.php"> Mes étudiants</a>
<a href="visites.php"> Visites de suivi</a><a href="notes.php"> Notes &amp; évaluations</a>
<a href="problemes.php"> Problèmes signalés</a><a href="jury.php" class="active"> Jury &amp; soutenances</a>
</nav><div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div></aside>
<div class="main-content">
<header class="topbar"><span class="page-title">Jury &amp; Soutenances</span><div class="user-info"><div class="avatar green"><?= userInitials() ?></div><span><?= userName() ?></span></div></header>
<main class="page-body"><?= flashMessage() ?>
<div class="card">
  <div class="table-wrapper"><table>
    <thead><tr><th>Étudiant</th><th>Date</th><th>Heure</th><th>Jury</th><th>Note rapport</th><th>Note oral</th><th>Action</th></tr></thead>
    <tbody>
      <?php foreach ($rows as $s): ?>
      <tr>
        <td><?= htmlspecialchars($s['prenom'].' '.$s['nom']) ?></td>
        <td><?= $s['date'] ? date('d/m/Y',strtotime($s['date'])) : '—' ?></td>
        <td><?= $s['heures'] ? substr($s['heures'],0,5) : '—' ?></td>
        <td><?= htmlspecialchars($s['membres_jury']??'—') ?></td>
        <td>
          <?php if ($s['note_rapport'] !== null): ?>
            <strong><?= $s['note_rapport'] ?>/20</strong>
          <?php else: ?>
            <form method="POST" action="../actions/noter_soutenance.php" style="display:flex;gap:.4rem;align-items:center;">
              <input type="hidden" name="num_soutenance" value="<?= $s['num_soutenance'] ?>"/>
              <input type="hidden" name="champ" value="note_rapport"/>
              <input class="form-control" style="width:75px;" type="number" name="valeur" min="0" max="20" step="0.5" placeholder="—"/>
              <button class="btn btn-primary btn-sm">OK</button>
            </form>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($s['note_oral'] !== null): ?>
            <strong><?= $s['note_oral'] ?>/20</strong>
          <?php else: ?>
            <form method="POST" action="../actions/noter_soutenance.php" style="display:flex;gap:.4rem;align-items:center;">
              <input type="hidden" name="num_soutenance" value="<?= $s['num_soutenance'] ?>"/>
              <input type="hidden" name="champ" value="note_oral"/>
              <input class="form-control" style="width:75px;" type="number" name="valeur" min="0" max="20" step="0.5" placeholder="—"/>
              <button class="btn btn-primary btn-sm">OK</button>
            </form>
          <?php endif; ?>
        </td>
        <td><?= ($s['note_rapport']!==null && $s['note_oral']!==null) ? '<span class="badge badge-green">Noté</span>' : '<span class="badge badge-yellow">En attente</span>' ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($rows)): ?><tr><td colspan="7" style="text-align:center;color:var(--muted);">Aucune soutenance planifiée.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
</main></div></div></body></html>

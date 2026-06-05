<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('enseignant');
$pdo = getDB(); $ensId = $_SESSION['profil_id'];

$stages = $pdo->prepare('SELECT s.num_stage, et.nom, et.prenom, ent.nom AS ent_nom FROM stage s JOIN etudiant et ON s.id_etudiant = et.id_etudiant JOIN entreprise ent ON s.id_entreprise = ent.id_entreprise WHERE et.num_enseignant_ref = ?');
$stages->execute([$ensId]);
$stagesList = $stages->fetchAll();

$notes = $pdo->prepare('SELECT n.*, et.nom, et.prenom, ent.nom AS ent_nom FROM notation n JOIN stage s ON n.num_stage = s.num_stage JOIN etudiant et ON s.id_etudiant = et.id_etudiant JOIN entreprise ent ON s.id_entreprise = ent.id_entreprise WHERE n.num_enseignant = ? ORDER BY n.date_notation DESC');
$notes->execute([$ensId]);
$notesRows = $notes->fetchAll();
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Stagify — Notes</title><link rel="stylesheet" href="../css/style.css"/></head>
<body><div class="app-layout">
<aside class="sidebar"><div class="brand">Stagify<br><span class="role-badge">Enseignant</span></div><nav>
<a href="dashboard.php"> Tableau de bord</a><a href="etudiants.php"> Mes étudiants</a>
<a href="visites.php"> Visites de suivi</a><a href="notes.php" class="active"> Notes &amp; évaluations</a>
<a href="problemes.php"> Problèmes signalés</a><a href="jury.php"> Jury &amp; soutenances</a>
</nav><div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div></aside>
<div class="main-content">
<header class="topbar"><span class="page-title">Notes &amp; évaluations</span><div class="user-info"><div class="avatar green"><?= userInitials() ?></div><span><?= userName() ?></span></div></header>
<main class="page-body"><?= flashMessage() ?>
<div class="card mb2">
  <div class="card-header"><span class="card-title">Saisir une note de stage</span></div>
  <form method="POST" action="../actions/noter_stage.php" style="max-width:500px;">
    <div class="form-group">
      <label class="form-label">Étudiant</label>
      <select class="form-control" name="num_stage" required>
        <option value="">-- Choisir --</option>
        <?php foreach ($stagesList as $s): ?>
        <option value="<?= $s['num_stage'] ?>"><?= htmlspecialchars($s['prenom'].' '.$s['nom'].' — '.$s['ent_nom']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Note /20</label><input class="form-control" type="number" name="note" min="0" max="20" step="0.5" required /></div>
      <div class="form-group"><label class="form-label">Date</label><input class="form-control" type="date" name="date_notation" value="<?= date('Y-m-d') ?>" required /></div>
    </div>
    <div class="form-group"><label class="form-label">Commentaire</label><textarea class="form-control" name="commentaire" rows="3" placeholder="Remarques sur le stage…"></textarea></div>
    <button type="submit" class="btn btn-primary">Enregistrer</button>
  </form>
</div>
<div class="card">
  <div class="card-header"><span class="card-title">Récapitulatif des notes</span></div>
  <div class="table-wrapper"><table>
    <thead><tr><th>Étudiant</th><th>Entreprise</th><th>Note</th><th>Date</th><th>Commentaire</th></tr></thead>
    <tbody>
      <?php foreach ($notesRows as $n): ?>
      <tr><td><?= htmlspecialchars($n['prenom'].' '.$n['nom']) ?></td><td><?= htmlspecialchars($n['ent_nom']) ?></td><td><strong><?= $n['note'] ?>/20</strong></td><td><?= date('d/m/Y',strtotime($n['date_notation'])) ?></td><td><?= htmlspecialchars($n['commentaire']??'—') ?></td></tr>
      <?php endforeach; ?>
      <?php if(empty($notesRows)): ?><tr><td colspan="5" style="text-align:center;color:var(--muted);">Aucune note saisie.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
</main></div></div></body></html>

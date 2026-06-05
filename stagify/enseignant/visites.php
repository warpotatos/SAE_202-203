<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('enseignant');
$pdo = getDB(); $ensId = $_SESSION['profil_id'];

// Récupérer les stages de ses étudiants pour le select
$stages = $pdo->prepare('SELECT s.num_stage, et.nom, et.prenom, ent.nom AS ent_nom FROM stage s JOIN etudiant et ON s.id_etudiant = et.id_etudiant JOIN entreprise ent ON s.id_entreprise = ent.id_entreprise WHERE et.num_enseignant_ref = ?');
$stages->execute([$ensId]);
$stagesList = $stages->fetchAll();

$visites = $pdo->prepare('SELECT vs.*, et.nom, et.prenom, ent.nom AS ent_nom FROM visite_suivi vs JOIN stage s ON vs.num_stage = s.num_stage JOIN etudiant et ON s.id_etudiant = et.id_etudiant JOIN entreprise ent ON s.id_entreprise = ent.id_entreprise WHERE vs.num_enseignant = ? ORDER BY vs.date_visite DESC');
$visites->execute([$ensId]);
$visitesRows = $visites->fetchAll();
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Stagify — Visites de suivi</title><link rel="stylesheet" href="../css/style.css"/></head>
<body><div class="app-layout">
<aside class="sidebar"><div class="brand">Stagify<br><span class="role-badge">Enseignant</span></div><nav>
<a href="dashboard.php"> Tableau de bord</a><a href="etudiants.php"> Mes étudiants</a>
<a href="visites.php" class="active"> Visites de suivi</a><a href="notes.php"> Notes &amp; évaluations</a>
<a href="problemes.php"> Problèmes signalés</a><a href="jury.php"> Jury &amp; soutenances</a>
</nav><div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div></aside>
<div class="main-content">
<header class="topbar"><span class="page-title">Visites de suivi</span><div class="user-info"><div class="avatar green"><?= userInitials() ?></div><span><?= userName() ?></span></div></header>
<main class="page-body"><?= flashMessage() ?>

<div class="card mb2">
  <div class="card-header"><span class="card-title">Planifier une visite</span></div>
  <form method="POST" action="../actions/planifier_visite.php">
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Étudiant / Stage</label>
        <select class="form-control" name="num_stage" required>
          <option value="">-- Choisir un étudiant --</option>
          <?php foreach ($stagesList as $s): ?>
          <option value="<?= $s['num_stage'] ?>"><?= htmlspecialchars($s['prenom'].' '.$s['nom'].' — '.$s['ent_nom']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Date de la visite</label>
        <input class="form-control" type="date" name="date_visite" required />
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Heure</label>
        <input class="form-control" type="time" name="heure_visite" required />
      </div>
      <div class="form-group">
        <label class="form-label">Lieu</label>
        <input class="form-control" type="text" name="lieu" placeholder="Entreprise – Salle…" required />
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Commentaires</label>
      <textarea class="form-control" name="commentaires" rows="3" placeholder="Observations, points à aborder…"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Enregistrer la visite</button>
  </form>
</div>

<div class="card">
  <div class="card-header"><span class="card-title">Historique des visites</span></div>
  <div class="table-wrapper">
    <table>
      <thead><tr><th>Étudiant</th><th>Entreprise</th><th>Date</th><th>Heure</th><th>Lieu</th><th>Commentaires</th></tr></thead>
      <tbody>
        <?php foreach ($visitesRows as $v): ?>
        <tr>
          <td><?= htmlspecialchars($v['prenom'].' '.$v['nom']) ?></td>
          <td><?= htmlspecialchars($v['ent_nom']) ?></td>
          <td><?= date('d/m/Y', strtotime($v['date_visite'])) ?></td>
          <td><?= $v['heure_visite'] ? substr($v['heure_visite'],0,5) : '—' ?></td>
          <td><?= htmlspecialchars($v['lieu']??'—') ?></td>
          <td><?= htmlspecialchars($v['commentaires']??'—') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($visitesRows)): ?><tr><td colspan="6" style="text-align:center;color:var(--muted);">Aucune visite enregistrée.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</main></div></div></body></html>

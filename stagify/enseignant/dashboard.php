<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('enseignant');

$pdo   = getDB();
$ensId = $_SESSION['profil_id'];

$nbEtudiants = $pdo->prepare('SELECT COUNT(*) FROM etudiant WHERE num_enseignant_ref = ?');
$nbEtudiants->execute([$ensId]);

$nbVisites = $pdo->prepare('SELECT COUNT(*) FROM visite_suivi vs JOIN stage s ON vs.num_stage = s.num_stage JOIN etudiant e ON s.id_etudiant = e.id_etudiant WHERE e.num_enseignant_ref = ? AND vs.date_visite >= CURDATE()');
$nbVisites->execute([$ensId]);

$nbProblemes = $pdo->prepare("SELECT COUNT(*) FROM probleme p JOIN stage s ON p.num_stage = s.num_stage JOIN etudiant e ON s.id_etudiant = e.id_etudiant WHERE e.num_enseignant_ref = ? AND p.statut != 'resolu'");
$nbProblemes->execute([$ensId]);

$nbSoutenances = $pdo->prepare('SELECT COUNT(*) FROM soutenance sout JOIN stage s ON sout.num_stage = s.num_stage JOIN etudiant e ON s.id_etudiant = e.id_etudiant WHERE e.num_enseignant_ref = ? AND sout.date >= CURDATE()');
$nbSoutenances->execute([$ensId]);

$visites = $pdo->prepare('SELECT vs.*, et.nom, et.prenom, ent.nom AS ent_nom FROM visite_suivi vs JOIN stage s ON vs.num_stage = s.num_stage JOIN etudiant et ON s.id_etudiant = et.id_etudiant JOIN entreprise ent ON s.id_entreprise = ent.id_entreprise WHERE vs.num_enseignant = ? AND vs.date_visite >= CURDATE() ORDER BY vs.date_visite LIMIT 5');
$visites->execute([$ensId]);

$problemes = $pdo->prepare("SELECT p.*, et.nom, et.prenom FROM probleme p JOIN stage s ON p.num_stage = s.num_stage JOIN etudiant et ON s.id_etudiant = et.id_etudiant WHERE et.num_enseignant_ref = ? AND p.statut != 'resolu' ORDER BY p.date_signalement DESC LIMIT 5");
$problemes->execute([$ensId]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Stagify — Enseignant · Tableau de bord</title>
  <link rel="stylesheet" href="../css/style.css"/>
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="brand">Stagify<br><span class="role-badge">Enseignant</span></div>
    <nav>
      <a href="dashboard.php" class="active"> Tableau de bord</a>
      <a href="etudiants.php"> Mes étudiants</a>
      <a href="visites.php"> Visites de suivi</a>
      <a href="notes.php"> Notes &amp; évaluations</a>
      <a href="problemes.php"> Problèmes signalés</a>
      <a href="jury.php"> Jury &amp; soutenances</a>
    </nav>
    <div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div>
  </aside>
  <div class="main-content">
    <header class="topbar">
      <span class="page-title">Tableau de bord</span>
      <div class="user-info"><div class="avatar green"><?= userInitials() ?></div><span><?= userName() ?> — Enseignant</span></div>
    </header>
    <main class="page-body">
      <?= flashMessage() ?>
      <div class="stats-row">
        <div class="stat-card"><div class="stat-label">Étudiants encadrés</div><div class="stat-value"><?= $nbEtudiants->fetchColumn() ?></div></div>
        <div class="stat-card"><div class="stat-label">Visites à venir</div><div class="stat-value"><?= $nbVisites->fetchColumn() ?></div></div>
        <div class="stat-card"><div class="stat-label">Problèmes ouverts</div><div class="stat-value"><?= $nbProblemes->fetchColumn() ?></div></div>
        <div class="stat-card"><div class="stat-label">Soutenances à venir</div><div class="stat-value"><?= $nbSoutenances->fetchColumn() ?></div></div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
        <div class="card">
          <div class="card-header"><span class="card-title">Prochaines visites</span><a href="visites.php" class="btn btn-outline btn-sm">Voir tout</a></div>
          <div class="table-wrapper">
            <table>
              <thead><tr><th>Étudiant</th><th>Date</th><th>Entreprise</th></tr></thead>
              <tbody>
                <?php foreach ($visites->fetchAll() as $v): ?>
                <tr><td><?= htmlspecialchars($v['prenom'].' '.$v['nom']) ?></td><td><?= date('d/m/Y', strtotime($v['date_visite'])) ?></td><td><?= htmlspecialchars($v['ent_nom']) ?></td></tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <div class="card">
          <div class="card-header"><span class="card-title">Problèmes non résolus</span><a href="problemes.php" class="btn btn-outline btn-sm">Voir tout</a></div>
          <div class="table-wrapper">
            <table>
              <thead><tr><th>Étudiant</th><th>Date</th><th>Statut</th></tr></thead>
              <tbody>
                <?php foreach ($problemes->fetchAll() as $p): ?>
                <?php $bc = ['ouvert'=>'badge-red','en_cours'=>'badge-yellow']; ?>
                <tr><td><?= htmlspecialchars($p['prenom'].' '.$p['nom']) ?></td><td><?= date('d/m/Y', strtotime($p['date_signalement'])) ?></td><td><span class="badge <?= $bc[$p['statut']]??'badge-gray' ?>"><?= ucfirst(str_replace('_',' ',$p['statut'])) ?></span></td></tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>
</body></html>

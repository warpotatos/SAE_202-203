<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('maitre_stage');
$pdo = getDB(); $msId = $_SESSION['profil_id'];

$ms = $pdo->prepare('SELECT ms.*, ent.nom AS ent_nom, ent.id_entreprise FROM maitre_stage ms LEFT JOIN entreprise ent ON ms.id_entreprise = ent.id_entreprise WHERE ms.id_maitre = ?');
$ms->execute([$msId]); $ms = $ms->fetch();
$entId = $ms['id_entreprise'];

$nbOffres     = $entId ? $pdo->prepare("SELECT COUNT(*) FROM offre_stage WHERE id_entreprise = ?")->execute([$entId]) && $pdo->query("SELECT COUNT(*) FROM offre_stage WHERE id_entreprise = $entId")->fetchColumn() : 0;
$nbCandidatures = $pdo->prepare("SELECT COUNT(*) FROM postulation p JOIN offre_stage o ON p.num_offre = o.num_offre WHERE o.id_maitre = ? AND p.statut = 'en_attente'")->execute([$msId]) ? $pdo->query("SELECT COUNT(*) FROM postulation p JOIN offre_stage o ON p.num_offre = o.num_offre WHERE o.id_maitre = $msId AND p.statut = 'en_attente'")->fetchColumn() : 0;
$nbStagiaires = $pdo->query("SELECT COUNT(*) FROM stage WHERE id_maitre = $msId AND date_fin >= CURDATE()")->fetchColumn();
$nbOffresOuv  = $entId ? $pdo->query("SELECT COUNT(*) FROM offre_stage WHERE id_entreprise = $entId AND statut = 'ouverte'")->fetchColumn() : 0;

$offresRecentes = $entId ? $pdo->query("SELECT o.*, (SELECT COUNT(*) FROM postulation p WHERE p.num_offre = o.num_offre) AS nb_cand FROM offre_stage o WHERE o.id_entreprise = $entId ORDER BY o.date_publication DESC LIMIT 5")->fetchAll() : [];
$stagiairesList = $pdo->query("SELECT s.*, et.nom, et.prenom, ent.nom AS ent_nom FROM stage s JOIN etudiant et ON s.id_etudiant = et.id_etudiant JOIN entreprise ent ON s.id_entreprise = ent.id_entreprise WHERE s.id_maitre = $msId AND s.date_fin >= CURDATE() LIMIT 5")->fetchAll();
$statMap = ['ouverte'=>'badge-green','pourvue'=>'badge-gray','fermee'=>'badge-red'];
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Stagify — Professionnel · Tableau de bord</title><link rel="stylesheet" href="../css/style.css"/></head>
<body><div class="app-layout">
<aside class="sidebar"><div class="brand">Stagify<br><span class="role-badge">Professionnel</span></div><nav>
<a href="dashboard.php" class="active"> Tableau de bord</a>
<a href="entreprise.php"> Mon entreprise</a>
<a href="offres.php"> Mes offres de stage</a>
<a href="candidatures.php"> Candidatures reçues</a>
<a href="stagiaires.php"> Mes stagiaires</a>
</nav><div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div></aside>
<div class="main-content">
<header class="topbar"><span class="page-title">Tableau de bord</span><div class="user-info"><div class="avatar orange"><?= userInitials() ?></div><span><?= userName() ?> — Maître de stage</span></div></header>
<main class="page-body"><?= flashMessage() ?>
<div class="stats-row">
  <div class="stat-card"><div class="stat-label">Offres publiées</div><div class="stat-value"><?= $nbOffres ?></div></div>
  <div class="stat-card"><div class="stat-label">Candidatures en attente</div><div class="stat-value"><?= $nbCandidatures ?></div></div>
  <div class="stat-card"><div class="stat-label">Stagiaires actifs</div><div class="stat-value"><?= $nbStagiaires ?></div></div>
  <div class="stat-card"><div class="stat-label">Offres ouvertes</div><div class="stat-value"><?= $nbOffresOuv ?></div></div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
  <div class="card">
    <div class="card-header"><span class="card-title">Mes offres actives</span><a href="offres.php" class="btn btn-outline btn-sm">Gérer</a></div>
    <div class="table-wrapper"><table>
      <thead><tr><th>Titre</th><th>Candidatures</th><th>Statut</th></tr></thead>
      <tbody>
        <?php foreach ($offresRecentes as $o): ?>
        <tr><td><?= htmlspecialchars($o['titre']) ?></td><td><?= $o['nb_cand'] ?></td><td><span class="badge <?= $statMap[$o['statut']]??'badge-gray' ?>"><?= ucfirst($o['statut']) ?></span></td></tr>
        <?php endforeach; ?>
        <?php if(empty($offresRecentes)): ?><tr><td colspan="3" style="text-align:center;color:var(--muted);">Aucune offre publiée.</td></tr><?php endif; ?>
      </tbody>
    </table></div>
  </div>
  <div class="card">
    <div class="card-header"><span class="card-title">Stagiaires en cours</span><a href="stagiaires.php" class="btn btn-outline btn-sm">Voir tout</a></div>
    <div class="table-wrapper"><table>
      <thead><tr><th>Nom</th><th>Fin</th></tr></thead>
      <tbody>
        <?php foreach ($stagiairesList as $s): ?>
        <tr><td><?= htmlspecialchars($s['prenom'].' '.$s['nom']) ?></td><td><?= date('d/m/Y',strtotime($s['date_fin'])) ?></td></tr>
        <?php endforeach; ?>
        <?php if(empty($stagiairesList)): ?><tr><td colspan="2" style="text-align:center;color:var(--muted);">Aucun stagiaire actif.</td></tr><?php endif; ?>
      </tbody>
    </table></div>
  </div>
</div>
</main></div></div></body></html>

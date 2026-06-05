<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('admin');
$pdo = getDB();
$problemes = $pdo->query("SELECT p.*, et.nom, et.prenom, ent.nom AS ent_nom FROM probleme p JOIN stage s ON p.num_stage=s.num_stage JOIN etudiant et ON s.id_etudiant=et.id_etudiant JOIN entreprise ent ON s.id_entreprise=ent.id_entreprise ORDER BY FIELD(p.statut,'ouvert','en_cours','resolu'), p.date_signalement DESC")->fetchAll();
$bc = ['ouvert'=>'badge-red','en_cours'=>'badge-yellow','resolu'=>'badge-green'];
?>
<!DOCTYPE html><html lang="fr">
<head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Stagify — Admin · Problèmes</title><link rel="stylesheet" href="../css/style.css"/></head>
<body><div class="app-layout">
<aside class="sidebar"><div class="brand">Stagify<br><span class="role-badge">Administrateur</span></div><nav>
<a href="dashboard.php"> Tableau de bord</a>
<a href="utilisateurs.php"> Utilisateurs</a>
<a href="stages.php"> Tous les stages</a>
<a href="problemes.php" class="active"> Tous les problèmes</a>
</nav><div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div></aside>
<div class="main-content">
<header class="topbar"><span class="page-title">Tous les problèmes signalés</span>
<div class="user-info"><div class="avatar"><?= userInitials() ?></div><span><?= userName() ?></span></div></header>
<main class="page-body">
<div class="card"><div class="table-wrapper"><table>
  <thead><tr><th>#</th><th>Étudiant</th><th>Entreprise</th><th>Date incident</th><th>Description</th><th>Statut</th></tr></thead>
  <tbody>
    <?php foreach ($problemes as $p): ?>
    <tr>
      <td><?= $p['num_prob'] ?></td>
      <td><?= htmlspecialchars($p['prenom'].' '.$p['nom']) ?></td>
      <td><?= htmlspecialchars($p['ent_nom']) ?></td>
      <td><?= date('d/m/Y',strtotime($p['date_incident'])) ?></td>
      <td><?= htmlspecialchars(mb_substr($p['description'],0,100)) ?>…</td>
      <td><span class="badge <?= $bc[$p['statut']]??'badge-gray' ?>"><?= ucfirst(str_replace('_',' ',$p['statut'])) ?></span></td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($problemes)): ?><tr><td colspan="6" style="text-align:center;color:var(--muted);">Aucun problème signalé.</td></tr><?php endif; ?>
  </tbody>
</table></div></div>
</main></div></div></body></html>

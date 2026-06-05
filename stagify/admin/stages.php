<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('admin');
$pdo = getDB();
$stages = $pdo->query("SELECT s.*, et.nom AS etu_nom, et.prenom AS etu_prenom, ent.nom AS ent_nom, ms.nom AS ms_nom, ms.prenom AS ms_prenom FROM stage s JOIN etudiant et ON s.id_etudiant=et.id_etudiant JOIN entreprise ent ON s.id_entreprise=ent.id_entreprise LEFT JOIN maitre_stage ms ON s.id_maitre=ms.id_maitre ORDER BY s.date_debut DESC")->fetchAll();
$now = date('Y-m-d');
?>
<!DOCTYPE html><html lang="fr">
<head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Stagify — Admin · Stages</title><link rel="stylesheet" href="../css/style.css"/></head>
<body><div class="app-layout">
<aside class="sidebar"><div class="brand">Stagify<br><span class="role-badge">Administrateur</span></div><nav>
<a href="dashboard.php"> Tableau de bord</a>
<a href="utilisateurs.php"> Utilisateurs</a>
<a href="stages.php" class="active"> Tous les stages</a>
<a href="problemes.php"> Tous les problèmes</a>
</nav><div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div></aside>
<div class="main-content">
<header class="topbar"><span class="page-title">Tous les stages</span>
<div class="user-info"><div class="avatar"><?= userInitials() ?></div><span><?= userName() ?></span></div></header>
<main class="page-body">
<div class="card"><div class="table-wrapper"><table>
  <thead><tr><th>#</th><th>Étudiant</th><th>Entreprise</th><th>Maître de stage</th><th>Début</th><th>Fin</th><th>Statut</th></tr></thead>
  <tbody>
    <?php foreach ($stages as $s): ?>
    <?php
      if ($s['date_fin'] < $now) $badge = '<span class="badge badge-green">Terminé</span>';
      elseif ($s['date_debut'] <= $now) $badge = '<span class="badge badge-blue">En cours</span>';
      else $badge = '<span class="badge badge-orange">À venir</span>';
    ?>
    <tr>
      <td><?= $s['num_stage'] ?></td>
      <td><?= htmlspecialchars($s['etu_prenom'].' '.$s['etu_nom']) ?></td>
      <td><?= htmlspecialchars($s['ent_nom']) ?></td>
      <td><?= $s['ms_nom'] ? htmlspecialchars($s['ms_prenom'].' '.$s['ms_nom']) : '—' ?></td>
      <td><?= $s['date_debut'] ? date('d/m/Y',strtotime($s['date_debut'])) : '—' ?></td>
      <td><?= $s['date_fin'] ? date('d/m/Y',strtotime($s['date_fin'])) : '—' ?></td>
      <td><?= $badge ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($stages)): ?><tr><td colspan="7" style="text-align:center;color:var(--muted);">Aucun stage enregistré.</td></tr><?php endif; ?>
  </tbody>
</table></div></div>
</main></div></div></body></html>

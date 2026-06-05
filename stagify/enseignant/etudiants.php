<?php
// enseignant/etudiants.php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('enseignant');
$pdo = getDB(); $ensId = $_SESSION['profil_id'];
$etudiants = $pdo->prepare('SELECT et.*, u.email AS u_email, s.num_stage, s.date_debut, s.date_fin, ent.nom AS ent_nom FROM etudiant et JOIN utilisateur u ON et.num_utilisateur = u.num_utilisateur LEFT JOIN stage s ON s.id_etudiant = et.id_etudiant LEFT JOIN entreprise ent ON s.id_entreprise = ent.id_entreprise WHERE et.num_enseignant_ref = ? ORDER BY et.nom');
$etudiants->execute([$ensId]);
$rows = $etudiants->fetchAll();
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/><title>Stagify — Mes étudiants</title><link rel="stylesheet" href="../css/style.css"/></head>
<body><div class="app-layout">
<aside class="sidebar"><div class="brand">Stagify<br><span class="role-badge">Enseignant</span></div><nav>
<a href="dashboard.php"> Tableau de bord</a><a href="etudiants.php" class="active"> Mes étudiants</a>
<a href="visites.php"> Visites de suivi</a><a href="notes.php"> Notes &amp; évaluations</a>
<a href="problemes.php"> Problèmes signalés</a><a href="jury.php"> Jury &amp; soutenances</a>
</nav><div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div></aside>
<div class="main-content">
<header class="topbar"><span class="page-title">Mes étudiants</span><div class="user-info"><div class="avatar green"><?= userInitials() ?></div><span><?= userName() ?></span></div></header>
<main class="page-body"><?= flashMessage() ?>
<div class="card"><div class="table-wrapper"><table>
<thead><tr><th>Nom</th><th>Prénom</th><th>TD/TP</th><th>Email</th><th>Entreprise</th><th>Période</th><th>Statut</th></tr></thead>
<tbody>
<?php foreach ($rows as $r): ?>
<?php $now=date('Y-m-d'); $stat = !$r['num_stage'] ? ['badge-gray','Pas de stage'] : ($r['date_fin']<$now ? ['badge-green','Terminé'] : ($r['date_debut']<=$now ? ['badge-blue','En cours'] : ['badge-orange','À venir'])); ?>
<tr><td><?= htmlspecialchars($r['nom']) ?></td><td><?= htmlspecialchars($r['prenom']) ?></td><td><?= htmlspecialchars($r['TD'].'/'.$r['TP']) ?></td><td><?= htmlspecialchars($r['email']) ?></td><td><?= htmlspecialchars($r['ent_nom']??'—') ?></td><td><?= $r['date_debut'] ? date('d/m/Y',strtotime($r['date_debut'])).' – '.date('d/m/Y',strtotime($r['date_fin'])) : '—' ?></td><td><span class="badge <?= $stat[0] ?>"><?= $stat[1] ?></span></td></tr>
<?php endforeach; ?>
<?php if(empty($rows)): ?><tr><td colspan="7" style="text-align:center;color:var(--muted);">Aucun étudiant assigné.</td></tr><?php endif; ?>
</tbody></table></div></div>
</main></div></div></body></html>

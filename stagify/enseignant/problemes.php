<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('enseignant');
$pdo = getDB(); $ensId = $_SESSION['profil_id'];
$problemes = $pdo->prepare("SELECT p.*, et.nom, et.prenom, ent.nom AS ent_nom FROM probleme p JOIN stage s ON p.num_stage = s.num_stage JOIN etudiant et ON s.id_etudiant = et.id_etudiant JOIN entreprise ent ON s.id_entreprise = ent.id_entreprise WHERE et.num_enseignant_ref = ? ORDER BY FIELD(p.statut,'ouvert','en_cours','resolu'), p.date_signalement DESC");
$problemes->execute([$ensId]);
$rows = $problemes->fetchAll();
$bc = ['ouvert'=>'badge-red','en_cours'=>'badge-yellow','resolu'=>'badge-green'];
?>
<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Stagify — Problèmes signalés</title><link rel="stylesheet" href="../css/style.css"/></head>
<body><div class="app-layout">
<aside class="sidebar"><div class="brand">Stagify<br><span class="role-badge">Enseignant</span></div><nav>
<a href="dashboard.php"> Tableau de bord</a><a href="etudiants.php"> Mes étudiants</a>
<a href="visites.php"> Visites de suivi</a><a href="notes.php"> Notes &amp; évaluations</a>
<a href="problemes.php" class="active"> Problèmes signalés</a><a href="jury.php"> Jury &amp; soutenances</a>
</nav><div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div></aside>
<div class="main-content">
<header class="topbar"><span class="page-title">Problèmes signalés</span><div class="user-info"><div class="avatar green"><?= userInitials() ?></div><span><?= userName() ?></span></div></header>
<main class="page-body"><?= flashMessage() ?>
<div class="card"><div class="table-wrapper"><table>
<thead><tr><th>Étudiant</th><th>Entreprise</th><th>Date incident</th><th>Description</th><th>Statut</th><th>Action</th></tr></thead>
<tbody>
<?php foreach ($rows as $p): ?>
<tr>
  <td><?= htmlspecialchars($p['prenom'].' '.$p['nom']) ?></td>
  <td><?= htmlspecialchars($p['ent_nom']) ?></td>
  <td><?= date('d/m/Y',strtotime($p['date_incident'])) ?></td>
  <td><?= htmlspecialchars(mb_substr($p['description'],0,80)) ?>…</td>
  <td><span class="badge <?= $bc[$p['statut']]??'badge-gray' ?>"><?= ucfirst(str_replace('_',' ',$p['statut'])) ?></span></td>
  <td>
    <?php if ($p['statut'] === 'ouvert'): ?>
    <form method="POST" action="../actions/traiter_probleme.php" style="display:inline;">
      <input type="hidden" name="num_prob" value="<?= $p['num_prob'] ?>"/>
      <input type="hidden" name="statut" value="en_cours"/>
      <button class="btn btn-warning btn-sm">Prendre en charge</button>
    </form>
    <?php elseif ($p['statut'] === 'en_cours'): ?>
    <form method="POST" action="../actions/traiter_probleme.php" style="display:inline;">
      <input type="hidden" name="num_prob" value="<?= $p['num_prob'] ?>"/>
      <input type="hidden" name="statut" value="resolu"/>
      <button class="btn btn-success btn-sm">Marquer résolu</button>
    </form>
    <?php else: ?>
    <span style="color:var(--muted);font-size:.82rem;">Résolu</span>
    <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
<?php if(empty($rows)): ?><tr><td colspan="6" style="text-align:center;color:var(--muted);">Aucun problème signalé.</td></tr><?php endif; ?>
</tbody></table></div></div>
</main></div></div></body></html>

<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('maitre_stage');
$pdo = getDB(); $msId = $_SESSION['profil_id'];

$stagiaires = $pdo->prepare("
    SELECT s.*, et.nom, et.prenom, et.email AS etu_email, et.TD, et.TP,
           ent.nom AS ent_nom,
           ens.nom AS ens_nom, ens.prenom AS ens_prenom, ens.email AS ens_email,
           o.titre AS offre_titre,
           vs.date_visite AS prochaine_visite, vs.lieu AS visite_lieu
    FROM stage s
    JOIN etudiant et ON s.id_etudiant = et.id_etudiant
    JOIN entreprise ent ON s.id_entreprise = ent.id_entreprise
    LEFT JOIN enseignant ens ON et.num_enseignant_ref = ens.num_enseignant
    LEFT JOIN offre_stage o ON s.num_offre = o.num_offre
    LEFT JOIN visite_suivi vs ON vs.num_stage = s.num_stage AND vs.date_visite = (
        SELECT MIN(date_visite) FROM visite_suivi WHERE num_stage = s.num_stage AND date_visite >= CURDATE()
    )
    WHERE s.id_maitre = ?
    ORDER BY s.date_fin DESC
");
$stagiaires->execute([$msId]);
$rows = $stagiaires->fetchAll();
$now = date('Y-m-d');
?>
<!DOCTYPE html><html lang="fr">
<head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Stagify — Mes stagiaires</title><link rel="stylesheet" href="../css/style.css"/></head>
<body><div class="app-layout">
<aside class="sidebar"><div class="brand">Stagify<br><span class="role-badge">Professionnel</span></div><nav>
<a href="dashboard.php"> Tableau de bord</a>
<a href="entreprise.php"> Mon entreprise</a>
<a href="offres.php"> Mes offres de stage</a>
<a href="candidatures.php"> Candidatures reçues</a>
<a href="stagiaires.php" class="active"> Mes stagiaires</a>
</nav><div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div></aside>
<div class="main-content">
<header class="topbar"><span class="page-title">Mes stagiaires</span>
<div class="user-info"><div class="avatar orange"><?= userInitials() ?></div><span><?= userName() ?></span></div></header>
<main class="page-body"><?= flashMessage() ?>

<?php if (empty($rows)): ?>
  <div class="alert alert-info">Aucun stagiaire assigné. Acceptez des candidatures pour en avoir.</div>
<?php else: ?>

<?php $actifs = array_filter($rows, fn($r) => $r['date_debut'] <= $now && $r['date_fin'] >= $now); ?>
<?php if ($actifs): ?>
<h2 class="section-title">Stagiaires actuels</h2>
<div class="card-grid">
  <?php foreach ($actifs as $s): ?>
  <div class="card">
    <div class="card-header">
      <span class="card-title"><?= htmlspecialchars($s['prenom'].' '.$s['nom']) ?></span>
      <span class="badge badge-blue">En cours</span>
    </div>
    <div style="font-size:.875rem;display:grid;gap:.45rem;margin-bottom:1rem;">
      <div> <strong>Mission :</strong> <?= htmlspecialchars($s['offre_titre']??'—') ?></div>
      <div> <strong>Période :</strong> <?= date('d/m/Y',strtotime($s['date_debut'])) ?> → <?= date('d/m/Y',strtotime($s['date_fin'])) ?></div>
      <div> <strong>TD/TP :</strong> <?= htmlspecialchars($s['TD'].'/'.$s['TP']) ?></div>
      <div> <?= htmlspecialchars($s['etu_email']) ?></div>
      <?php if ($s['ens_nom']): ?>
      <div> <strong>Référent :</strong> <?= htmlspecialchars($s['ens_prenom'].' '.$s['ens_nom']) ?></div>
      <?php endif; ?>
      <?php if ($s['prochaine_visite']): ?>
      <div> <strong>Prochaine visite :</strong> <?= date('d/m/Y',strtotime($s['prochaine_visite'])) ?> — <?= htmlspecialchars($s['visite_lieu']??'') ?></div>
      <?php endif; ?>
    </div>
    <?php if ($s['contenu_du_stage']): ?>
    <p style="font-size:.82rem;color:var(--muted);"><?= htmlspecialchars(mb_substr($s['contenu_du_stage'],0,120)) ?>…</p>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php $anciens = array_filter($rows, fn($r) => $r['date_fin'] < $now); ?>
<?php if ($anciens): ?>
<h2 class="section-title mt2">Anciens stagiaires</h2>
<div class="card"><div class="table-wrapper"><table>
  <thead><tr><th>Nom</th><th>Mission</th><th>Début</th><th>Fin</th></tr></thead>
  <tbody>
    <?php foreach ($anciens as $s): ?>
    <tr>
      <td><?= htmlspecialchars($s['prenom'].' '.$s['nom']) ?></td>
      <td><?= htmlspecialchars($s['offre_titre']??'—') ?></td>
      <td><?= date('d/m/Y',strtotime($s['date_debut'])) ?></td>
      <td><?= date('d/m/Y',strtotime($s['date_fin'])) ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table></div></div>
<?php endif; ?>

<?php endif; ?>
</main></div></div></body></html>

<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('maitre_stage');
$pdo = getDB(); $msId = $_SESSION['profil_id'];

$ms = $pdo->prepare('SELECT id_entreprise FROM maitre_stage WHERE id_maitre=?');
$ms->execute([$msId]); $ms = $ms->fetch();
$entId = $ms['id_entreprise'];

$offres = $entId ? $pdo->query("SELECT o.*, (SELECT COUNT(*) FROM postulation p WHERE p.num_offre=o.num_offre) AS nb_cand FROM offre_stage o WHERE o.id_entreprise=$entId ORDER BY o.date_publication DESC")->fetchAll() : [];
$statMap = ['ouverte'=>'badge-green','pourvue'=>'badge-blue','fermee'=>'badge-red'];
?>
<!DOCTYPE html><html lang="fr">
<head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Stagify — Mes offres</title><link rel="stylesheet" href="../css/style.css"/></head>
<body><div class="app-layout">
<aside class="sidebar"><div class="brand">Stagify<br><span class="role-badge">Professionnel</span></div><nav>
<a href="dashboard.php"> Tableau de bord</a>
<a href="entreprise.php"> Mon entreprise</a>
<a href="offres.php" class="active"> Mes offres de stage</a>
<a href="candidatures.php"> Candidatures reçues</a>
<a href="stagiaires.php"> Mes stagiaires</a>
</nav><div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div></aside>
<div class="main-content">
<header class="topbar"><span class="page-title">Mes offres de stage</span>
<div class="user-info"><div class="avatar orange"><?= userInitials() ?></div><span><?= userName() ?></span></div></header>
<main class="page-body"><?= flashMessage() ?>

<?php if (!$entId): ?>
  <div class="alert alert-warning">Vous devez d'abord renseigner votre entreprise. <a href="entreprise.php">Compléter mon profil</a></div>
<?php else: ?>

<!-- Nouvelle offre -->
<div class="card mb2">
  <div class="card-header"><span class="card-title">Publier une nouvelle offre</span></div>
  <form method="POST" action="../actions/publier_offre.php">
    <div class="form-group"><label class="form-label">Titre de l'offre</label>
      <input class="form-control" type="text" name="titre" placeholder="ex : Développeur Backend Java" required/></div>
    <div class="form-group"><label class="form-label">Description</label>
      <textarea class="form-control" name="description" rows="4" placeholder="Missions, technologies, profil recherché…" required></textarea></div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Durée</label>
        <select class="form-control" name="duree" required>
          <option value="">-- Choisir --</option>
          <option>3 mois</option><option>4 mois</option><option>6 mois</option>
        </select></div>
      <div class="form-group"><label class="form-label">Date de publication</label>
        <input class="form-control" type="date" name="date_publication" value="<?= date('Y-m-d') ?>" required/></div>
    </div>
    <button type="submit" class="btn btn-primary"> Publier l'offre</button>
  </form>
</div>

<!-- Liste des offres -->
<div class="card">
  <div class="card-header"><span class="card-title">Offres publiées (<?= count($offres) ?>)</span></div>
  <div class="table-wrapper"><table>
    <thead><tr><th>Titre</th><th>Durée</th><th>Publiée le</th><th>Candidatures</th><th>Statut</th><th>Action</th></tr></thead>
    <tbody>
      <?php foreach ($offres as $o): ?>
      <tr>
        <td><strong><?= htmlspecialchars($o['titre']) ?></strong></td>
        <td><?= htmlspecialchars($o['duree']) ?></td>
        <td><?= $o['date_publication'] ? date('d/m/Y',strtotime($o['date_publication'])) : '—' ?></td>
        <td><?= $o['nb_cand'] ?></td>
        <td><span class="badge <?= $statMap[$o['statut']]??'badge-gray' ?>"><?= ucfirst($o['statut']) ?></span></td>
        <td>
          <?php if ($o['statut'] === 'ouverte'): ?>
          <form method="POST" action="../actions/fermer_offre.php" style="display:inline;">
            <input type="hidden" name="num_offre" value="<?= $o['num_offre'] ?>"/>
            <button class="btn btn-danger btn-sm">Fermer</button>
          </form>
          <?php else: ?>
          <span style="color:var(--muted);font-size:.82rem;">Archivée</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($offres)): ?><tr><td colspan="6" style="text-align:center;color:var(--muted);">Aucune offre publiée.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
<?php endif; ?>
</main></div></div></body></html>

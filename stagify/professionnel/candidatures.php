<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('maitre_stage');
$pdo = getDB(); $msId = $_SESSION['profil_id'];

$candidatures = $pdo->prepare("
    SELECT p.*, o.titre, o.duree, et.nom, et.prenom, et.TD, et.TP, et.email AS etu_email
    FROM postulation p
    JOIN offre_stage o ON p.num_offre = o.num_offre
    JOIN etudiant et ON p.id_etudiant = et.id_etudiant
    WHERE o.id_maitre = ?
    ORDER BY FIELD(p.statut,'en_attente','acceptee','refusee'), p.date_postulation DESC
");
$candidatures->execute([$msId]);
$rows = $candidatures->fetchAll();

$offres = $pdo->prepare("SELECT num_offre, titre FROM offre_stage WHERE id_maitre=? AND statut='ouverte'");
$offres->execute([$msId]);
$offresListe = $offres->fetchAll();

$bc = ['en_attente'=>'badge-yellow','acceptee'=>'badge-green','refusee'=>'badge-red'];
?>
<!DOCTYPE html><html lang="fr">
<head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Stagify — Candidatures reçues</title><link rel="stylesheet" href="../css/style.css"/></head>
<body><div class="app-layout">
<aside class="sidebar"><div class="brand">Stagify<br><span class="role-badge">Professionnel</span></div><nav>
<a href="dashboard.php"> Tableau de bord</a>
<a href="entreprise.php"> Mon entreprise</a>
<a href="offres.php"> Mes offres de stage</a>
<a href="candidatures.php" class="active"> Candidatures reçues</a>
<a href="stagiaires.php"> Mes stagiaires</a>
</nav><div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div></aside>
<div class="main-content">
<header class="topbar"><span class="page-title">Candidatures reçues</span>
<div class="user-info"><div class="avatar orange"><?= userInitials() ?></div><span><?= userName() ?></span></div></header>
<main class="page-body"><?= flashMessage() ?>

<form method="GET" style="margin-bottom:1.25rem;max-width:320px;">
  <label class="form-label">Filtrer par offre</label>
  <select class="form-control" name="offre" onchange="this.form.submit()">
    <option value="">Toutes les offres</option>
    <?php foreach ($offresListe as $o): ?>
    <option value="<?= $o['num_offre'] ?>" <?= ($_GET['offre']??'')==$o['num_offre']?'selected':'' ?>><?= htmlspecialchars($o['titre']) ?></option>
    <?php endforeach; ?>
  </select>
</form>

<div class="card">
  <div class="table-wrapper"><table>
    <thead><tr><th>Étudiant</th><th>Email</th><th>TD/TP</th><th>Offre</th><th>Date</th><th>Statut</th><th>Actions</th></tr></thead>
    <tbody>
      <?php
      $filtreOffre = (int)($_GET['offre'] ?? 0);
      foreach ($rows as $c):
        if ($filtreOffre && $c['num_offre'] != $filtreOffre) continue;
      ?>
      <tr>
        <td><strong><?= htmlspecialchars($c['prenom'].' '.$c['nom']) ?></strong></td>
        <td><?= htmlspecialchars($c['etu_email']) ?></td>
        <td><?= htmlspecialchars($c['TD'].'/'.$c['TP']) ?></td>
        <td><?= htmlspecialchars($c['titre']) ?></td>
        <td><?= date('d/m/Y',strtotime($c['date_postulation'])) ?></td>
        <td><span class="badge <?= $bc[$c['statut']] ?>"><?= ucfirst(str_replace('_',' ',$c['statut'])) ?></span></td>
        <td>
          <?php if ($c['statut'] === 'en_attente'): ?>
          <form method="POST" action="../actions/decider_candidature.php" style="display:inline;">
            <input type="hidden" name="id_etudiant" value="<?= $c['id_etudiant'] ?>"/>
            <input type="hidden" name="num_offre"   value="<?= $c['num_offre'] ?>"/>
            <input type="hidden" name="statut"      value="acceptee"/>
            <button class="btn btn-success btn-sm">Accepter</button>
          </form>
          <form method="POST" action="../actions/decider_candidature.php" style="display:inline;margin-left:.4rem;">
            <input type="hidden" name="id_etudiant" value="<?= $c['id_etudiant'] ?>"/>
            <input type="hidden" name="num_offre"   value="<?= $c['num_offre'] ?>"/>
            <input type="hidden" name="statut"      value="refusee"/>
            <button class="btn btn-danger btn-sm">Refuser</button>
          </form>
          <?php else: ?>
          <span style="color:var(--muted);font-size:.82rem;">Décision prise</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($rows)): ?><tr><td colspan="7" style="text-align:center;color:var(--muted);">Aucune candidature reçue.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
</main></div></div></body></html>

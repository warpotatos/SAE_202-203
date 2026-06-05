<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('etudiant');

$pdo = getDB();
$etudiantId = $_SESSION['profil_id'];

$stmt = $pdo->prepare('
    SELECT p.*, o.titre, o.duree, e.nom AS entreprise_nom, e.ville
    FROM postulation p
    JOIN offre_stage o ON p.num_offre = o.num_offre
    JOIN entreprise e ON o.id_entreprise = e.id_entreprise
    WHERE p.id_etudiant = ?
    ORDER BY p.date_postulation DESC
');
$stmt->execute([$etudiantId]);
$candidatures = $stmt->fetchAll();

$badgeMap = [
    'en_attente' => '<span class="badge badge-yellow">En attente</span>',
    'acceptee'   => '<span class="badge badge-green">Acceptée</span>',
    'refusee'    => '<span class="badge badge-red">Refusée</span>',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Stagify — Mes candidatures</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="brand">Stagify<br><span class="role-badge">Étudiant</span></div>
    <nav>
      <a href="dashboard.php"> Tableau de bord</a>
      <a href="offres.php"> Offres de stage</a>
      <a href="candidatures.php" class="active"> Mes candidatures</a>
      <a href="stage.php"> Mon stage</a>
      <a href="problemes.php"> Signaler un problème</a>
      <a href="soutenance.php"> Ma soutenance</a>
    </nav>
    <div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div>
  </aside>
  <div class="main-content">
    <header class="topbar">
      <span class="page-title">Mes candidatures</span>
      <div class="user-info"><div class="avatar"><?= userInitials() ?></div><span><?= userName() ?></span></div>
    </header>
    <main class="page-body">
      <?= flashMessage() ?>
      <div class="card">
        <div class="table-wrapper">
          <table>
            <thead><tr><th>Offre</th><th>Entreprise</th><th>Ville</th><th>Durée</th><th>Date</th><th>Statut</th></tr></thead>
            <tbody>
              <?php foreach ($candidatures as $c): ?>
              <tr>
                <td><strong><?= htmlspecialchars($c['titre']) ?></strong></td>
                <td><?= htmlspecialchars($c['entreprise_nom']) ?></td>
                <td><?= htmlspecialchars($c['ville']) ?></td>
                <td><?= htmlspecialchars($c['duree']) ?></td>
                <td><?= date('d/m/Y', strtotime($c['date_postulation'])) ?></td>
                <td><?= $badgeMap[$c['statut']] ?? $c['statut'] ?></td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($candidatures)): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--muted);">Vous n'avez encore postulé à aucune offre.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>
</body></html>

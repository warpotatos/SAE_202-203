<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('etudiant');

$pdo = getDB();
$etudiantId = $_SESSION['profil_id'];

// Récupérer le stage actif
$stageStmt = $pdo->prepare('
    SELECT s.*, e.nom AS entreprise_nom, ms.nom AS maitre_nom, ms.prenom AS maitre_prenom,
           ens.nom AS ens_nom, ens.prenom AS ens_prenom
    FROM stage s
    JOIN entreprise e ON s.id_entreprise = e.id_entreprise
    LEFT JOIN maitre_stage ms ON s.id_maitre = ms.id_maitre
    LEFT JOIN etudiant et ON s.id_etudiant = et.id_etudiant
    LEFT JOIN enseignant ens ON et.num_enseignant_ref = ens.num_enseignant
    WHERE s.id_etudiant = ?
    ORDER BY s.date_debut DESC LIMIT 1
');
$stageStmt->execute([$etudiantId]);
$stage = $stageStmt->fetch();

// Nb candidatures
$nbCandidatures = $pdo->prepare('SELECT COUNT(*) FROM postulation WHERE id_etudiant = ?');
$nbCandidatures->execute([$etudiantId]);
$nbCand = $nbCandidatures->fetchColumn();

// Nb offres ouvertes
$nbOffres = $pdo->query("SELECT COUNT(*) FROM offre_stage WHERE statut = 'ouverte'")->fetchColumn();

// Offres récentes
$offres = $pdo->query("
    SELECT o.*, e.nom AS entreprise_nom, e.ville
    FROM offre_stage o
    JOIN entreprise e ON o.id_entreprise = e.id_entreprise
    WHERE o.statut = 'ouverte'
    ORDER BY o.date_publication DESC LIMIT 3
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Stagify — Tableau de bord étudiant</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="brand">Stagify<br><span class="role-badge">Étudiant</span></div>
    <nav>
      <a href="dashboard.php" class="active"> Tableau de bord</a>
      <a href="offres.php"> Offres de stage</a>
      <a href="candidatures.php"> Mes candidatures</a>
      <a href="stage.php"> Mon stage</a>
      <a href="problemes.php"> Signaler un problème</a>
      <a href="soutenance.php"> Ma soutenance</a>
    </nav>
    <div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div>
  </aside>
  <div class="main-content">
    <header class="topbar">
      <span class="page-title">Tableau de bord</span>
      <div class="user-info">
        <div class="avatar"><?= userInitials() ?></div>
        <span><?= userName() ?> — Étudiant</span>
      </div>
    </header>
    <main class="page-body">
      <?= flashMessage() ?>
      <div class="stats-row">
        <div class="stat-card"><div class="stat-label">Offres disponibles</div><div class="stat-value"><?= $nbOffres ?></div></div>
        <div class="stat-card"><div class="stat-label">Mes candidatures</div><div class="stat-value"><?= $nbCand ?></div></div>
        <div class="stat-card">
          <div class="stat-label">Statut stage</div>
          <div class="stat-value" style="font-size:.95rem;padding-top:.5rem;">
            <?php if ($stage): ?>
              <?php
                $now = date('Y-m-d');
                if ($stage['date_fin'] < $now) $badge = '<span class="badge badge-green">Terminé</span>';
                elseif ($stage['date_debut'] <= $now) $badge = '<span class="badge badge-blue">En cours</span>';
                else $badge = '<span class="badge badge-orange">À venir</span>';
                echo $badge;
              ?>
            <?php else: ?>
              <span class="badge badge-gray">Aucun stage</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <h2 class="section-title">Dernières offres publiées</h2>
      <div class="card-grid">
        <?php foreach ($offres as $o): ?>
        <div class="card">
          <div class="card-header">
            <span class="card-title"><?= htmlspecialchars($o['titre']) ?></span>
            <span class="badge badge-green">Ouverte</span>
          </div>
          <p style="font-size:.85rem;color:var(--muted);margin-bottom:.5rem;">
            <?= htmlspecialchars($o['entreprise_nom']) ?> · <?= htmlspecialchars($o['ville']) ?> · <?= htmlspecialchars($o['duree']) ?>
          </p>
          <p style="font-size:.875rem;margin-bottom:1rem;"><?= htmlspecialchars(mb_substr($o['description'], 0, 100)) ?>…</p>
          <a href="offres.php" class="btn btn-outline btn-sm">Voir &amp; postuler</a>
        </div>
        <?php endforeach; ?>
        <?php if (empty($offres)): ?>
          <div class="alert alert-info">Aucune offre disponible pour le moment.</div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</div>
</body></html>

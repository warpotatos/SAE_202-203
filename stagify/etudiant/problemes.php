<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('etudiant');

$pdo = getDB();
$etudiantId = $_SESSION['profil_id'];

// Récupérer le stage actif
$stmt = $pdo->prepare('SELECT num_stage FROM stage WHERE id_etudiant = ? ORDER BY date_debut DESC LIMIT 1');
$stmt->execute([$etudiantId]);
$stage = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Stagify — Signaler un problème</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="brand">Stagify<br><span class="role-badge">Étudiant</span></div>
    <nav>
      <a href="dashboard.php"> Tableau de bord</a>
      <a href="offres.php"> Offres de stage</a>
      <a href="candidatures.php"> Mes candidatures</a>
      <a href="stage.php"> Mon stage</a>
      <a href="problemes.php" class="active"> Signaler un problème</a>
      <a href="soutenance.php"> Ma soutenance</a>
    </nav>
    <div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div>
  </aside>
  <div class="main-content">
    <header class="topbar">
      <span class="page-title">Signaler un problème</span>
      <div class="user-info"><div class="avatar"><?= userInitials() ?></div><span><?= userName() ?></span></div>
    </header>
    <main class="page-body">
      <?= flashMessage() ?>
      <?php if (!$stage): ?>
        <div class="alert alert-warning">Vous devez avoir un stage assigné pour signaler un problème. <a href="offres.php">Voir les offres.</a></div>
      <?php else: ?>
        <div class="card" style="max-width:580px;">
          <div class="card-header"><span class="card-title">Nouveau signalement</span></div>
          <div class="alert alert-warning">Votre enseignant référent sera notifié de ce signalement.</div>
          <form method="POST" action="../actions/signaler_probleme.php">
            <input type="hidden" name="num_stage" value="<?= $stage['num_stage'] ?>" />
            <div class="form-group">
              <label class="form-label">Date de l'incident</label>
              <input class="form-control" type="date" name="date_incident" max="<?= date('Y-m-d') ?>" required />
            </div>
            <div class="form-group">
              <label class="form-label">Description du problème</label>
              <textarea class="form-control" name="description" rows="5" placeholder="Décrivez précisément le problème rencontré…" required></textarea>
            </div>
            <button type="submit" class="btn btn-danger">Envoyer le signalement</button>
          </form>
        </div>
      <?php endif; ?>
    </main>
  </div>
</div>
</body></html>

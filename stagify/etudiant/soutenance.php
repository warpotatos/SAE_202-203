<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('etudiant');

$pdo = getDB();
$etudiantId = $_SESSION['profil_id'];

$soutenance = $pdo->prepare('
    SELECT sout.*, j.membres_jury
    FROM soutenance sout
    JOIN stage s ON sout.num_stage = s.num_stage
    LEFT JOIN jury j ON sout.num_jury = j.num_jury
    WHERE s.id_etudiant = ?
    ORDER BY sout.date DESC LIMIT 1
');
$soutenance->execute([$etudiantId]);
$sout = $soutenance->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Stagify — Ma soutenance</title>
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
      <a href="problemes.php"> Signaler un problème</a>
      <a href="soutenance.php" class="active"> Ma soutenance</a>
    </nav>
    <div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div>
  </aside>
  <div class="main-content">
    <header class="topbar">
      <span class="page-title">Ma soutenance</span>
      <div class="user-info"><div class="avatar"><?= userInitials() ?></div><span><?= userName() ?></span></div>
    </header>
    <main class="page-body">
      <?php if (!$sout): ?>
        <div class="alert alert-info">Aucune soutenance planifiée pour l'instant. Votre enseignant vous communiquera la date.</div>
      <?php else: ?>
        <div class="card" style="max-width:560px;">
          <div class="card-header">
            <span class="card-title">Détails de la soutenance</span>
            <?= $sout['note_oral'] ? '<span class="badge badge-green">Notée</span>' : '<span class="badge badge-blue">Programmée</span>' ?>
          </div>
          <div style="display:grid;gap:.75rem;font-size:.9rem;margin-bottom:1.25rem;">
            <div><strong>Date :</strong> <?= $sout['date'] ? date('d/m/Y', strtotime($sout['date'])) : '—' ?></div>
            <div><strong>Heure :</strong> <?= $sout['heures'] ? substr($sout['heures'],0,5) : '—' ?></div>
            <div><strong>Jury :</strong> <?= htmlspecialchars($sout['membres_jury'] ?? '—') ?></div>
          </div>
          <hr>
          <div class="stats-row" style="margin:1.25rem 0 0;">
            <div class="stat-card">
              <div class="stat-label">Note rapport</div>
              <div class="stat-value"><?= $sout['note_rapport'] !== null ? $sout['note_rapport'].'/20' : '—' ?></div>
            </div>
            <div class="stat-card">
              <div class="stat-label">Note oral</div>
              <div class="stat-value"><?= $sout['note_oral'] !== null ? $sout['note_oral'].'/20' : '—' ?></div>
            </div>
          </div>
          <?php if ($sout['note_oral'] === null): ?>
            <div class="alert alert-info mt2">Les notes seront disponibles après votre soutenance.</div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </main>
  </div>
</div>
</body></html>

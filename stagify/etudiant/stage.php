<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('etudiant');

$pdo = getDB();
$etudiantId = $_SESSION['profil_id'];

$stage = $pdo->prepare('
    SELECT s.*, e.nom AS ent_nom, e.adresse, e.ville, e.telephone AS ent_tel,
           ms.nom AS ms_nom, ms.prenom AS ms_prenom, ms.email AS ms_email,
           ens.nom AS ens_nom, ens.prenom AS ens_prenom
    FROM stage s
    JOIN entreprise e ON s.id_entreprise = e.id_entreprise
    LEFT JOIN maitre_stage ms ON s.id_maitre = ms.id_maitre
    LEFT JOIN etudiant et ON s.id_etudiant = et.id_etudiant
    LEFT JOIN enseignant ens ON et.num_enseignant_ref = ens.num_enseignant
    WHERE s.id_etudiant = ?
    ORDER BY s.date_debut DESC LIMIT 1
');
$stage->execute([$etudiantId]);
$stage = $stage->fetch();

$visites = [];
$problemes = [];
if ($stage) {
    $v = $pdo->prepare('SELECT vs.*, ens.nom, ens.prenom FROM visite_suivi vs LEFT JOIN enseignant ens ON vs.num_enseignant = ens.num_enseignant WHERE vs.num_stage = ? ORDER BY vs.date_visite');
    $v->execute([$stage['num_stage']]);
    $visites = $v->fetchAll();

    $p = $pdo->prepare('SELECT * FROM probleme WHERE num_stage = ? ORDER BY date_signalement DESC');
    $p->execute([$stage['num_stage']]);
    $problemes = $p->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Stagify — Mon stage</title>
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
      <a href="stage.php" class="active"> Mon stage</a>
      <a href="problemes.php"> Signaler un problème</a>
      <a href="soutenance.php"> Ma soutenance</a>
    </nav>
    <div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div>
  </aside>
  <div class="main-content">
    <header class="topbar">
      <span class="page-title">Mon stage</span>
      <div class="user-info"><div class="avatar"><?= userInitials() ?></div><span><?= userName() ?></span></div>
    </header>
    <main class="page-body">
      <?= flashMessage() ?>
      <?php if (!$stage): ?>
        <div class="alert alert-info">Vous n'avez pas encore de stage assigné. <a href="offres.php">Consultez les offres disponibles.</a></div>
      <?php else: ?>
        <div class="card mb2">
          <div class="card-header">
            <span class="card-title">Informations du stage</span>
            <?php
              $now = date('Y-m-d');
              if ($stage['date_fin'] < $now) echo '<span class="badge badge-green">Terminé</span>';
              elseif ($stage['date_debut'] <= $now) echo '<span class="badge badge-blue">En cours</span>';
              else echo '<span class="badge badge-orange">À venir</span>';
            ?>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.9rem;font-size:.9rem;">
            <div><strong>Entreprise :</strong> <?= htmlspecialchars($stage['ent_nom']) ?></div>
            <div><strong>Ville :</strong> <?= htmlspecialchars($stage['ville']) ?></div>
            <div><strong>Début :</strong> <?= $stage['date_debut'] ? date('d/m/Y', strtotime($stage['date_debut'])) : '—' ?></div>
            <div><strong>Fin :</strong> <?= $stage['date_fin'] ? date('d/m/Y', strtotime($stage['date_fin'])) : '—' ?></div>
            <div><strong>Durée :</strong> <?= htmlspecialchars($stage['duree'] ?? '—') ?></div>
            <div><strong>Horaires :</strong> <?= htmlspecialchars($stage['horaires'] ?? '—') ?></div>
            <?php if ($stage['ms_nom']): ?>
            <div><strong>Maître de stage :</strong> <?= htmlspecialchars($stage['ms_prenom'] . ' ' . $stage['ms_nom']) ?></div>
            <?php endif; ?>
            <?php if ($stage['ens_nom']): ?>
            <div><strong>Enseignant référent :</strong> <?= htmlspecialchars($stage['ens_prenom'] . ' ' . $stage['ens_nom']) ?></div>
            <?php endif; ?>
            <?php if ($stage['contenu_du_stage']): ?>
            <div style="grid-column:1/-1;"><strong>Contenu :</strong> <?= htmlspecialchars($stage['contenu_du_stage']) ?></div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Visites -->
        <div class="card mb2">
          <div class="card-header"><span class="card-title">Visites de suivi</span></div>
          <?php if (empty($visites)): ?>
            <div class="alert alert-info">Aucune visite planifiée pour le moment.</div>
          <?php else: ?>
            <div class="table-wrapper">
              <table>
                <thead><tr><th>Date</th><th>Heure</th><th>Lieu</th><th>Enseignant</th><th>Commentaires</th></tr></thead>
                <tbody>
                  <?php foreach ($visites as $v): ?>
                  <tr>
                    <td><?= date('d/m/Y', strtotime($v['date_visite'])) ?></td>
                    <td><?= $v['heure_visite'] ? substr($v['heure_visite'],0,5) : '—' ?></td>
                    <td><?= htmlspecialchars($v['lieu'] ?? '—') ?></td>
                    <td><?= $v['nom'] ? htmlspecialchars($v['prenom'].' '.$v['nom']) : '—' ?></td>
                    <td><?= htmlspecialchars($v['commentaires'] ?? '—') ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <!-- Problèmes -->
        <div class="card">
          <div class="card-header">
            <span class="card-title">Problèmes signalés</span>
            <a href="problemes.php" class="btn btn-danger btn-sm">+ Signaler</a>
          </div>
          <?php if (empty($problemes)): ?>
            <div class="alert alert-info">Aucun problème signalé.</div>
          <?php else: ?>
            <div class="table-wrapper">
              <table>
                <thead><tr><th>Date incident</th><th>Description</th><th>Statut</th></tr></thead>
                <tbody>
                  <?php foreach ($problemes as $p): ?>
                  <tr>
                    <td><?= date('d/m/Y', strtotime($p['date_incident'])) ?></td>
                    <td><?= htmlspecialchars(mb_substr($p['description'],0,80)) ?>…</td>
                    <td>
                      <?php $bc = ['ouvert'=>'badge-red','en_cours'=>'badge-yellow','resolu'=>'badge-green']; ?>
                      <span class="badge <?= $bc[$p['statut']] ?? 'badge-gray' ?>"><?= ucfirst(str_replace('_',' ',$p['statut'])) ?></span>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </main>
  </div>
</div>
</body></html>

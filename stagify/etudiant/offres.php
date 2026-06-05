<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('etudiant');

$pdo = getDB();
$etudiantId = $_SESSION['profil_id'];

// Filtres
$ville  = trim($_GET['ville']  ?? '');
$duree  = trim($_GET['duree']  ?? '');

$where = ["o.statut = 'ouverte'"];
$params = [];
if ($ville) { $where[] = 'e.ville LIKE ?'; $params[] = "%$ville%"; }
if ($duree) { $where[] = 'o.duree = ?';    $params[] = $duree; }

$sql = '
    SELECT o.*, e.nom AS entreprise_nom, e.ville,
           p.statut AS ma_candidature
    FROM offre_stage o
    JOIN entreprise e ON o.id_entreprise = e.id_entreprise
    LEFT JOIN postulation p ON p.num_offre = o.num_offre AND p.id_etudiant = ?
    WHERE ' . implode(' AND ', $where) . '
    ORDER BY o.date_publication DESC
';
array_unshift($params, $etudiantId);
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$offres = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Stagify — Offres de stage</title>
  <link rel="stylesheet" href="../css/style.css" />
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="brand">Stagify<br><span class="role-badge">Étudiant</span></div>
    <nav>
      <a href="dashboard.php"> Tableau de bord</a>
      <a href="offres.php" class="active"> Offres de stage</a>
      <a href="candidatures.php"> Mes candidatures</a>
      <a href="stage.php"> Mon stage</a>
      <a href="problemes.php"> Signaler un problème</a>
      <a href="soutenance.php"> Ma soutenance</a>
    </nav>
    <div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div>
  </aside>
  <div class="main-content">
    <header class="topbar">
      <span class="page-title">Offres de stage disponibles</span>
      <div class="user-info"><div class="avatar"><?= userInitials() ?></div><span><?= userName() ?></span></div>
    </header>
    <main class="page-body">
      <?= flashMessage() ?>
      <!-- Filtres -->
      <form method="GET" class="card mb2">
        <div style="display:flex;gap:1rem;flex-wrap:wrap;align-items:flex-end;">
          <div class="form-group" style="margin:0;flex:1;min-width:140px;">
            <label class="form-label">Ville</label>
            <input class="form-control" type="text" name="ville" value="<?= htmlspecialchars($ville) ?>" placeholder="Paris, Lyon…" />
          </div>
          <div class="form-group" style="margin:0;flex:1;min-width:130px;">
            <label class="form-label">Durée</label>
            <select class="form-control" name="duree">
              <option value="">Toutes</option>
              <?php foreach (['3 mois','4 mois','6 mois'] as $d): ?>
                <option <?= $duree === $d ? 'selected' : '' ?>><?= $d ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary">Filtrer</button>
          <a href="offres.php" class="btn btn-outline">Réinitialiser</a>
        </div>
      </form>

      <div class="card">
        <div class="table-wrapper">
          <table>
            <thead><tr><th>Titre</th><th>Entreprise</th><th>Ville</th><th>Durée</th><th>Publiée le</th><th>Action</th></tr></thead>
            <tbody>
              <?php foreach ($offres as $o): ?>
              <tr>
                <td><strong><?= htmlspecialchars($o['titre']) ?></strong></td>
                <td><?= htmlspecialchars($o['entreprise_nom']) ?></td>
                <td><?= htmlspecialchars($o['ville']) ?></td>
                <td><?= htmlspecialchars($o['duree']) ?></td>
                <td><?= $o['date_publication'] ? date('d/m/Y', strtotime($o['date_publication'])) : '—' ?></td>
                <td>
                  <?php if ($o['ma_candidature'] === 'en_attente'): ?>
                    <span class="badge badge-yellow">Candidature envoyée</span>
                  <?php elseif ($o['ma_candidature'] === 'acceptee'): ?>
                    <span class="badge badge-green">Acceptée ✓</span>
                  <?php elseif ($o['ma_candidature'] === 'refusee'): ?>
                    <span class="badge badge-red">Refusée</span>
                  <?php else: ?>
                    <form method="POST" action="../actions/postuler.php">
                      <input type="hidden" name="num_offre" value="<?= $o['num_offre'] ?>" />
                      <button type="submit" class="btn btn-primary btn-sm">Postuler</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($offres)): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--muted);">Aucune offre ne correspond à vos critères.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</div>
</body></html>

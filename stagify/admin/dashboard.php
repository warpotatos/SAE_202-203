<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('admin');
$pdo = getDB();
$stats = [
    'Utilisateurs'   => $pdo->query('SELECT COUNT(*) FROM utilisateur')->fetchColumn(),
    'Étudiants'      => $pdo->query('SELECT COUNT(*) FROM etudiant')->fetchColumn(),
    'Enseignants'    => $pdo->query('SELECT COUNT(*) FROM enseignant')->fetchColumn(),
    'Maîtres stage'  => $pdo->query('SELECT COUNT(*) FROM maitre_stage')->fetchColumn(),
    'Entreprises'    => $pdo->query('SELECT COUNT(*) FROM entreprise')->fetchColumn(),
    'Offres ouvertes'=> $pdo->query("SELECT COUNT(*) FROM offre_stage WHERE statut='ouverte'")->fetchColumn(),
    'Stages actifs'  => $pdo->query("SELECT COUNT(*) FROM stage WHERE date_debut <= CURDATE() AND date_fin >= CURDATE()")->fetchColumn(),
    'Problèmes ouverts' => $pdo->query("SELECT COUNT(*) FROM probleme WHERE statut='ouvert'")->fetchColumn(),
];
$derniers = $pdo->query("SELECT u.*, COALESCE(et.nom, ens.nom, ms.nom) AS nom, COALESCE(et.prenom, ens.prenom, ms.prenom) AS prenom FROM utilisateur u LEFT JOIN etudiant et ON et.num_utilisateur=u.num_utilisateur LEFT JOIN enseignant ens ON ens.num_utilisateur=u.num_utilisateur LEFT JOIN maitre_stage ms ON ms.num_utilisateur=u.num_utilisateur ORDER BY u.date_creation DESC LIMIT 10")->fetchAll();
$roleLabel = ['etudiant'=>'Étudiant','enseignant'=>'Enseignant','maitre_stage'=>'Professionnel','admin'=>'Admin'];
$roleBadge = ['etudiant'=>'badge-blue','enseignant'=>'badge-green','maitre_stage'=>'badge-orange','admin'=>'badge-red'];
?>
<!DOCTYPE html><html lang="fr">
<head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Stagify — Admin</title><link rel="stylesheet" href="../css/style.css"/></head>
<body><div class="app-layout">
<aside class="sidebar"><div class="brand">Stagify<br><span class="role-badge">Administrateur</span></div><nav>
<a href="dashboard.php" class="active"> Tableau de bord</a>
<a href="utilisateurs.php"> Utilisateurs</a>
<a href="stages.php"> Tous les stages</a>
<a href="problemes.php"> Tous les problèmes</a>
</nav><div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div></aside>
<div class="main-content">
<header class="topbar"><span class="page-title">Tableau de bord admin</span>
<div class="user-info"><div class="avatar"><?= userInitials() ?></div><span><?= userName() ?></span></div></header>
<main class="page-body"><?= flashMessage() ?>
<div class="stats-row">
  <?php foreach ($stats as $label => $val): ?>
  <div class="stat-card"><div class="stat-label"><?= $label ?></div><div class="stat-value"><?= $val ?></div></div>
  <?php endforeach; ?>
</div>
<div class="card">
  <div class="card-header"><span class="card-title">Derniers comptes créés</span><a href="utilisateurs.php" class="btn btn-outline btn-sm">Voir tout</a></div>
  <div class="table-wrapper"><table>
    <thead><tr><th>Login</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Créé le</th></tr></thead>
    <tbody>
      <?php foreach ($derniers as $u): ?>
      <tr>
        <td><?= htmlspecialchars($u['login']) ?></td>
        <td><?= htmlspecialchars(($u['prenom']??'').' '.($u['nom']??'')) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><span class="badge <?= $roleBadge[$u['role']]??'badge-gray' ?>"><?= $roleLabel[$u['role']]??$u['role'] ?></span></td>
        <td><?= date('d/m/Y H:i', strtotime($u['date_creation'])) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
</main></div></div></body></html>

<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('admin');
$pdo = getDB();
$users = $pdo->query("SELECT u.*, COALESCE(et.nom, ens.nom, ms.nom, a.nom) AS nom, COALESCE(et.prenom, ens.prenom, ms.prenom, a.prenom) AS prenom FROM utilisateur u LEFT JOIN etudiant et ON et.num_utilisateur=u.num_utilisateur LEFT JOIN enseignant ens ON ens.num_utilisateur=u.num_utilisateur LEFT JOIN maitre_stage ms ON ms.num_utilisateur=u.num_utilisateur LEFT JOIN admin a ON a.num_utilisateur=u.num_utilisateur ORDER BY u.date_creation DESC")->fetchAll();
$roleLabel = ['etudiant'=>'Étudiant','enseignant'=>'Enseignant','maitre_stage'=>'Professionnel','admin'=>'Admin'];
$roleBadge = ['etudiant'=>'badge-blue','enseignant'=>'badge-green','maitre_stage'=>'badge-orange','admin'=>'badge-red'];
?>
<!DOCTYPE html><html lang="fr">
<head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Stagify — Admin · Utilisateurs</title><link rel="stylesheet" href="../css/style.css"/></head>
<body><div class="app-layout">
<aside class="sidebar"><div class="brand">Stagify<br><span class="role-badge">Administrateur</span></div><nav>
<a href="dashboard.php"> Tableau de bord</a>
<a href="utilisateurs.php" class="active"> Utilisateurs</a>
<a href="stages.php"> Tous les stages</a>
<a href="problemes.php"> Tous les problèmes</a>
</nav><div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div></aside>
<div class="main-content">
<header class="topbar"><span class="page-title">Gestion des utilisateurs</span>
<div class="user-info"><div class="avatar"><?= userInitials() ?></div><span><?= userName() ?></span></div></header>
<main class="page-body"><?= flashMessage() ?>
<div class="card">
  <div class="table-wrapper"><table>
    <thead><tr><th>#</th><th>Login</th><th>Nom complet</th><th>Email</th><th>Rôle</th><th>Créé le</th><th>Action</th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td><?= $u['num_utilisateur'] ?></td>
        <td><?= htmlspecialchars($u['login']) ?></td>
        <td><?= htmlspecialchars(trim(($u['prenom']??'').' '.($u['nom']??''))) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><span class="badge <?= $roleBadge[$u['role']]??'badge-gray' ?>"><?= $roleLabel[$u['role']]??$u['role'] ?></span></td>
        <td><?= date('d/m/Y', strtotime($u['date_creation'])) ?></td>
        <td>
          <?php if ($u['role'] !== 'admin'): ?>
          <form method="POST" action="../actions/supprimer_utilisateur.php" onsubmit="return confirm('Supprimer cet utilisateur ?');" style="display:inline;">
            <input type="hidden" name="num_utilisateur" value="<?= $u['num_utilisateur'] ?>"/>
            <button class="btn btn-danger btn-sm">Supprimer</button>
          </form>
          <?php else: ?>
          <span style="font-size:.8rem;color:var(--muted);">Admin</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
</div>
</main></div></div></body></html>

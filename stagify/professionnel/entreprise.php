<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('maitre_stage');
$pdo = getDB(); $msId = $_SESSION['profil_id'];

$ms = $pdo->prepare('SELECT ms.*, ent.nom AS ent_nom, ent.adresse, ent.ville, ent.telephone AS ent_tel, ent.email AS ent_email, ent.id_entreprise FROM maitre_stage ms LEFT JOIN entreprise ent ON ms.id_entreprise = ent.id_entreprise WHERE ms.id_maitre = ?');
$ms->execute([$msId]);
$data = $ms->fetch();
?>
<!DOCTYPE html><html lang="fr">
<head><meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<title>Stagify — Mon entreprise</title><link rel="stylesheet" href="../css/style.css"/></head>
<body><div class="app-layout">
<aside class="sidebar"><div class="brand">Stagify<br><span class="role-badge">Professionnel</span></div><nav>
<a href="dashboard.php"> Tableau de bord</a>
<a href="entreprise.php" class="active"> Mon entreprise</a>
<a href="offres.php"> Mes offres de stage</a>
<a href="candidatures.php"> Candidatures reçues</a>
<a href="stagiaires.php"> Mes stagiaires</a>
</nav><div class="sidebar-footer"><a href="../logout.php"> Déconnexion</a></div></aside>
<div class="main-content">
<header class="topbar"><span class="page-title">Mon entreprise</span>
<div class="user-info"><div class="avatar orange"><?= userInitials() ?></div><span><?= userName() ?></span></div></header>
<main class="page-body"><?= flashMessage() ?>

<div class="card mb2" style="max-width:600px;">
  <div class="card-header"><span class="card-title">Informations de l'entreprise</span></div>
  <form method="POST" action="../actions/update_entreprise.php">
    <input type="hidden" name="id_entreprise" value="<?= $data['id_entreprise'] ?>"/>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Nom de l'entreprise</label>
        <input class="form-control" type="text" name="nom" value="<?= htmlspecialchars($data['ent_nom']??'') ?>" required/></div>
      <div class="form-group"><label class="form-label">Email entreprise</label>
        <input class="form-control" type="email" name="email" value="<?= htmlspecialchars($data['ent_email']??'') ?>"/></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Adresse</label>
        <input class="form-control" type="text" name="adresse" value="<?= htmlspecialchars($data['adresse']??'') ?>"/></div>
      <div class="form-group"><label class="form-label">Ville</label>
        <input class="form-control" type="text" name="ville" value="<?= htmlspecialchars($data['ville']??'') ?>"/></div>
    </div>
    <div class="form-group"><label class="form-label">Téléphone</label>
      <input class="form-control" type="tel" name="telephone" value="<?= htmlspecialchars($data['ent_tel']??'') ?>"/></div>
    <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
  </form>
</div>

<div class="card" style="max-width:600px;">
  <div class="card-header"><span class="card-title">Mon profil (maître de stage)</span></div>
  <form method="POST" action="../actions/update_maitre.php">
    <div class="form-row">
      <div class="form-group"><label class="form-label">Nom</label>
        <input class="form-control" type="text" name="nom" value="<?= htmlspecialchars($data['nom']) ?>" required/></div>
      <div class="form-group"><label class="form-label">Prénom</label>
        <input class="form-control" type="text" name="prenom" value="<?= htmlspecialchars($data['prenom']) ?>" required/></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">Poste</label>
        <input class="form-control" type="text" name="poste" value="<?= htmlspecialchars($data['poste']??'') ?>"/></div>
      <div class="form-group"><label class="form-label">Téléphone</label>
        <input class="form-control" type="tel" name="telephone" value="<?= htmlspecialchars($data['telephone']??'') ?>"/></div>
    </div>
    <button type="submit" class="btn btn-outline">Mettre à jour mon profil</button>
  </form>
</div>
</main></div></div></body></html>

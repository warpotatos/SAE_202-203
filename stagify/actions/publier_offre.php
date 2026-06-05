<?php
// actions/publier_offre.php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('maitre_stage');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../professionnel/offres.php'); exit; }
$pdo   = getDB(); $msId = $_SESSION['profil_id'];
$ms    = $pdo->prepare('SELECT id_entreprise FROM maitre_stage WHERE id_maitre=?');
$ms->execute([$msId]); $ms = $ms->fetch();
$entId = $ms['id_entreprise'];
if (!$entId) { setFlash('error','Aucune entreprise liée à votre compte.'); header('Location: ../professionnel/offres.php'); exit; }
$titre = trim($_POST['titre'] ?? '');
$desc  = trim($_POST['description'] ?? '');
$duree = trim($_POST['duree'] ?? '');
$date  = trim($_POST['date_publication'] ?? date('Y-m-d'));
if (!$titre || !$desc || !$duree) { setFlash('error','Tous les champs sont requis.'); header('Location: ../professionnel/offres.php'); exit; }
$pdo->prepare('INSERT INTO offre_stage (titre, description, duree, date_publication, statut, id_entreprise, id_maitre) VALUES (?,?,?,?,?,?,?)')
    ->execute([$titre, $desc, $duree, $date, 'ouverte', $entId, $msId]);
setFlash('success','✅ Offre publiée avec succès !');
header('Location: ../professionnel/offres.php'); exit;

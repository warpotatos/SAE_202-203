<?php
// actions/update_maitre.php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('maitre_stage');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../professionnel/entreprise.php'); exit; }
$pdo   = getDB(); $msId = $_SESSION['profil_id'];
$nom   = trim($_POST['nom']       ?? '');
$pren  = trim($_POST['prenom']    ?? '');
$poste = trim($_POST['poste']     ?? '');
$tel   = trim($_POST['telephone'] ?? '');
if (!$nom || !$pren) { setFlash('error','Nom et prénom obligatoires.'); header('Location: ../professionnel/entreprise.php'); exit; }
$pdo->prepare('UPDATE maitre_stage SET nom=?,prenom=?,poste=?,telephone=? WHERE id_maitre=?')->execute([$nom,$pren,$poste,$tel,$msId]);
$_SESSION['nom'] = $nom; $_SESSION['prenom'] = $pren;
setFlash('success','✅ Profil mis à jour.'); header('Location: ../professionnel/entreprise.php'); exit;

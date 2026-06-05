<?php
// actions/fermer_offre.php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('maitre_stage');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../professionnel/offres.php'); exit; }
$pdo = getDB(); $msId = $_SESSION['profil_id'];
$numOffre = (int)($_POST['num_offre'] ?? 0);
if (!$numOffre) { setFlash('error','Offre invalide.'); header('Location: ../professionnel/offres.php'); exit; }
$pdo->prepare("UPDATE offre_stage SET statut='fermee' WHERE num_offre=? AND id_maitre=?")->execute([$numOffre, $msId]);
setFlash('success','Offre fermée.');
header('Location: ../professionnel/offres.php'); exit;

<?php
// actions/update_entreprise.php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('maitre_stage');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../professionnel/entreprise.php'); exit; }
$pdo = getDB();
$entId = (int)($_POST['id_entreprise'] ?? 0);
$nom   = trim($_POST['nom']      ?? '');
$email = trim($_POST['email']    ?? '');
$adr   = trim($_POST['adresse']  ?? '');
$ville = trim($_POST['ville']    ?? '');
$tel   = trim($_POST['telephone'] ?? '');
if (!$nom) { setFlash('error','Le nom est obligatoire.'); header('Location: ../professionnel/entreprise.php'); exit; }
if ($entId) {
    $pdo->prepare('UPDATE entreprise SET nom=?,email=?,adresse=?,ville=?,telephone=? WHERE id_entreprise=?')->execute([$nom,$email,$adr,$ville,$tel,$entId]);
} else {
    $pdo->prepare('INSERT INTO entreprise (nom,email,adresse,ville,telephone) VALUES (?,?,?,?,?)')->execute([$nom,$email,$adr,$ville,$tel]);
    $newId = $pdo->lastInsertId();
    $pdo->prepare('UPDATE maitre_stage SET id_entreprise=? WHERE id_maitre=?')->execute([$newId,$_SESSION['profil_id']]);
}
setFlash('success','✅ Entreprise mise à jour.'); header('Location: ../professionnel/entreprise.php'); exit;

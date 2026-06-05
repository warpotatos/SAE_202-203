<?php
// actions/supprimer_utilisateur.php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('admin');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../admin/utilisateurs.php'); exit; }
$pdo = getDB();
$id  = (int)($_POST['num_utilisateur'] ?? 0);
if (!$id) { setFlash('error','ID invalide.'); header('Location: ../admin/utilisateurs.php'); exit; }
// Refuser de supprimer l'admin connecté
if ($id === (int)$_SESSION['user_id']) { setFlash('error','Vous ne pouvez pas supprimer votre propre compte.'); header('Location: ../admin/utilisateurs.php'); exit; }
$pdo->prepare('DELETE FROM utilisateur WHERE num_utilisateur=?')->execute([$id]);
setFlash('success','Utilisateur supprimé.');
header('Location: ../admin/utilisateurs.php'); exit;

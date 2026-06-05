<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('enseignant');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../enseignant/problemes.php'); exit; }
$pdo = getDB();
$numProb = (int)($_POST['num_prob'] ?? 0);
$statut  = trim($_POST['statut']    ?? '');
if (!$numProb || !in_array($statut, ['en_cours','resolu'])) { setFlash('error','Données invalides.'); header('Location: ../enseignant/problemes.php'); exit; }
$pdo->prepare('UPDATE probleme SET statut = ? WHERE num_prob = ?')->execute([$statut, $numProb]);
setFlash('success','✅ Statut du problème mis à jour.');
header('Location: ../enseignant/problemes.php'); exit;

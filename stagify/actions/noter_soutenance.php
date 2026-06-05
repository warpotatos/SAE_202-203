<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('enseignant');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../enseignant/jury.php'); exit; }
$pdo = getDB();
$numSout = (int)($_POST['num_soutenance'] ?? 0);
$champ   = trim($_POST['champ']          ?? '');
$valeur  = (float)($_POST['valeur']      ?? 0);
if (!$numSout || !in_array($champ, ['note_rapport','note_oral']) || $valeur < 0 || $valeur > 20) { setFlash('error','Données invalides.'); header('Location: ../enseignant/jury.php'); exit; }
$pdo->prepare("UPDATE soutenance SET {$champ} = ? WHERE num_soutenance = ?")->execute([$valeur, $numSout]);
setFlash('success','✅ Note enregistrée.');
header('Location: ../enseignant/jury.php'); exit;

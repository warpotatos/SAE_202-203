<?php
// actions/noter_stage.php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('enseignant');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../enseignant/notes.php'); exit; }
$pdo = getDB(); $ensId = $_SESSION['profil_id'];
$numStage = (int)($_POST['num_stage'] ?? 0);
$note     = (float)($_POST['note'] ?? 0);
$date     = trim($_POST['date_notation'] ?? date('Y-m-d'));
$comment  = trim($_POST['commentaire'] ?? '');
if (!$numStage || $note < 0 || $note > 20) { setFlash('error','Données invalides.'); header('Location: ../enseignant/notes.php'); exit; }
$pdo->prepare('INSERT INTO notation (num_enseignant, num_stage, note, commentaire, date_notation) VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE note=VALUES(note), commentaire=VALUES(commentaire), date_notation=VALUES(date_notation)')
    ->execute([$ensId, $numStage, $note, $comment, $date]);
setFlash('success','✅ Note enregistrée.');
header('Location: ../enseignant/notes.php'); exit;

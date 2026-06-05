<?php
// actions/planifier_visite.php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('enseignant');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../enseignant/visites.php'); exit; }

$pdo          = getDB();
$ensId        = $_SESSION['profil_id'];
$numStage     = (int)($_POST['num_stage']     ?? 0);
$dateVisite   = trim($_POST['date_visite']    ?? '');
$heureVisite  = trim($_POST['heure_visite']   ?? '');
$lieu         = trim($_POST['lieu']           ?? '');
$commentaires = trim($_POST['commentaires']   ?? '');

if (!$numStage || !$dateVisite || !$heureVisite || !$lieu) {
    setFlash('error', 'Tous les champs obligatoires doivent être remplis.');
    header('Location: ../enseignant/visites.php'); exit;
}

$pdo->prepare('INSERT INTO visite_suivi (date_visite, lieu, heure_visite, commentaires, num_stage, num_enseignant) VALUES (?,?,?,?,?,?)')
    ->execute([$dateVisite, $lieu, $heureVisite, $commentaires, $numStage, $ensId]);

setFlash('success', '✅ Visite enregistrée avec succès.');
header('Location: ../enseignant/visites.php');
exit;

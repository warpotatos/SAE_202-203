<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('etudiant');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../etudiant/problemes.php');
    exit;
}

$pdo           = getDB();
$etudiantId    = $_SESSION['profil_id'];
$numStage      = (int)($_POST['num_stage']      ?? 0);
$dateIncident  = trim($_POST['date_incident']   ?? '');
$description   = trim($_POST['description']     ?? '');

if (!$numStage || !$dateIncident || !$description) {
    setFlash('error', 'Tous les champs sont requis.');
    header('Location: ../etudiant/problemes.php');
    exit;
}

// Vérifier que le stage appartient à l'étudiant
$check = $pdo->prepare('SELECT num_stage FROM stage WHERE num_stage = ? AND id_etudiant = ?');
$check->execute([$numStage, $etudiantId]);
if (!$check->fetch()) {
    setFlash('error', 'Stage introuvable.');
    header('Location: ../etudiant/problemes.php');
    exit;
}

$pdo->prepare('INSERT INTO probleme (date_incident, description, date_signalement, statut, num_stage, id_etudiant) VALUES (?, ?, CURDATE(), "ouvert", ?, ?)')
    ->execute([$dateIncident, $description, $numStage, $etudiantId]);

setFlash('success', '✅ Signalement enregistré. Votre enseignant référent a été notifié.');
header('Location: ../etudiant/problemes.php');
exit;

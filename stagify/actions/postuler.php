<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('etudiant');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../etudiant/offres.php');
    exit;
}

$pdo        = getDB();
$etudiantId = $_SESSION['profil_id'];
$numOffre   = (int)($_POST['num_offre'] ?? 0);

if (!$numOffre) {
    setFlash('error', 'Offre invalide.');
    header('Location: ../etudiant/offres.php');
    exit;
}

// Vérifier que l'offre est ouverte
$offre = $pdo->prepare("SELECT * FROM offre_stage WHERE num_offre = ? AND statut = 'ouverte'");
$offre->execute([$numOffre]);
if (!$offre->fetch()) {
    setFlash('error', 'Cette offre n\'est plus disponible.');
    header('Location: ../etudiant/offres.php');
    exit;
}

// Vérifier que l'étudiant n'a pas déjà postulé
$exist = $pdo->prepare('SELECT * FROM postulation WHERE id_etudiant = ? AND num_offre = ?');
$exist->execute([$etudiantId, $numOffre]);
if ($exist->fetch()) {
    setFlash('error', 'Vous avez déjà postulé à cette offre.');
    header('Location: ../etudiant/offres.php');
    exit;
}

$pdo->prepare('INSERT INTO postulation (id_etudiant, num_offre, date_postulation) VALUES (?, ?, CURDATE())')
    ->execute([$etudiantId, $numOffre]);

setFlash('success', '✅ Votre candidature a bien été envoyée !');
header('Location: ../etudiant/offres.php');
exit;

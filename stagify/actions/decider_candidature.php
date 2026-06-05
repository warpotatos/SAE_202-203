<?php
// actions/decider_candidature.php
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireRole('maitre_stage');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../professionnel/candidatures.php'); exit; }
$pdo        = getDB();
$idEtudiant = (int)($_POST['id_etudiant'] ?? 0);
$numOffre   = (int)($_POST['num_offre']   ?? 0);
$statut     = trim($_POST['statut']       ?? '');
if (!$idEtudiant || !$numOffre || !in_array($statut, ['acceptee','refusee'])) {
    setFlash('error','Données invalides.');
    header('Location: ../professionnel/candidatures.php'); exit;
}
$pdo->prepare('UPDATE postulation SET statut=? WHERE id_etudiant=? AND num_offre=?')->execute([$statut, $idEtudiant, $numOffre]);
setFlash('success', $statut === 'acceptee' ? '✅ Candidature acceptée.' : 'Candidature refusée.');
header('Location: ../professionnel/candidatures.php'); exit;

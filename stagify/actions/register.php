<?php
// actions/register.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../register.php');
    exit;
}

$role     = trim($_POST['role']     ?? '');
$nom      = trim($_POST['nom']      ?? '');
$prenom   = trim($_POST['prenom']   ?? '');
$email    = trim($_POST['email']    ?? '');
$login    = trim($_POST['login']    ?? '');
$password = trim($_POST['password'] ?? '');

$validRoles = ['etudiant', 'enseignant', 'maitre_stage'];
if (!in_array($role, $validRoles) || !$nom || !$prenom || !$email || !$login || !$password) {
    header('Location: ../register.php?error=missing');
    exit;
}

$pdo = getDB();

// Vérifier unicité login / email
$check = $pdo->prepare('SELECT num_utilisateur FROM utilisateur WHERE login = ? OR email = ?');
$check->execute([$login, $email]);
if ($check->fetch()) {
    header('Location: ../register.php?error=exists');
    exit;
}

$hash = password_hash($password, PASSWORD_BCRYPT);

$pdo->beginTransaction();
try {
    // Créer utilisateur
    $stmt = $pdo->prepare('INSERT INTO utilisateur (login, mot_de_passe, email, role) VALUES (?, ?, ?, ?)');
    $stmt->execute([$login, $hash, $email, $role]);
    $userId = $pdo->lastInsertId();

    // Créer le profil selon le rôle
    switch ($role) {
        case 'etudiant':
            $tel = trim($_POST['telephone'] ?? '');
            $td  = trim($_POST['td']        ?? '');
            $tp  = trim($_POST['tp']        ?? '');
            $pdo->prepare('INSERT INTO etudiant (nom, prenom, email, telephone, TD, TP, num_utilisateur) VALUES (?,?,?,?,?,?,?)')
                ->execute([$nom, $prenom, $email, $tel, $td, $tp, $userId]);
            break;

        case 'enseignant':
            $matiere = trim($_POST['matiere'] ?? '');
            $pdo->prepare('INSERT INTO enseignant (nom, prenom, matiere, email, num_utilisateur) VALUES (?,?,?,?,?)')
                ->execute([$nom, $prenom, $matiere, $email, $userId]);
            break;

        case 'maitre_stage':
            $entNom     = trim($_POST['entreprise_nom']     ?? '');
            $entAdresse = trim($_POST['entreprise_adresse'] ?? '');
            $entVille   = trim($_POST['entreprise_ville']   ?? '');
            $entTel     = trim($_POST['entreprise_tel']     ?? '');
            $poste      = trim($_POST['poste']              ?? '');

            // Créer ou récupérer l'entreprise
            $entId = null;
            if ($entNom) {
                $pdo->prepare('INSERT INTO entreprise (nom, adresse, ville, telephone, email) VALUES (?,?,?,?,?)')
                    ->execute([$entNom, $entAdresse, $entVille, $entTel, $email]);
                $entId = $pdo->lastInsertId();
            }

            $pdo->prepare('INSERT INTO maitre_stage (nom, prenom, email, poste, id_entreprise, num_utilisateur) VALUES (?,?,?,?,?,?)')
                ->execute([$nom, $prenom, $email, $poste, $entId, $userId]);
            break;
    }

    $pdo->commit();
    header('Location: ../index.php?registered=1');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    header('Location: ../register.php?error=db');
    exit;
}

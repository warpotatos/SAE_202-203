<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$login    = trim($_POST['login']    ?? '');
$password = trim($_POST['password'] ?? '');

if (!$login || !$password) {
    header('Location: ../index.php?error=empty');
    exit;
}

$pdo  = getDB();
$stmt = $pdo->prepare('SELECT * FROM utilisateur WHERE login = ?');
$stmt->execute([$login]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['mot_de_passe'])) {
    header('Location: ../index.php?error=credentials');
    exit;
}

session_regenerate_id(true);
$_SESSION['user_id'] = $user['num_utilisateur'];
$_SESSION['login']   = $user['login'];
$_SESSION['role']    = $user['role'];

switch ($user['role']) {
    case 'etudiant':
        $s = $pdo->prepare('SELECT nom, prenom, id_etudiant FROM etudiant WHERE num_utilisateur = ?');
        $s->execute([$user['num_utilisateur']]);
        $profil = $s->fetch();
        $_SESSION['nom']    = $profil['nom'];
        $_SESSION['prenom'] = $profil['prenom'];
        $_SESSION['profil_id'] = $profil['id_etudiant'];
        header('Location: ../etudiant/dashboard.php');
        break;

    case 'enseignant':
        $s = $pdo->prepare('SELECT nom, prenom, num_enseignant FROM enseignant WHERE num_utilisateur = ?');
        $s->execute([$user['num_utilisateur']]);
        $profil = $s->fetch();
        $_SESSION['nom']    = $profil['nom'];
        $_SESSION['prenom'] = $profil['prenom'];
        $_SESSION['profil_id'] = $profil['num_enseignant'];
        header('Location: ../enseignant/dashboard.php');
        break;

    case 'maitre_stage':
        $s = $pdo->prepare('SELECT nom, prenom, id_maitre FROM maitre_stage WHERE num_utilisateur = ?');
        $s->execute([$user['num_utilisateur']]);
        $profil = $s->fetch();
        $_SESSION['nom']    = $profil['nom'];
        $_SESSION['prenom'] = $profil['prenom'];
        $_SESSION['profil_id'] = $profil['id_maitre'];
        header('Location: ../professionnel/dashboard.php');
        break;

    case 'admin':
        $s = $pdo->prepare('SELECT nom, prenom FROM admin WHERE num_utilisateur = ?');
        $s->execute([$user['num_utilisateur']]);
        $profil = $s->fetch();
        $_SESSION['nom']    = $profil['nom'];
        $_SESSION['prenom'] = $profil['prenom'];
        header('Location: ../admin/dashboard.php');
        break;
}
exit;

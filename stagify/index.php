<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Si déjà connecté, rediriger
if (!empty($_SESSION['user_id'])) {
    header('Location: ' . $_SESSION['role'] . '/dashboard.php');
    exit;
}

$error = '';
if (!empty($_GET['error'])) {
    $error = match($_GET['error']) {
        'session' => 'Votre session a expiré. Veuillez vous reconnecter.',
        'access'  => 'Accès refusé : vous n\'avez pas les droits nécessaires.',
        default   => ''
    };
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Stagify — Connexion</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<div class="auth-page">
  <div class="auth-box">
    <div class="brand">Stagify</div>
  

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($_GET['registered'])): ?>
      <div class="alert alert-success">✅ Compte créé ! Vous pouvez vous connecter.</div>
    <?php endif; ?>

    <form method="POST" action="actions/login.php">
      <div class="form-group">
        <label class="form-label">Identifiant</label>
        <input class="form-control" type="text" name="login" placeholder="Votre identifiant" required autofocus />
      </div>
      <div class="form-group">
        <label class="form-label">Mot de passe</label>
        <input class="form-control" type="password" name="password" placeholder="••••••••" required />
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:.5rem;">
        Se connecter
      </button>
    </form>

    <div class="auth-footer">
      Pas encore de compte ? <a href="register.php">Créer un compte</a>
    </div>


  </div>
</div>
</body>
</html>

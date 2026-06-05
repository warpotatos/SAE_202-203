<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . $_SESSION['role'] . '/dashboard.php');
    exit;
}
$error = '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Stagify — Créer un compte</title>
  <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<div class="auth-page" style="padding:2rem 1rem;">
  <div class="auth-box" style="max-width:520px;">
    <div class="brand">Stagify</div>
    <div class="tagline">Créer votre compte</div>

    <div id="step-role">
      <p style="text-align:center;margin-bottom:1rem;font-size:.9rem;color:var(--muted);">Choisissez votre rôle :</p>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
        <button class="btn btn-outline" onclick="selectRole('etudiant')"> Étudiant</button>
        <button class="btn btn-outline" onclick="selectRole('enseignant')"> Enseignant</button>
        <button class="btn btn-outline" style="grid-column:1/-1;" onclick="selectRole('maitre_stage')"> Professionnel / Maître de stage</button>
      </div>
    </div>

    <form id="register-form" class="hidden" method="POST" action="actions/register.php">
      <input type="hidden" name="role" id="role-input" />
      <div id="role-label" class="alert alert-info" style="margin-bottom:1.25rem;font-size:.85rem;"></div>

      <p class="section-title">Informations de connexion</p>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Nom</label>
          <input class="form-control" type="text" name="nom" required />
        </div>
        <div class="form-group">
          <label class="form-label">Prénom</label>
          <input class="form-control" type="text" name="prenom" required />
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Email</label>
        <input class="form-control" type="email" name="email" required />
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Identifiant</label>
          <input class="form-control" type="text" name="login" required />
        </div>
        <div class="form-group">
          <label class="form-label">Mot de passe</label>
          <input class="form-control" type="password" name="password" required minlength="8" />
        </div>
      </div>

      <div id="fields-etudiant" class="hidden">
        <p class="section-title">Informations étudiant</p>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Téléphone</label>
            <input class="form-control" type="tel" name="telephone" />
          </div>
          <div class="form-group">
            <label class="form-label">Groupe TD</label>
            <input class="form-control" type="text" name="td" placeholder="TD1" />
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Groupe TP</label>
          <input class="form-control" type="text" name="tp" placeholder="TP2" />
        </div>
      </div>

      <div id="fields-enseignant" class="hidden">
        <p class="section-title">Informations enseignant</p>
        <div class="form-group">
          <label class="form-label">Matière enseignée</label>
          <input class="form-control" type="text" name="matiere" placeholder="Informatique, Réseau…" />
        </div>
      </div>

      <div id="fields-maitre_stage" class="hidden">
        <p class="section-title">Informations professionnelles</p>
        <div class="form-group">
          <label class="form-label">Nom de l'entreprise</label>
          <input class="form-control" type="text" name="entreprise_nom" />
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Adresse</label>
            <input class="form-control" type="text" name="entreprise_adresse" />
          </div>
          <div class="form-group">
            <label class="form-label">Ville</label>
            <input class="form-control" type="text" name="entreprise_ville" />
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Téléphone entreprise</label>
            <input class="form-control" type="tel" name="entreprise_tel" />
          </div>
          <div class="form-group">
            <label class="form-label">Votre poste</label>
            <input class="form-control" type="text" name="poste" placeholder="Responsable RH" />
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:.5rem;">Créer mon compte</button>
      <button type="button" class="btn btn-outline" style="width:100%;justify-content:center;margin-top:.5rem;" onclick="resetForm()">← Changer de rôle</button>
    </form>

    <div class="auth-footer">Déjà un compte ? <a href="index.php">Se connecter</a></div>
  </div>
</div>
<script>
const labels = {
  etudiant:     ' Vous créez un compte Étudiant',
  enseignant:   ' Vous créez un compte Enseignant',
  maitre_stage: ' Vous créez un compte Professionnel / Maître de stage',
};
function selectRole(role) {
  document.getElementById('step-role').classList.add('hidden');
  document.getElementById('register-form').classList.remove('hidden');
  document.getElementById('role-input').value = role;
  document.getElementById('role-label').textContent = labels[role];
  ['etudiant','enseignant','maitre_stage'].forEach(r => {
    document.getElementById('fields-' + r).classList.add('hidden');
  });
  document.getElementById('fields-' + role).classList.remove('hidden');
}
function resetForm() {
  document.getElementById('register-form').classList.add('hidden');
  document.getElementById('step-role').classList.remove('hidden');
}
</script>
</body>
</html>

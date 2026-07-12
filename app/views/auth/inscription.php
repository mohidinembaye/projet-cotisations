<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Inscription — Cotisations</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="auth-screen">
  <div class="auth-card">
    <div class="sidebar-brand" style="margin-bottom:22px;">
      <div class="brand-name">Cotisations</div>
      <span class="brand-tag">AUTO-INSCRIPTION APPRENANT</span>
    </div>

    <div class="card-title">Créer mon compte</div>
    <form method="post" action="/inscription" novalidate>
      <?= csrf_field() ?>
      <div class="field-row">
        <div class="field">
          <label>Prénom</label>
          <input type="text" name="prenom" value="<?= e($old['prenom'] ?? '') ?>" placeholder="Ex : Mohidine">
          <?php if (!empty($erreurs['prenom'])): ?><div class="field-error"><?= e($erreurs['prenom']) ?></div><?php endif; ?>
        </div>
        <div class="field">
          <label>Nom</label>
          <input type="text" name="nom" value="<?= e($old['nom'] ?? '') ?>" placeholder="Ex : Mbaye">
          <?php if (!empty($erreurs['nom'])): ?><div class="field-error"><?= e($erreurs['nom']) ?></div><?php endif; ?>
        </div>
      </div>
      <div class="field">
        <label>Email institutionnel</label>
        <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" placeholder="vous@classe.sn">
        <?php if (!empty($erreurs['email'])): ?><div class="field-error"><?= e($erreurs['email']) ?></div><?php endif; ?>
      </div>
      <div class="field">
        <label>Mot de passe</label>
        <input type="password" name="password" placeholder="4 caractères minimum">
        <?php if (!empty($erreurs['password'])): ?><div class="field-error"><?= e($erreurs['password']) ?></div><?php endif; ?>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Créer mon compte</button>
    </form>

    <div class="auth-footer">Déjà inscrit ? <a href="/login">Se connecter</a></div>
  </div>
</div>
</body>
</html>
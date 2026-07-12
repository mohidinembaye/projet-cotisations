<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion — Cotisations</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="auth-screen">
  <div class="auth-card">
    <div class="sidebar-brand" style="margin-bottom:22px;">
      <div class="brand-name">Cotisations</div>
      <span class="brand-tag">GESTION FINANCIÈRE</span>
    </div>

    <?php $flash = flash_get(); if ($flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?>">
        <span><?= $flash['type'] === 'success' ? '&#9989;' : '&#10071;' ?></span>
        <div><?= e($flash['message']) ?></div>
      </div>
    <?php endif; ?>

    <div class="card-title">Connexion</div>
    <form method="post" action="/login" novalidate>
      <?= csrf_field() ?>
      <div class="field">
        <label>Email</label>
        <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" placeholder="vous@classe.sn" autofocus>
        <?php if (!empty($erreurs['email'])): ?><div class="field-error"><?= e($erreurs['email']) ?></div><?php endif; ?>
      </div>
      <div class="field">
        <label>Mot de passe</label>
        <input type="password" name="password" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;">
        <?php if (!empty($erreurs['password'])): ?><div class="field-error"><?= e($erreurs['password']) ?></div><?php endif; ?>
      </div>
      <button type="submit" class="btn btn-primary btn-block">Se connecter</button>
    </form>

    <div class="auth-demo">
      <div class="cell-muted" style="margin-bottom:6px; font-weight:700;">Comptes de démonstration</div>
      <div class="cell-muted">Gérant : gerant@cotisations.app / gerant123</div>
      <div class="cell-muted">Coach : coach@cotisations.app / coach123</div>
      <div class="cell-muted">Apprenant : mohidine.mbaye@classe.sn / apprenant123</div>
    </div>

    <div class="auth-footer">Pas encore de compte apprenant ? <a href="/inscription">S'inscrire</a></div>
  </div>
</div>
</body>
</html>
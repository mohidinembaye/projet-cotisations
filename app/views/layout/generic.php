<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?> — Cotisations</title>
<link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="error-page">
  <div class="error-code"><?= e($code) ?></div>
  <div class="error-title"><?= e($title) ?></div>
  <p class="error-message"><?= e($message) ?></p>
  <a href="/" class="btn btn-primary">Retour à l'accueil</a>
</div>
</body>
</html>
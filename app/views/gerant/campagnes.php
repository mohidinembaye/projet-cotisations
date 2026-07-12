<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Campagnes</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="app">
        <?php require __DIR__ . '/../layout/main.php'; ?>

        <main class="main">
            <header class="topbar">
                <strong>Nouvelle campagne</strong>
                <a href="/logout">Déconnexion</a>
            </header>

            <section class="content">
                <div class="grid grid-2">
                    <form method="post" class="card">
                        <h1 class="card-title">Créer une cotisation ponctuelle</h1>

                        <?php foreach (array_merge($flash, array_map(fn($m) => ['type' => 'error', 'message' => $m], $erreurs)) as $message): ?>
                            <p class="alert alert-<?= e($message['type']) ?>">
                                <?= e($message['message']) ?>
                            </p>
                        <?php endforeach; ?>

                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                        <div class="field">
                            <label>Type</label>
                            <select name="type">
                                <option value="anniversaire">Anniversaire (montant fixe)</option>
                                <option value="deces">Décès (don libre, 7 jours)</option>
                                <option value="autre">Autre événement</option>
                            </select>
                        </div>

                        <div class="field">
                            <label>Nom</label>
                            <input name="nom" required placeholder="Ex. Anniversaire de …">
                        </div>

                        <div class="field">
                            <label>Montant fixe (hors décès)</label>
                            <input name="montant" type="number" min="1" placeholder="300">
                        </div>

                        <div class="field">
                            <label>Date limite (hors décès)</label>
                            <input name="date_limite" type="date" value="<?= date('Y-m-d') ?>">
                        </div>

                        <button class="btn btn-primary btn-block">Valider la campagne</button>
                    </form>

                    <div class="card">
                        <h2 class="card-title">Règles appliquées</h2>
                        <p><strong>Anniversaire :</strong> création pendant la dernière semaine du mois, montant fixe.</p>
                        <p><strong>Décès :</strong> don libre, clôture automatique après 7 jours.</p>
                        <p><strong>Autre :</strong> montant fixe et date limite définis par le gérant.</p>

                        <h2 class="card-title">Historique</h2>
                        <?php foreach ($campagnes as $campagne): ?>
                            <p>
                                <?= e($campagne['nom']) ?>
                                <span class="badge <?= $campagne['statut'] === 'ouverte' ? 'green' : 'neutral' ?>">
                                    <?= e($campagne['statut']) ?>
                                </span>
                            </p>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

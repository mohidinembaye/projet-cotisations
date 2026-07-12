<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Saisie paiement</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="app">
        <?php require __DIR__ . '/../layout/main.php'; ?>

        <main class="main">
            <header class="topbar">
                <strong>Saisie de paiement</strong>
                <a href="/logout">Déconnexion</a>
            </header>

            <section class="content">
                <div class="grid grid-2">
                    <form method="post" class="card">
                        <h1 class="card-title">Encaissement</h1>

                        <?php foreach (array_merge($flash, array_map(fn($m) => ['type' => 'error', 'message' => $m], $erreurs)) as $message): ?>
                            <p class="alert alert-<?= e($message['type']) ?>">
                                <?= e($message['message']) ?>
                            </p>
                        <?php endforeach; ?>

                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                        <div class="field">
                            <label>Apprenant</label>
                            <select name="apprenant_id" required>
                                <option value="">Sélectionner</option>
                                <?php foreach ($apprenants as $apprenant): ?>
                                    <option value="<?= e($apprenant['id']) ?>">
                                        <?= e($apprenant['prenom'] . ' ' . $apprenant['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field">
                            <label>Type</label>
                            <select name="type">
                                <option value="hebdomadaire">Hebdomadaire</option>
                                <option value="anniversaire">Anniversaire</option>
                                <option value="deces">Don décès</option>
                            </select>
                        </div>

                        <div class="field">
                            <label>Montant reçu (FCFA)</label>
                            <input type="number" name="montant" min="1" required>
                        </div>

                        <button class="btn btn-primary btn-block">Confirmer l’encaissement</button>
                    </form>

                    <div class="card">
                        <h2 class="card-title">Ventilation hebdomadaire</h2>
                        <p>
                            Montant par semaine :
                            <strong><?= number_format($configuration['montant'], 0, ',', ' ') ?> FCFA</strong>
                        </p>
                        <p>
                            Un paiement hebdomadaire valide automatiquement les premières semaines impayées,
                            dans l’ordre.
                        </p>
                        <p class="cell-muted">
                            Exemple : <?= number_format($configuration['montant'] * 3, 0, ',', ' ') ?> FCFA
                            règle 3 semaines consécutives.
                        </p>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

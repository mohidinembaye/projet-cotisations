<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Apprenants</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="app">
        <?php require __DIR__ . '/../layout/main.php'; ?>

        <main class="main">
            <header class="topbar">
                <strong>Apprenants</strong>
                <a href="/logout">Déconnexion</a>
            </header>

            <section class="content">
                <?php foreach (array_merge($flash, array_map(fn($m) => ['type' => 'error', 'message' => $m], $erreurs)) as $message): ?>
                    <p class="alert alert-<?= e($message['type']) ?>">
                        <?= e($message['message']) ?>
                    </p>
                <?php endforeach; ?>

                <div class="grid grid-2">
                    <div class="card">
                        <h1 class="card-title">Registre des apprenants</h1>
                        <table>
                            <thead><tr><th>NOM</th><th>EMAIL</th></tr></thead>
                            <tbody>
                                <?php foreach ($apprenants as $apprenant): ?>
                                    <tr>
                                        <td><?= e($apprenant['prenom'] . ' ' . $apprenant['nom']) ?></td>
                                        <td><?= e($apprenant['email']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div>
                        <form class="card" method="post">
                            <h2 class="card-title">Ajouter un apprenant</h2>
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="ajouter">
                            <div class="field"><label>Prénom</label><input name="prenom" required></div>
                            <div class="field"><label>Nom</label><input name="nom" required></div>
                            <div class="field"><label>Email</label><input type="email" name="email" required></div>
                            <button class="btn btn-primary btn-block">Ajouter</button>
                        </form>

                        <form class="card" style="margin-top:18px" method="post">
                            <h2 class="card-title">Reprise d’un passif</h2>
                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                            <input type="hidden" name="action" value="passif">
                            <div class="field">
                                <label>Apprenant</label>
                                <select name="apprenant_id">
                                    <option value="">Sélectionner</option>
                                    <?php foreach ($apprenants as $apprenant): ?>
                                        <option value="<?= e($apprenant['id']) ?>">
                                            <?= e($apprenant['prenom'] . ' ' . $apprenant['nom']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="field"><label>Montant FCFA</label><input name="montant" type="number" min="1" required></div>
                            <button class="btn btn-outline btn-block">Enregistrer le passif</button>
                        </form>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

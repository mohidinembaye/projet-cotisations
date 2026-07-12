<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tableau de bord</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="app">
        <?php require __DIR__ . '/../layout/main.php'; ?>

        <main class="main">
            <header class="topbar">
                <strong>Gestion des cotisations</strong>
                <a href="/logout">Déconnexion</a>
            </header>

            <section class="content">
                <?php foreach (array_merge($flash, array_map(fn($m) => ['type' => 'error', 'message' => $m], $erreurs)) as $message): ?>
                    <p class="alert alert-<?= e($message['type']) ?>">
                        <?= e($message['message']) ?>
                    </p>
                <?php endforeach; ?>

                <div class="hero">
                    <div class="hero-eyebrow">TRÉSORERIE &amp; PERFORMANCE</div>
                    <div class="hero-title">Suivi financier en temps réel</div>
                    <div class="hero-stats">
                        <div>
                            <div class="hero-stat-label">TOTAL COLLECTÉ</div>
                            <div class="hero-stat-value">
                                <?= number_format($total, 0, ',', ' ') ?> FCFA
                            </div>
                        </div>
                        <div>
                            <div class="hero-stat-label">RECOUVREMENT</div>
                            <div class="hero-stat-value green"><?= e((string) $recouvrement) ?>%</div>
                        </div>
                    </div>
                </div>

                <form class="card" method="post" style="margin-bottom:18px">
                    <div class="flex-between">
                        <h2 class="card-title">Paramétrage hebdomadaire</h2>
                        <button class="btn btn-outline">Enregistrer</button>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <div class="field-row">
                        <div class="field">
                            <label>Montant par semaine (FCFA)</label>
                            <input name="montant_hebdo" type="number" min="1" value="<?= e((string) $configuration['montant']) ?>" required>
                        </div>
                        <div class="field">
                            <label>Nombre de semaines</label>
                            <input name="nombre_semaines" type="number" min="1" max="52" value="<?= e((string) $configuration['nombre']) ?>" required>
                        </div>
                    </div>
                </form>

                <div class="card">
                    <div class="flex-between">
                        <h2 class="card-title">Suivi hebdomadaire</h2>
                        <a class="btn btn-outline" href="/gerant/paiements/create">Saisir un paiement</a>
                    </div>
                    <p class="cell-muted">
                        Montant hebdomadaire : <?= number_format($configuration['montant'], 0, ',', ' ') ?> FCFA
                        — <?= $configuration['nombre'] ?> semaines
                    </p>
                    <table>
                        <thead><tr><th>APPRENANT</th><th>SEMAINES PAYÉES</th><th>TOTAL</th></tr></thead>
                        <tbody>
                            <?php if (!$apprenants): ?>
                                <tr><td colspan="3">Aucun apprenant enregistré.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($apprenants as $apprenant): ?>
<?php $semaines = getSemainesPayees($apprenant['id']); ?>                                <tr>
                                    <td>
                                        <?= e($apprenant['prenom'] . ' ' . $apprenant['nom']) ?><br>
                                        <small><?= e($apprenant['email'] ?: 'Email non renseigné') ?></small>
                                    </td>
                                    <td><?= $semaines ? 'S' . implode(', S', $semaines) : 'Aucune' ?></td>
                                    <td><?= number_format(count($semaines) * $configuration['montant'], 0, ',', ' ') ?> FCFA</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="grid grid-2" style="margin-top:18px">
                    <div class="card">
                        <h2 class="card-title">Campagnes actives</h2>
                        <?php foreach ($campagnes as $campagne): ?>
                            <?php if ($campagne['statut'] === 'ouverte'): ?>
                                <p>
                                    <strong><?= e($campagne['nom']) ?></strong>
                                    — <?= e($campagne['type']) ?>,
                                    échéance <?= e(date('d/m/Y', strtotime($campagne['date_limite']))) ?>
                                </p>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <a href="/gerant/campagnes/create">Créer une campagne</a>
                    </div>
                    <div class="card">
                        <h2 class="card-title">Gestion</h2>
                        <p><a href="/gerant/apprenants">Gérer les apprenants et les passifs</a></p>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

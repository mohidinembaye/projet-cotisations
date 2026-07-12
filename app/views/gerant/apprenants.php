<div class="hero" style="margin-bottom:18px;">
  <div class="hero-eyebrow">REGISTRE DE LA CLASSE</div>
  <div class="hero-title">Gérez vos apprenants avec précision.</div>
  <div class="hero-stats">
    <div>
      <div class="hero-stat-label">TOTAL APPRENANTS</div>
      <div class="hero-stat-value"><?= count($apprenants) ?></div>
    </div>
  </div>
</div>

<div class="grid grid-2">
  <div class="card">
    <div class="flex-between" style="margin-bottom:12px;">
      <div class="card-title" style="margin:0;">Liste des Apprenants <span class="cell-muted"><?= count($apprenants) ?> inscrits</span></div>
    </div>
    <form method="get" action="/gerant/apprenants" style="margin-bottom:12px;">
      <input type="text" name="q" value="<?= e($recherche) ?>" placeholder="Rechercher par nom, email, matricule..." style="width:100%; padding:8px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px;">
    </form>
    <div class="table-scroll">
      <table>
        <thead>
          <tr><th>APPRENANT</th><th>STATUT</th><th style="text-align:right;">DETTE</th></tr>
        </thead>
        <tbody>
          <?php foreach ($apprenants as $a): $statut = apprenant_statut_libelle($a['id']); $dette = apprenant_montant_du_hebdo($a['id']) + passif_total_pour($a['id']); ?>
            <tr>
              <td class="row-user">
                <span class="avatar-sm"><?= e(initials($a['prenom'], $a['nom'])) ?></span>
                <div>
                  <div class="cell-primary"><?= e($a['prenom'] . ' ' . $a['nom']) ?></div>
                  <div class="cell-muted"><?= e($a['email']) ?> &nbsp;•&nbsp; <?= e($a['matricule']) ?></div>
                </div>
              </td>
              <td><span class="badge <?= e($statut['classe']) ?>"><?= e($statut['label']) ?></span></td>
              <td style="text-align:right;" class="<?= $dette > 0 ? 'text-red cell-primary' : 'cell-muted' ?>"><?= $dette > 0 ? money($dette) : '—' ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$apprenants): ?>
            <tr><td colspan="3" class="cell-muted">Aucun résultat.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div>
    <div class="card" style="margin-bottom:18px;">
      <div class="card-title">Nouveau Profil</div>
      <form method="post" action="/gerant/apprenants/create">
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
          <input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" placeholder="mo@gmail.com">
          <?php if (!empty($erreurs['email'])): ?><div class="field-error"><?= e($erreurs['email']) ?></div><?php endif; ?>
          <div class="cell-muted" style="margin-top:4px;">Un mot de passe temporaire sera généré et affiché après ajout.</div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Ajouter au registre</button>
      </form>
    </div>

    <div class="card">
      <div class="card-title">&#9878;&#65039; Dettes &amp; Passifs</div>
      <form method="post" action="/gerant/apprenants/passif">
        <?= csrf_field() ?>
        <div class="field">
          <label>Profil concerné</label>
          <select name="apprenant_id" required>
            <option value="">Rechercher un apprenant...</option>
            <?php foreach ($apprenants as $a): ?>
              <option value="<?= e($a['id']) ?>"><?= e($a['prenom'] . ' ' . $a['nom']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field-row">
          <div class="field"><label>Semaines de retard</label><input type="number" name="nb_semaines" value="0" min="0"></div>
          <div class="field"><label>Montant total (F)</label><input type="number" name="montant" value="0" min="0" step="1"></div>
        </div>
        <div class="field">
          <label>Note (optionnel)</label>
          <input type="text" name="notes" placeholder="Ex : Report avant mise en place de la plateforme">
        </div>
        <div class="cell-muted" style="margin-bottom:12px;">Ce montant sera ajouté au solde débiteur de l'apprenant.</div>
        <button type="submit" class="btn btn-outline btn-block">&#128190; Enregistrer le passif</button>
      </form>
    </div>
  </div>
</div>
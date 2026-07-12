<div class="grid grid-2">
  <div>
    <div class="hero" style="margin-bottom:18px;">
      <div class="hero-eyebrow">CONFIGURATION DE COLLECTE</div>
      <div class="hero-title">Nouvelle Campagne Ponctuelle</div>
      <p style="font-size:12.5px; color:var(--navy-200); max-width:80%; margin-top:8px;">
        Initialisez une levée de fonds spécifique. Les règles s'adaptent automatiquement selon le type d'événement.
      </p>
    </div>

    <div class="card">
      <div class="card-title">Type de Campagne</div>
      <form method="post" action="/gerant/campagnes/create" id="campagneForm">
        <?= csrf_field() ?>
        <div class="choice-group" role="radiogroup">
          <label class="choice-card <?= ($old['type'] ?? 'anniversaire') === 'anniversaire' ? 'selected' : '' ?>">
            <input type="radio" name="type" value="anniversaire" <?= ($old['type'] ?? 'anniversaire') === 'anniversaire' ? 'checked' : '' ?> style="display:none;">
            <span class="choice-icon">&#127874;</span>Anniversaire
          </label>
          <label class="choice-card <?= ($old['type'] ?? '') === 'deces' ? 'selected' : '' ?>">
            <input type="radio" name="type" value="deces" <?= ($old['type'] ?? '') === 'deces' ? 'checked' : '' ?> style="display:none;">
            <span class="choice-icon">&#128148;</span>Décès
          </label>
          <label class="choice-card <?= ($old['type'] ?? '') === 'autre' ? 'selected' : '' ?>">
            <input type="radio" name="type" value="autre" <?= ($old['type'] ?? '') === 'autre' ? 'checked' : '' ?> style="display:none;">
            <span class="choice-icon">&#8943;</span>Autre
          </label>
        </div>
        <?php if (!empty($erreurs['type'])): ?><div class="field-error" style="margin-bottom:10px;"><?= e($erreurs['type']) ?></div><?php endif; ?>

        <div class="field">
          <label>Nom de la campagne</label>
          <input type="text" name="nom" value="<?= e($old['nom'] ?? '') ?>" placeholder="Ex : Anniversaire de Mohidine Mbaye">
          <?php if (!empty($erreurs['nom'])): ?><div class="field-error"><?= e($erreurs['nom']) ?></div><?php endif; ?>
        </div>

        <div class="field-row" id="champsAutre">
          <div class="field">
            <label>Date d'échéance</label>
            <input type="date" name="date_limite" value="<?= e($old['date_limite'] ?? '') ?>">
            <div class="cell-muted" style="margin-top:4px;">Anniversaire : automatique (dernière semaine du mois). Décès : automatique (7 jours).</div>
            <?php if (!empty($erreurs['date_limite'])): ?><div class="field-error"><?= e($erreurs['date_limite']) ?></div><?php endif; ?>
          </div>
          <div class="field">
            <label>Montant par personne</label>
            <div style="position:relative;">
              <input type="number" name="montant_fixe" value="<?= e($old['montant_fixe'] ?? '300') ?>" min="0" step="1" style="padding-right:52px;">
              <span class="cell-muted" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:11px; font-weight:700;">FCFA</span>
            </div>
            <div class="cell-muted" style="margin-top:4px;">Décès : montant libre laissé au choix de chaque apprenant, ce champ est ignoré.</div>
            <?php if (!empty($erreurs['montant_fixe'])): ?><div class="field-error"><?= e($erreurs['montant_fixe']) ?></div><?php endif; ?>
          </div>
        </div>

        <button type="submit" class="btn btn-success btn-block" style="margin-top:6px;">Valider la Campagne</button>
      </form>
    </div>
  </div>

  <div>
    <div class="alert alert-info" style="margin-bottom:16px;">
      <span>&#8505;&#65039;</span>
      <div>
        Anniversaire : montant fixe, collecté automatiquement lors de la dernière semaine du mois (une seule campagne active par mois).
        Décès : montant libre, clôture automatique après 7 jours. Autre : montant et date libres.
      </div>
    </div>

    <div class="card">
      <div class="card-title">Campagnes</div>
      <div class="table-scroll">
        <table>
          <thead>
            <tr><th>NOM</th><th>TYPE</th><th>STATUT</th><th style="text-align:right;">COLLECTÉ</th></tr>
          </thead>
          <tbody>
            <?php foreach ($campagnes as $c): ?>
              <tr>
                <td>
                  <div class="cell-primary"><?= e($c['nom']) ?></div>
                  <div class="cell-muted">Échéance : <?= fdate($c['date_limite']) ?></div>
                </td>
                <td class="cell-muted"><?= e(ucfirst($c['type'])) ?></td>
                <td><span class="badge <?= $c['statut'] === 'active' ? 'green' : 'neutral' ?>"><?= $c['statut'] === 'active' ? 'ACTIF' : 'CLÔTURÉE' ?></span></td>
                <td style="text-align:right;" class="cell-primary"><?= money($c['total_collecte']) ?> <span class="cell-muted">(<?= e($c['nb_contributeurs']) ?>)</span></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$campagnes): ?>
              <tr><td colspan="4" class="cell-muted">Aucune campagne pour le moment.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('#campagneForm .choice-card').forEach(function (card) {
  card.addEventListener('click', function () {
    document.querySelectorAll('#campagneForm .choice-card').forEach(function (c) { c.classList.remove('selected'); });
    card.classList.add('selected');
    card.querySelector('input[type=radio]').checked = true;
  });
});
</script>
<div class="flex-between" style="margin-bottom:16px; flex-wrap:wrap; gap:10px;">
  <div class="cell-muted">SESSION ACTIVE — Caisse Principale</div>
  <div style="text-align:right;">
    <div class="cell-muted">TOTAL COLLECTÉ (JOUR)</div>
    <div style="font-weight:700; color:var(--green);"><?= money($totalCollecteJour) ?> &#8599;</div>
  </div>
  <div style="background:#eef0f4; padding:8px 14px; border-radius:var(--radius-sm); text-align:right;">
    <div class="cell-muted" style="font-size:10px;">DATE DU JOUR</div>
    <div class="cell-primary" style="font-size:12px;"><?= e(mb_strtoupper(date('d F Y'))) ?></div>
  </div>
</div>

<div class="grid grid-2">
  <div>
    <div class="card" style="margin-bottom:18px;">
      <div class="card-title">&#9312; Sélection de l'Apprenant</div>
      <form method="get" action="/gerant/paiements/create">
        <select name="apprenant_id" onchange="this.form.submit()" style="width:100%; padding:9px 10px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:13px;">
          <option value="">Rechercher par nom...</option>
          <?php foreach ($apprenants as $a): ?>
            <option value="<?= e($a['id']) ?>" <?= $apprenant && $apprenant['id'] === $a['id'] ? 'selected' : '' ?>><?= e($a['prenom'] . ' ' . $a['nom']) ?> — <?= e($a['matricule']) ?></option>
          <?php endforeach; ?>
        </select>
      </form>

      <?php if ($apprenant): $statut = apprenant_statut_libelle($apprenant['id']); ?>
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px; border:1px solid var(--border); border-radius:var(--radius-sm); margin-top:12px;">
          <div style="display:flex; align-items:center; gap:12px;">
            <div class="avatar-sm" style="width:38px; height:38px; font-size:13px;"><?= e(initials($apprenant['prenom'], $apprenant['nom'])) ?></div>
            <div>
              <div class="cell-primary"><?= e($apprenant['prenom'] . ' ' . $apprenant['nom']) ?></div>
              <div class="cell-muted">Statut : <span class="<?= $statut['classe'] === 'green' ? 'text-green' : 'text-red' ?>"><?= e($statut['label']) ?></span></div>
            </div>
          </div>
          <span class="badge neutral" style="background:var(--navy-950); color:#fff;">Matricule : <?= e($apprenant['matricule']) ?></span>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($apprenant): ?>
    <div class="card">
      <div class="card-title">&#9313; Type de Paiement</div>
      <div class="choice-group" id="typeChoix">
        <label class="choice-card selected" data-type="hebdo"><input type="radio" name="type_display" value="hebdo" checked style="display:none;"><span class="choice-icon">&#128197;</span>Hebdomadaire</label>
        <label class="choice-card <?= $campagneAnniversaire ? '' : 'disabled' ?>" data-type="anniversaire"><input type="radio" name="type_display" value="anniversaire" style="display:none;" <?= $campagneAnniversaire ? '' : 'disabled' ?>><span class="choice-icon">&#127874;</span>Anniversaire</label>
        <label class="choice-card <?= $campagneDeces ? '' : 'disabled' ?>" data-type="deces"><input type="radio" name="type_display" value="deces" style="display:none;" <?= $campagneDeces ? '' : 'disabled' ?>><span class="choice-icon">&#128153;</span>Dons Décès</label>
      </div>
      <?php if (!$campagneAnniversaire && !$campagneDeces): ?>
        <div class="cell-muted">Aucune campagne ponctuelle active — seule la cotisation hebdomadaire est disponible.</div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

  <div>
    <?php if (!$apprenant): ?>
      <div class="card"><div class="cell-muted">Sélectionnez un apprenant pour saisir un paiement.</div></div>
    <?php else: ?>
      <form method="post" action="/gerant/paiements/create" id="paiementForm">
        <?= csrf_field() ?>
        <input type="hidden" name="apprenant_id" value="<?= e($apprenant['id']) ?>">
        <input type="hidden" name="type_paiement" id="typePaiementInput" value="hebdo">
        <input type="hidden" name="campagne_id" id="campagneIdInput" value="<?= $campagneAnniversaire ? e($campagneAnniversaire['id']) : '' ?>">

        <div class="card" style="margin-bottom:18px;" id="blocHebdo">
          <div class="flex-between" style="margin-bottom:12px;">
            <div class="card-title" style="margin:0;">Cotisations Semaines</div>
            <div class="cell-muted">Taux : <?= money($montantHebdo) ?> / sem</div>
          </div>
          <div class="week-grid">
            <?php foreach ($cellulesSemaines as $cell): ?>
              <div class="week-pill <?= $cell['payee'] ? 'paid' : ($cell['retard'] ? 'due' : 'idle') ?>">
                <span class="dot"><?= $cell['payee'] ? '&#10003;' : '&#10007;' ?></span>S<?= e($cell['semaine']['numero']) ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="card" style="margin-bottom:18px; display:none;" id="blocAnniversaire">
          <?php if ($campagneAnniversaire): ?>
            <div class="cell-muted">Montant fixe imposé : <strong class="cell-primary"><?= money($campagneAnniversaire['montant_fixe']) ?></strong></div>
          <?php endif; ?>
        </div>

        <div class="card" style="margin-bottom:18px;">
          <label style="display:block; font-size:11px; font-weight:700; color:var(--text-faint); text-transform:uppercase; margin-bottom:8px;" id="labelMontant">Montant total versé (FCFA)</label>
          <input type="number" name="montant" id="montantInput" min="1" step="1" value="<?= e($campagneAnniversaire['montant_fixe'] ?? $montantHebdo) ?>" style="font-size:26px; font-weight:700; width:100%; border:1px solid var(--border); border-radius:var(--radius-sm); padding:8px 10px;">
          <?php if (!empty($erreurs['montant'])): ?><div class="field-error"><?= e($erreurs['montant']) ?></div><?php endif; ?>
          <div class="cell-muted" style="margin-top:8px;" id="ventilationHint">La ventilation validera automatiquement les semaines consécutives impayées les plus anciennes.</div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Confirmer l'encaissement &nbsp;&#8594;</button>
        <div class="cell-muted" style="text-align:center; margin-top:10px;">Une quittance sera générée automatiquement.</div>
      </form>
    <?php endif; ?>
  </div>
</div>

<script>
(function () {
  var choix = document.querySelectorAll('#typeChoix .choice-card');
  var typeInput = document.getElementById('typePaiementInput');
  var campagneInput = document.getElementById('campagneIdInput');
  var montantInput = document.getElementById('montantInput');
  var hint = document.getElementById('ventilationHint');
  var label = document.getElementById('labelMontant');
  var blocHebdo = document.getElementById('blocHebdo');
  var blocAnniv = document.getElementById('blocAnniversaire');
  var montantHebdo = <?= (int) $montantHebdo ?>;
  var montantAnniv = <?= $campagneAnniversaire ? (int) $campagneAnniversaire['montant_fixe'] : 0 ?>;
  var campagneAnnivId = <?= $campagneAnniversaire ? (int) $campagneAnniversaire['id'] : 'null' ?>;
  var campagneDecesId = <?= $campagneDeces ? (int) $campagneDeces['id'] : 'null' ?>;

  choix.forEach(function (card) {
    card.addEventListener('click', function () {
      if (card.classList.contains('disabled')) return;
      choix.forEach(function (c) { c.classList.remove('selected'); });
      card.classList.add('selected');
      var type = card.getAttribute('data-type');
      typeInput.value = type;
      if (type === 'hebdo') {
        campagneInput.value = '';
        montantInput.value = montantHebdo;
        blocHebdo.style.display = '';
        blocAnniv.style.display = 'none';
        label.textContent = 'Montant total versé (FCFA)';
        hint.textContent = 'La ventilation validera automatiquement les semaines consécutives impayées les plus anciennes.';
      } else if (type === 'anniversaire') {
        campagneInput.value = campagneAnnivId;
        montantInput.value = montantAnniv;
        blocHebdo.style.display = 'none';
        blocAnniv.style.display = '';
        label.textContent = 'Montant fixe (FCFA)';
        hint.textContent = 'Montant identique pour chaque apprenant.';
      } else {
        campagneInput.value = campagneDecesId;
        montantInput.value = '';
        blocHebdo.style.display = 'none';
        blocAnniv.style.display = 'none';
        label.textContent = 'Don libre (FCFA)';
        hint.textContent = 'Montant libre selon la volonté et les moyens de l\'apprenant.';
      }
    });
  });
})();
</script>
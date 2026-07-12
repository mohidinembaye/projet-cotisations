<div class="hero" style="margin-bottom:18px;">
  <div class="hero-eyebrow">RECOUVREMENT</div>
  <div class="hero-title">Relancez vos retardataires.</div>
  <div class="hero-stats">
    <div>
      <div class="hero-stat-label">EN RETARD</div>
      <div class="hero-stat-value red"><?= count($retardataires) ?></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-title">Apprenants en retard</div>
  <div class="table-scroll">
    <table>
      <thead>
        <tr><th>APPRENANT</th><th>SEMAINES DE RETARD</th><th>MONTANT DÛ</th><th>DERNIÈRE RELANCE</th><th style="text-align:right;">ACTION</th></tr>
      </thead>
      <tbody>
        <?php foreach ($retardataires as $r): $a = $r['apprenant']; ?>
          <tr>
            <td class="row-user">
              <span class="avatar-sm"><?= e(initials($a['prenom'], $a['nom'])) ?></span>
              <?= e($a['prenom'] . ' ' . $a['nom']) ?>
            </td>
            <td><span class="badge red"><?= e($r['nb_semaines_retard']) ?></span></td>
            <td class="cell-primary text-red"><?= money($r['montant_du']) ?></td>
            <td class="cell-muted"><?= $r['derniere_relance'] ? fdate($r['derniere_relance']['date_envoi'], 'd/m/Y à H:i') : 'Jamais relancé' ?></td>
            <td style="text-align:right;">
              <form method="post" action="/gerant/relances/marquer">
                <?= csrf_field() ?>
                <input type="hidden" name="apprenant_id" value="<?= e($a['id']) ?>">
                <button type="submit" class="btn btn-outline">&#128276; Relancer</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$retardataires): ?>
          <tr><td colspan="5" class="cell-muted">Aucun retard à ce jour — la classe est à jour.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
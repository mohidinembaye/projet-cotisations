<div class="grid grid-2">
  <div>
    <div class="hero" style="margin-bottom:18px;">
      <div class="hero-eyebrow">TRÉSORERIE &amp; PERFORMANCE</div>
      <div class="hero-title">Suivi financier en temps réel.</div>
      <div class="hero-stats">
        <div>
          <div class="hero-stat-label">TRÉSORERIE TOTALE</div>
          <div class="hero-stat-value"><?= money($tresorerie) ?></div>
        </div>
        <div>
          <div class="hero-stat-label">RECOUVREMENT</div>
          <div class="hero-stat-value <?= $recouvrement >= 70 ? 'green' : 'red' ?>"><?= e($recouvrement) ?>%</div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="flex-between" style="margin-bottom:12px;">
        <div class="card-title" style="margin:0;">Suivi Hebdomadaire — Vue croisée</div>
        <span class="cell-muted" style="font-size:12px;">S1 &#8594; S<?= count($semaines) ?></span>
      </div>
      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th>APPRENANT</th>
              <th>S1–S<?= count($semaines) ?></th>
              <th style="text-align:right;">TOTAL</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($lignes as $ligne): $a = $ligne['apprenant']; ?>
              <tr>
                <td class="row-user">
                  <span class="avatar-sm"><?= e(initials($a['prenom'], $a['nom'])) ?></span>
                  <a href="/gerant/paiements/create?apprenant_id=<?= e($a['id']) ?>"><?= e($a['prenom'] . ' ' . $a['nom']) ?></a>
                </td>
                <td>
                  <div class="dot-row">
                    <?php foreach ($ligne['cellules'] as $etat): ?>
                      <span class="dot-sm <?= e($etat) ?>"></span>
                    <?php endforeach; ?>
                  </div>
                </td>
                <td style="text-align:right;" class="cell-primary"><?= money($ligne['total_paye']) ?></td>
              </tr>
            <?php endforeach; ?>
            <?php if (!$lignes): ?>
              <tr><td colspan="3" class="cell-muted">Aucun apprenant enregistré pour le moment.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div>
    <div class="stat-tile <?= $nbRetards > 0 ? 'tile-red' : 'tile-green' ?>" style="margin-bottom:14px;">
      <div class="tile-top">
        <div>
          <div class="tile-value <?= $nbRetards > 0 ? 'text-red' : 'text-green' ?>"><?= e(str_pad((string) $nbRetards, 2, '0', STR_PAD_LEFT)) ?> Retard<?= $nbRetards > 1 ? 's' : '' ?></div>
          <div class="tile-label">
            <?= $nbRetards > 0 ? 'Relances prioritaires à traiter.' : 'Aucun retard, bravo à la classe !' ?>
          </div>
        </div>
        <a href="/gerant/relances" class="badge <?= $nbRetards > 0 ? 'red' : 'green' ?>"><?= $nbRetards > 0 ? 'CRITIQUE' : 'À JOUR' ?></a>
      </div>
    </div>

    <?php if ($campagneAnniversaireActive): ?>
      <div class="stat-tile tile-green" style="margin-bottom:18px;">
        <div class="tile-top">
          <div>
            <div style="font-weight:700; font-size:14px;">&#127874; <?= e($campagneAnniversaireActive['nom']) ?></div>
            <div class="tile-label" style="margin-top:2px;">Échéance le <?= fdate($campagneAnniversaireActive['date_limite']) ?>.</div>
          </div>
          <span class="badge green">ACTIF</span>
        </div>
      </div>
    <?php endif; ?>

    <div class="card" style="margin-bottom:18px;">
      <div class="card-title">&#128200; Analytique</div>
      <div class="cell-muted" style="margin-bottom:6px;">RECOUVREMENT</div>
      <div class="progress-bar">
        <div class="progress-bar-fill" style="width:<?= e(min(100, max(0, $recouvrement))) ?>%;"></div>
      </div>
      <div style="text-align:right; font-size:12px; font-weight:700; margin-top:4px;"><?= e($recouvrement) ?>%</div>
    </div>

    <div class="card">
      <div class="card-title">&#128225; Campagnes actives</div>
      <ul class="notif-list">
        <?php foreach ($campagnesActives as $c): ?>
          <li class="cell-muted" style="border:none; padding:6px 0;">
            <?= $c['type'] === 'anniversaire' ? '&#127874;' : ($c['type'] === 'deces' ? '&#128153;' : '&#8943;') ?>
            <?= e($c['nom']) ?> — échéance <?= fdate($c['date_limite']) ?>
          </li>
        <?php endforeach; ?>
        <?php if (!$campagnesActives): ?>
          <li class="cell-muted" style="border:none; padding:6px 0;">Aucune campagne active.</li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</div>
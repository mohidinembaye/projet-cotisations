<?php
/**
 * SemaineModel — Cotisations hebdomadaires fixes.
 * Lit/écrit exclusivement les tableaux de session "semaines" et
 * "paiements_hebdo" et "soldes_hebdo".
 */

/**
 * Génère le calendrier des ~40 semaines de la formation (10 mois) une seule
 * fois, à partir de config.date_debut (un lundi). Pour chaque semaine :
 *  - date_debut (lundi)
 *  - date_limite (samedi 00h00 de la même semaine — au-delà = retard)
 *  - mois (Y-m) et est_derniere_semaine_du_mois (utilisé pour programmer
 *    automatiquement les campagnes "Anniversaire").
 */
function semaine_generer_calendrier(): void
{
    if (session_has('semaines')) {
        return;
    }

    $config = session_get('config');
    $debut = new DateTime($config['date_debut']);
    $nbSemaines = (int) $config['nb_semaines'];

    $semaines = [];
    for ($i = 0; $i < $nbSemaines; $i++) {
        $lundi = (clone $debut)->modify("+{$i} week");
        $samedi = (clone $lundi)->modify('+5 days'); // lundi + 5 = samedi
        $mois = $lundi->format('Y-m');

        $semaines[] = [
            'id' => $i + 1,
            'numero' => $i + 1,
            'date_debut' => $lundi->format('Y-m-d'),
            'date_limite' => $samedi->format('Y-m-d') . ' 00:00:00',
            'mois' => $mois,
        ];
    }

    // Marque la dernière semaine de chaque mois calendaire couvert.
    $moisVus = [];
    for ($i = count($semaines) - 1; $i >= 0; $i--) {
        $mois = $semaines[$i]['mois'];
        $semaines[$i]['est_derniere_semaine_du_mois'] = !isset($moisVus[$mois]);
        $moisVus[$mois] = true;
    }

    session_set('semaines', $semaines);
}

function semaine_all(): array
{
    return session_get('semaines', []);
}

function semaine_find(int $id): ?array
{
    foreach (semaine_all() as $s) {
        if ((int) $s['id'] === $id) {
            return $s;
        }
    }
    return null;
}

/** Semaine correspondant à la date du jour (ou null si hors calendrier). */
function semaine_courante(): ?array
{
    $today = date('Y-m-d');
    foreach (semaine_all() as $s) {
        $finSemaine = (clone new DateTime($s['date_debut']))->modify('+6 days')->format('Y-m-d');
        if ($today >= $s['date_debut'] && $today <= $finSemaine) {
            return $s;
        }
    }
    return null;
}

/** Une semaine est en retard si sa date limite (samedi 00h00) est dépassée et qu'elle n'est pas payée. */
function semaine_est_en_retard(array $semaine): bool
{
    return strtotime($semaine['date_limite']) < time();
}

/** Le dernier mois dont la dernière semaine est déjà passée ou en cours. */
function semaine_dernieres_du_mois_courant(): ?array
{
    $today = date('Y-m-d');
    foreach (semaine_all() as $s) {
        if (!$s['est_derniere_semaine_du_mois']) {
            continue;
        }
        $finSemaine = (clone new DateTime($s['date_debut']))->modify('+6 days')->format('Y-m-d');
        if ($today >= $s['date_debut'] && $today <= $finSemaine) {
            return $s;
        }
    }
    return null;
}

/* ------------------------------------------------------------------ */
/* Paiements hebdomadaires                                             */
/* ------------------------------------------------------------------ */

function paiement_hebdo_all(): array
{
    return session_get('paiements_hebdo', []);
}

function paiement_hebdo_pour(int $apprenantId): array
{
    return array_values(array_filter(paiement_hebdo_all(), fn ($p) => (int) $p['apprenant_id'] === $apprenantId));
}

function paiement_hebdo_est_payee(int $apprenantId, int $semaineId): bool
{
    foreach (paiement_hebdo_pour($apprenantId) as $p) {
        if ((int) $p['semaine_id'] === $semaineId) {
            return true;
        }
    }
    return false;
}

/** Semaines impayées d'un apprenant, triées par numéro croissant. */
function semaine_impayees_pour(int $apprenantId): array
{
    $payees = array_map(fn ($p) => (int) $p['semaine_id'], paiement_hebdo_pour($apprenantId));
    $toutes = semaine_all();
    $impayees = array_values(array_filter($toutes, fn ($s) => !in_array((int) $s['id'], $payees, true)));
    usort($impayees, fn ($a, $b) => $a['numero'] <=> $b['numero']);
    return $impayees;
}

/**
 * Ventilation automatique : impute un versement global sur les semaines
 * consécutives impayées les plus anciennes, dans l'ordre chronologique.
 * Le reliquat insuffisant pour couvrir une semaine complète est conservé en
 * solde crédit (soldes_hebdo) réutilisable au prochain versement.
 *
 * @return array{semaines_payees: array<int>, montant_utilise: float, reliquat: float}
 */
function paiement_hebdo_ventiler(int $apprenantId, float $montantVerse): array
{
    $config = session_get('config');
    $montantHebdo = (float) $config['montant_hebdo'];

    $soldes = session_get('soldes_hebdo', []);
    $creditDisponible = (float) ($soldes[$apprenantId] ?? 0);

    $enveloppe = $montantVerse + $creditDisponible;
    $impayees = semaine_impayees_pour($apprenantId);

    $semainesPayees = [];
    $montantUtilise = 0.0;

    foreach ($impayees as $semaine) {
        if ($enveloppe < $montantHebdo) {
            break;
        }
        session_collection_push('paiements_hebdo', [
            'apprenant_id' => $apprenantId,
            'semaine_id' => (int) $semaine['id'],
            'montant' => $montantHebdo,
            'date_paiement' => date('Y-m-d H:i:s'),
        ]);
        $enveloppe -= $montantHebdo;
        $montantUtilise += $montantHebdo;
        $semainesPayees[] = (int) $semaine['numero'];
    }

    $soldes[$apprenantId] = round($enveloppe, 2);
    session_set('soldes_hebdo', $soldes);

    return [
        'semaines_payees' => $semainesPayees,
        'montant_utilise' => $montantUtilise,
        'reliquat' => round($enveloppe, 2),
    ];
}

/** Nombre de semaines en retard (échues et impayées) pour un apprenant, à date. */
function apprenant_nb_semaines_retard(int $apprenantId): int
{
    $count = 0;
    foreach (semaine_impayees_pour($apprenantId) as $semaine) {
        if (semaine_est_en_retard($semaine)) {
            $count++;
        }
    }
    return $count;
}

/** Total dû (hebdo uniquement) à ce jour pour un apprenant : semaines échues impayées. */
function apprenant_montant_du_hebdo(int $apprenantId): float
{
    $config = session_get('config');
    return $config['montant_hebdo'] * apprenant_nb_semaines_retard($apprenantId);
}
<?php
/**
 * CampagneModel — Cotisations ponctuelles (anniversaire, décès, autre).
 * Lit/écrit exclusivement les collections de session "campagnes" et
 * "paiements_campagne".
 */

const CAMPAGNE_TYPES = ['anniversaire', 'deces', 'autre'];

/**
 * Ferme automatiquement les campagnes "décès" dont le délai strict de 7
 * jours est dépassé. Appelée à chaque affichage lié aux campagnes pour
 * refléter la règle métier "clôture automatique après 7 jours".
 */
function campagne_cloturer_expirees(): void
{
    $campagnes = session_get('campagnes', []);
    $modifie = false;

    foreach ($campagnes as $index => $campagne) {
        if ($campagne['statut'] !== 'active') {
            continue;
        }
        if (strtotime($campagne['date_limite']) < time()) {
            $campagnes[$index]['statut'] = 'cloturee';
            $modifie = true;
        }
    }

    if ($modifie) {
        session_set('campagnes', $campagnes);
    }
}

function campagne_all(): array
{
    campagne_cloturer_expirees();
    $items = session_get('campagnes', []);
    usort($items, fn ($a, $b) => strtotime($b['created_at']) <=> strtotime($a['created_at']));
    return $items;
}

function campagne_find(int $id): ?array
{
    return session_collection_find('campagnes', $id);
}

function campagne_actives(): array
{
    return array_values(array_filter(campagne_all(), fn ($c) => $c['statut'] === 'active'));
}

/** Y a-t-il déjà une campagne "anniversaire" active pour le mois en cours ? */
function campagne_anniversaire_du_mois_existe(string $mois): bool
{
    foreach (session_get('campagnes', []) as $c) {
        if ($c['type'] === 'anniversaire' && ($c['mois'] ?? null) === $mois && $c['statut'] === 'active') {
            return true;
        }
    }
    return false;
}

/**
 * Crée une campagne selon son type, en appliquant les règles métier :
 *  - anniversaire : montant fixe par apprenant, échéance = dernière semaine du mois.
 *  - deces        : montant libre, délai strict de 7 jours à partir de la création.
 *  - autre        : montant et date limite libres (paramétrables par le Gérant).
 *
 * @return array{0: bool, 1: array|string}
 */
function campagne_creer(array $data): array
{
    $erreurs = validate_campagne($data);
    if ($erreurs) {
        return [false, $erreurs];
    }

    $type = $data['type'];
    $maintenant = date('Y-m-d H:i:s');

    $champs = [
        'type' => $type,
        'nom' => $data['nom'],
        'statut' => 'active',
        'created_at' => $maintenant,
    ];

    if ($type === 'anniversaire') {
        $derniereSemaine = semaine_dernieres_du_mois_courant();
        $mois = $derniereSemaine['mois'] ?? date('Y-m');
        $dateLimite = $derniereSemaine
            ? (clone new DateTime($derniereSemaine['date_debut']))->modify('+6 days')->format('Y-m-d') . ' 23:59:59'
            : (new DateTime('last day of this month 23:59:59'))->format('Y-m-d H:i:s');

        $champs += [
            'montant_fixe' => (float) $data['montant_fixe'],
            'montant_libre' => false,
            'mois' => $mois,
            'date_limite' => $dateLimite,
        ];
    } elseif ($type === 'deces') {
        $champs += [
            'montant_fixe' => null,
            'montant_libre' => true,
            'date_limite' => (new DateTime($maintenant))->modify('+7 days')->format('Y-m-d H:i:s'),
        ];
    } else { // autre
        $champs += [
            'montant_fixe' => (float) $data['montant_fixe'],
            'montant_libre' => false,
            'date_limite' => (new DateTime($data['date_limite']))->format('Y-m-d H:i:s'),
        ];
    }

    $campagne = session_collection_push('campagnes', $champs);
    return [true, $campagne];
}

/* ------------------------------------------------------------------ */
/* Paiements liés aux campagnes ponctuelles                            */
/* ------------------------------------------------------------------ */

function paiement_campagne_all(): array
{
    return session_get('paiements_campagne', []);
}

function paiement_campagne_pour(int $campagneId): array
{
    return array_values(array_filter(paiement_campagne_all(), fn ($p) => (int) $p['campagne_id'] === $campagneId));
}

function paiement_campagne_apprenant_a_paye(int $campagneId, int $apprenantId): bool
{
    foreach (paiement_campagne_pour($campagneId) as $p) {
        if ((int) $p['apprenant_id'] === $apprenantId) {
            return true;
        }
    }
    return false;
}

function paiement_campagne_total(int $campagneId): float
{
    return array_sum(array_column(paiement_campagne_pour($campagneId), 'montant'));
}

function paiement_campagne_creer(int $campagneId, int $apprenantId, float $montant): array
{
    return session_collection_push('paiements_campagne', [
        'campagne_id' => $campagneId,
        'apprenant_id' => $apprenantId,
        'montant' => $montant,
        'date_paiement' => date('Y-m-d H:i:s'),
    ]);
}
<?php
/**
 * GerantController — Cœur fonctionnel de l'Incrément 1.
 * Tableau de bord croisé, gestion des apprenants, campagnes ponctuelles,
 * saisie des paiements (avec ventilation automatique) et relances.
 */

/** Charge le registre avant toute fonctionnalité réservée au gérant. */
function gerant_charger_apprenants(): array
{
    apprenant_initialiser_registre();
    return apprenant_all();
}

/* ------------------------------------------------------------------ */
/* Tableau de bord                                                     */
/* ------------------------------------------------------------------ */

function gerant_dashboard(): void
{
    auth_require_role(['gerant']);

    $config = session_get('config');
    $apprenants = gerant_charger_apprenants();
    $semaines = semaine_all();

    $totalCollecteHebdo = array_sum(array_column(paiement_hebdo_all(), 'montant'));
    $totalCollecteCampagnes = array_sum(array_column(paiement_campagne_all(), 'montant'));
    $tresorerie = $totalCollecteHebdo + $totalCollecteCampagnes;

    $semainesEchues = array_values(array_filter($semaines, fn ($s) => semaine_est_en_retard($s)));
    $totalAttendu = count($semainesEchues) * count($apprenants) * $config['montant_hebdo'];
    $recouvrement = $totalAttendu > 0 ? round(($totalCollecteHebdo / $totalAttendu) * 100) : 100;

    $nbRetards = 0;
    $lignes = [];
    foreach ($apprenants as $apprenant) {
        $retard = apprenant_nb_semaines_retard($apprenant['id']);
        $nbRetards += $retard > 0 ? 1 : 0;
        $totalPaye = array_sum(array_column(paiement_hebdo_pour($apprenant['id']), 'montant'));
        $lignes[] = [
            'apprenant' => $apprenant,
            'retard' => $retard,
            'total_paye' => $totalPaye,
            'cellules' => array_map(
                fn ($s) => paiement_hebdo_est_payee($apprenant['id'], $s['id'])
                    ? 'on'
                    : (semaine_est_en_retard($s) ? 'off' : 'idle'),
                $semaines
            ),
        ];
    }

    $campagneAnniversaireActive = null;
    foreach (campagne_actives() as $c) {
        if ($c['type'] === 'anniversaire') {
            $campagneAnniversaireActive = $c;
            break;
        }
    }

    render('gerant/dashboard', [
        'tresorerie' => $tresorerie,
        'recouvrement' => $recouvrement,
        'nbRetards' => $nbRetards,
        'lignes' => $lignes,
        'semaines' => $semaines,
        'campagneAnniversaireActive' => $campagneAnniversaireActive,
        'campagnesActives' => campagne_actives(),
    ], 'dashboard', 'Tableau de bord — Gérant');
}

/* ------------------------------------------------------------------ */
/* Apprenants                                                          */
/* ------------------------------------------------------------------ */

function gerant_apprenants_index(): void
{
    auth_require_role(['gerant']);

    $recherche = mb_strtolower((string) query('q', ''));
    $apprenants = gerant_charger_apprenants();
    if ($recherche !== '') {
        $apprenants = array_values(array_filter($apprenants, function ($a) use ($recherche) {
            $hay = mb_strtolower($a['prenom'] . ' ' . $a['nom'] . ' ' . $a['email'] . ' ' . $a['matricule']);
            return str_contains($hay, $recherche);
        }));
    }

    render('gerant/apprenants', [
        'apprenants' => $apprenants,
        'recherche' => query('q', ''),
        'erreurs' => session_get('_form_erreurs', []),
        'old' => session_get('_form_old', []),
    ], 'apprenants', 'Apprenants — Gérant');
    session_set('_form_erreurs', []);
    session_set('_form_old', []);
}

function gerant_apprenants_create(): void
{
    auth_require_role(['gerant']);
    require_valid_post();

    $data = [
        'prenom' => post('prenom'),
        'nom' => post('nom'),
        'email' => post('email'),
        'password' => '',
        'date_naissance' => post('date_naissance', null) ?: null,
    ];

    [$succes, $resultat] = apprenant_creer($data, 'manuel');

    if (!$succes) {
        session_set('_form_erreurs', $resultat);
        session_set('_form_old', $data);
        redirect('/gerant/apprenants');
    }

    flash_set('success', "Apprenant « {$resultat['prenom']} {$resultat['nom']} » ajouté (matricule {$resultat['matricule']}, mot de passe temporaire : {$resultat['password_genere']}).");
    redirect('/gerant/apprenants');
}

function gerant_apprenants_passif(): void
{
    auth_require_role(['gerant']);
    require_valid_post();

    $apprenantId = (int) post('apprenant_id');
    $nbSemaines = max(0, (int) post('nb_semaines'));
    $montant = max(0, (float) post('montant'));
    $apprenant = apprenant_find($apprenantId);

    if (!$apprenant || $montant <= 0) {
        flash_set('error', 'Merci de sélectionner un apprenant et un montant valide.');
        redirect('/gerant/apprenants');
    }

    passif_creer($apprenantId, $nbSemaines, $montant, (string) post('notes', ''));
    flash_set('success', "Passif de " . money($montant) . " enregistré pour {$apprenant['prenom']} {$apprenant['nom']}.");
    redirect('/gerant/apprenants');
}

/* ------------------------------------------------------------------ */
/* Campagnes ponctuelles                                               */
/* ------------------------------------------------------------------ */

function gerant_campagnes_index(): void
{
    auth_require_role(['gerant']);

    $campagnes = array_map(function ($c) {
        $c['total_collecte'] = paiement_campagne_total($c['id']);
        $c['nb_contributeurs'] = count(paiement_campagne_pour($c['id']));
        return $c;
    }, campagne_all());

    render('gerant/campagnes', [
        'campagnes' => $campagnes,
        'nbApprenants' => count(gerant_charger_apprenants()),
        'erreurs' => session_get('_form_erreurs', []),
        'old' => session_get('_form_old', []),
        'peutCreerAnniversaire' => !campagne_anniversaire_du_mois_existe(date('Y-m')) && semaine_dernieres_du_mois_courant() !== null,
    ], 'campagnes', 'Campagnes — Gérant');
    session_set('_form_erreurs', []);
    session_set('_form_old', []);
}

function gerant_campagnes_create(): void
{
    auth_require_role(['gerant']);
    require_valid_post();

    $data = [
        'type' => post('type'),
        'nom' => post('nom'),
        'montant_fixe' => post('montant_fixe', null),
        'date_limite' => post('date_limite', null),
    ];

    [$succes, $resultat] = campagne_creer($data);

    if (!$succes) {
        session_set('_form_erreurs', $resultat);
        session_set('_form_old', $data);
        redirect('/gerant/campagnes');
    }

    flash_set('success', "Campagne « {$resultat['nom']} » créée et notifiée à tous les apprenants.");
    redirect('/gerant/campagnes');
}

/* ------------------------------------------------------------------ */
/* Saisie des paiements (avec ventilation automatique)                 */
/* ------------------------------------------------------------------ */

function gerant_paiements_create_show(): void
{
    auth_require_role(['gerant']);

    $apprenantId = (int) query('apprenant_id', 0);
    $apprenant = $apprenantId ? apprenant_find($apprenantId) : null;

    $config = session_get('config');
    $semaines = semaine_all();

    $cellulesSemaines = [];
    if ($apprenant) {
        foreach ($semaines as $s) {
            $cellulesSemaines[] = [
                'semaine' => $s,
                'payee' => paiement_hebdo_est_payee($apprenant['id'], $s['id']),
                'retard' => semaine_est_en_retard($s),
            ];
        }
    }

    render('gerant/saisie-paiement', [
        'apprenants' => gerant_charger_apprenants(),
        'apprenant' => $apprenant,
        'montantHebdo' => $config['montant_hebdo'],
        'cellulesSemaines' => $cellulesSemaines,
        'campagneAnniversaire' => campagne_active_par_type('anniversaire'),
        'campagneDeces' => campagne_active_par_type('deces'),
        'totalCollecteJour' => paiements_total_du_jour(),
        'erreurs' => session_get('_form_erreurs', []),
    ], 'saisie-paiement', 'Saisie Paiement — Gérant');
    session_set('_form_erreurs', []);
}

function campagne_active_par_type(string $type): ?array
{
    foreach (campagne_actives() as $c) {
        if ($c['type'] === $type) {
            return $c;
        }
    }
    return null;
}

/** Somme de tous les paiements (hebdo + campagnes) enregistrés aujourd'hui. */
function paiements_total_du_jour(): float
{
    $today = date('Y-m-d');
    $total = 0.0;
    foreach (paiement_hebdo_all() as $p) {
        if (str_starts_with($p['date_paiement'], $today)) {
            $total += $p['montant'];
        }
    }
    foreach (paiement_campagne_all() as $p) {
        if (str_starts_with($p['date_paiement'], $today)) {
            $total += $p['montant'];
        }
    }
    return $total;
}

function gerant_paiements_create_submit(): void
{
    auth_require_role(['gerant']);
    require_valid_post();

    $data = [
        'apprenant_id' => post('apprenant_id'),
        'type_paiement' => post('type_paiement'),
        'montant' => post('montant'),
        'campagne_id' => post('campagne_id', null),
    ];

    $erreurs = validate_paiement($data);
    if ($erreurs) {
        session_set('_form_erreurs', $erreurs);
        redirect('/gerant/paiements/create?apprenant_id=' . (int) $data['apprenant_id']);
    }

    $apprenantId = (int) $data['apprenant_id'];
    $apprenant = apprenant_find($apprenantId);
    $montant = (float) $data['montant'];

    if ($data['type_paiement'] === 'hebdo') {
        $resultat = paiement_hebdo_ventiler($apprenantId, $montant);
        $nbSemaines = count($resultat['semaines_payees']);
        $liste = $nbSemaines ? implode(', S', $resultat['semaines_payees']) : '';
        $message = $nbSemaines
            ? "Paiement de " . money($montant) . " ventilé automatiquement sur {$nbSemaines} semaine(s) : S{$liste}."
            : "Paiement enregistré en crédit (" . money($resultat['reliquat']) . ") : montant insuffisant pour couvrir une semaine complète.";
    } else {
        $campagneId = (int) $data['campagne_id'];
        paiement_campagne_creer($campagneId, $apprenantId, $montant);
        $campagne = campagne_find($campagneId);
        $message = "Contribution de " . money($montant) . " enregistrée pour la campagne « {$campagne['nom']} ».";
    }

    flash_set('success', "{$apprenant['prenom']} {$apprenant['nom']} — {$message} Une quittance a été générée.");
    redirect('/gerant/paiements/create?apprenant_id=' . $apprenantId);
}

/* ------------------------------------------------------------------ */
/* Relances                                                             */
/* ------------------------------------------------------------------ */

function gerant_relances_index(): void
{
    auth_require_role(['gerant']);

    $retardataires = [];
    foreach (gerant_charger_apprenants() as $apprenant) {
        $retard = apprenant_nb_semaines_retard($apprenant['id']);
        if ($retard > 0) {
            $retardataires[] = [
                'apprenant' => $apprenant,
                'nb_semaines_retard' => $retard,
                'montant_du' => apprenant_montant_du_hebdo($apprenant['id']),
                'derniere_relance' => relance_derniere_pour($apprenant['id']),
            ];
        }
    }

    usort($retardataires, fn ($a, $b) => $b['nb_semaines_retard'] <=> $a['nb_semaines_retard']);

    render('gerant/relances', [
        'retardataires' => $retardataires,
    ], 'relances', 'Relances — Gérant');
}

function relance_all(): array
{
    return session_get('relances', []);
}

function relance_derniere_pour(int $apprenantId): ?array
{
    $dernieres = array_values(array_filter(relance_all(), fn ($r) => (int) $r['apprenant_id'] === $apprenantId));
    if (!$dernieres) {
        return null;
    }
    usort($dernieres, fn ($a, $b) => strtotime($b['date_envoi']) <=> strtotime($a['date_envoi']));
    return $dernieres[0];
}

function gerant_relances_marquer(): void
{
    auth_require_role(['gerant']);
    require_valid_post();

    $apprenantId = (int) post('apprenant_id');
    $apprenant = apprenant_find($apprenantId);
    if (!$apprenant) {
        flash_set('error', 'Apprenant introuvable.');
        redirect('/gerant/relances');
    }

    session_collection_push('relances', [
        'apprenant_id' => $apprenantId,
        'date_envoi' => date('Y-m-d H:i:s'),
        'canal' => 'manuel',
    ]);

    flash_set('success', "Relance envoyée à {$apprenant['prenom']} {$apprenant['nom']}.");
    redirect('/gerant/relances');
}
<?php
/**
 * ApprenantModel — Registre des apprenants de la classe.
 * Lit/écrit exclusivement la collection de session "apprenants".
 */

/**
 * Registre permanent de la classe. Il ne dépend pas de la session : toutes
 * les vues retrouvent donc toujours les apprenants, y compris après une
 * nouvelle connexion ou la suppression de la session.
 */
function apprenant_registre(): array
{
    static $apprenants = null;
    if ($apprenants !== null) {
        return $apprenants;
    }

    $registre = [
        ['Abdou', 'Kebe', 'abdou.kebe@classe.sn'],
        ['Abdoul Hakim', 'Ly', 'abdoul.hakim.ly@classe.sn'],
        ['Abdoulaye', 'Gueye MBAYE', 'abdoulaye.gueye.mbaye@classe.sn'],
        ['Ablaye', 'SARR', 'ablaye.sarr@classe.sn'],
        ['Adama', 'NDIAYE', 'adama.ndiaye@classe.sn'],
        ['Adama', 'TIMERA', 'adama.timera@classe.sn'],
        ['Adja Coura', 'NDOUR', 'adja.coura.ndour@classe.sn'],
        ['Aissatou', 'BARRY', 'aissatou.barry@classe.sn'],
        ['Aïssatou', 'Gueye', 'aissatou.gueye@classe.sn'],
        ['Aïta', 'GUEYE', 'aita.gueye@classe.sn'],
        ['Aminata', 'Sy', 'aminata.sy@classe.sn'],
        ['Assane', 'BADJI', 'assane.badji@classe.sn'],
        ['Assane', 'SIDY', 'assane.sidy@classe.sn'],
        ['Awa', 'SALL', 'awa.sall@classe.sn'],
        ['Babacar', 'Faye', 'babacar.faye@classe.sn'],
        ['Denise Mossane', 'SÈNE', 'denise.mossane.sene@classe.sn'],
        ['Dieynaba', 'LENE', 'dieynaba.lene@classe.sn'],
        ['Fatmata', 'NDIAYE', 'fatmata.ndiaye@classe.sn'],
        ['Fatou', 'NDIAYE', 'fatou.ndiaye@classe.sn'],
        ['Fatoumata Binetou', 'DIA', 'fatoumata.binetou.dia@classe.sn'],
        ['Guitté', 'WADE', 'guitte.wade@classe.sn'],
        ['Idrissa', 'DIOUF', 'idrissa.diouf@classe.sn'],
        ['Khady', 'DIOP', 'khady.diop@classe.sn'],
        ['Khoudia', 'CISSÉ', 'khoudia.cisse@classe.sn'],
        ['Léna', 'SAMBA', 'lena.samba@classe.sn'],
        ['Maïmouna', 'NDAO', 'maimouna.ndao@classe.sn'],
        ['Malang kiya', 'CISSÉ', 'malang.kiya.cisse@classe.sn'],
        ['Mame Fatou', 'SYLLA', 'mame.fatou.sylla@classe.sn'],
        ['Marc Dip', 'FAYE', 'marc.dip.faye@classe.sn'],
        ['Mariama', 'BA', 'mariama.ba@classe.sn'],
        ['Mariama', 'NDOUR', 'mariama.ndour@classe.sn'],
        ['Mbagnick', 'DIOUF', 'mbagnick.diouf@classe.sn'],
        ['Modou', 'FAYE', 'modou.faye@classe.sn'],
        ['Mohidine', 'MBAYE', 'mohidine.mbaye@classe.sn'],
        ['Mouhamadou Fall', 'Bah', 'mouhamadou.fall.bah@classe.sn'],
        ['Mouhamedou Moustapha', 'BA', 'mouhamedou.moustapha.ba@classe.sn'],
        ['Moussa', 'BA', 'moussa.ba@classe.sn'],
        ['Ndeye Aissatou', 'SÈNE', 'ndeye.aissatou.sene@classe.sn'],
        ['Ndeye Fatou', 'NDIOUR', 'ndeye.fatou.ndiour@classe.sn'],
        ['Ndeye Marième Sarr', 'NDONG', 'ndeye.marieme.sarr.ndong@classe.sn'],
        ['Ndiassé', 'MBOW', 'ndiasse.mbow@classe.sn'],
        ['Oumy', 'LO', 'oumy.lo@classe.sn'],
        ['Ousmane', 'Samba', 'ousmane.samba@classe.sn'],
        ['Papa Mamadou', 'DIOUF', 'papa.mamadou.diouf@classe.sn'],
        ['Pape Mamadou Seck', 'DIEYE', 'pape.mamadou.seck.dieye@classe.sn'],
        ['Rougui', 'SY', 'rougui.sy@classe.sn'],
        ['Saly', 'FAYE', 'saly.faye@classe.sn'],
        ['Serigne Abdou Khoudousse', 'MBACKE', 'serigne.abdou.khoudousse.mbacke@classe.sn'],
        ['Serigne Bamba', 'SANKHÉ', 'serigne.bamba.sankhe@classe.sn'],
        ['Serigne Touba Mbacké', 'DIONE', 'serigne.touba.mbacke.dione@classe.sn'],
        ['Seynabou', 'MBAYE', 'seynabou.mbaye@classe.sn'],
        ['Youssou', 'SALL', 'youssou.sall@classe.sn'],
    ];

    $motDePasseHash = password_hash('apprenant123', PASSWORD_DEFAULT);
    $apprenants = [];
    foreach ($registre as $index => [$prenom, $nom, $email]) {
        $apprenants[] = [
            'id' => $index + 1,
            'matricule' => sprintf('%s-MT%02d', date('Y'), $index + 1),
            'prenom' => $prenom,
            'nom' => $nom,
            'email' => $email,
            'password_hash' => $motDePasseHash,
            'source' => 'initialisation',
            'date_naissance' => null,
            'statut' => 'actif',
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    return $apprenants;
}

/** Conserve un point d'appel explicite pour les contrôleurs. */
function apprenant_initialiser_registre(): void
{
    apprenant_registre();
}

function apprenant_all(): array
{
    $items = array_merge(apprenant_registre(), session_get('apprenants_ajoutes', []));
    usort($items, fn ($a, $b) => strcmp($a['nom'] . $a['prenom'], $b['nom'] . $b['prenom']));
    return $items;
}

function apprenant_find(int $id): ?array
{
    foreach (apprenant_all() as $apprenant) {
        if ((int) $apprenant['id'] === $id) {
            return $apprenant;
        }
    }
    return null;
}

function apprenant_find_by_email(string $email): ?array
{
    foreach (apprenant_all() as $a) {
        if (strcasecmp($a['email'], $email) === 0) {
            return $a;
        }
    }
    return null;
}

function apprenant_generer_matricule(): string
{
    $annee = date('Y');
    $identifiants = array_column(apprenant_all(), 'id');
    $numero = $identifiants ? max($identifiants) + 1 : 1;
    return sprintf('%s-MT%02d', $annee, $numero);
}


function apprenant_creer(array $data, string $source = 'manuel'): array
{
    $erreurs = validate_apprenant($data, apprenant_all());
    if ($erreurs) {
        return [false, $erreurs];
    }

    $motDePasse = $data['password'] !== '' ? $data['password'] : bin2hex(random_bytes(4));

    $apprenantsAjoutes = session_get('apprenants_ajoutes', []);
    $identifiants = array_column(apprenant_all(), 'id');
    $nouvelId = $identifiants ? max($identifiants) + 1 : 1;
    $apprenant = [
        'id' => $nouvelId,
        'matricule' => apprenant_generer_matricule(),
        'prenom' => $data['prenom'],
        'nom' => $data['nom'],
        'email' => mb_strtolower($data['email']),
        'password_hash' => password_hash($motDePasse, PASSWORD_DEFAULT),
        'source' => $source,
        'date_naissance' => $data['date_naissance'] ?? null,
        'statut' => 'actif',
        'created_at' => date('Y-m-d H:i:s'),
    ];
    $apprenantsAjoutes[] = $apprenant;
    session_set('apprenants_ajoutes', $apprenantsAjoutes);

    return [true, $apprenant + ['password_genere' => $motDePasse]];
}

/** Statut d'avancement d'un apprenant pour l'affichage (à jour / retard). */
function apprenant_statut_libelle(int $apprenantId): array
{
    $retard = apprenant_nb_semaines_retard($apprenantId);
    if ($retard === 0) {
        return ['label' => 'À jour', 'classe' => 'green'];
    }
    return ['label' => "Retard ({$retard})", 'classe' => 'red'];
}

/* ------------------------------------------------------------------ */
/* Passifs (dettes / historique antérieur à l'application)             */
/* ------------------------------------------------------------------ */

function passif_all(): array
{
    return session_get('passifs', []);
}

function passif_pour(int $apprenantId): array
{
    return array_values(array_filter(passif_all(), fn ($p) => (int) $p['apprenant_id'] === $apprenantId));
}

function passif_total_pour(int $apprenantId): float
{
    return array_sum(array_column(passif_pour($apprenantId), 'montant'));
}

function passif_creer(int $apprenantId, int $nbSemaines, float $montant, string $notes = ''): array
{
    return session_collection_push('passifs', [
        'apprenant_id' => $apprenantId,
        'nb_semaines' => $nbSemaines,
        'montant' => $montant,
        'notes' => $notes,
        'created_at' => date('Y-m-d H:i:s'),
    ]);
}
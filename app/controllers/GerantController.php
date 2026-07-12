<?php
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/ApprenantModel.php';
require_once __DIR__ . '/../models/CampagneModel.php';
require_once __DIR__ . '/../models/PaiementModel.php';
require_once __DIR__ . '/../models/SemaineModel.php';
require_once __DIR__ . '/../validators/ApprenantValidator.php';
require_once __DIR__ . '/../validators/CampagneValidator.php';
require_once __DIR__ . '/../validators/PaiementValidator.php';
require_once __DIR__ . '/../models/NotificationModel.php';

function gerantDashboard()
{
    verifierRole('gerant');
    $erreurs = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verifier();
        $montant = filter_var($_POST['montant_hebdo'] ?? null, FILTER_VALIDATE_FLOAT);
        $nombre = filter_var($_POST['nombre_semaines'] ?? null, FILTER_VALIDATE_INT);

        if ($montant === false || $montant <= 0 || $nombre === false || $nombre < 1 || $nombre > 52) {
            $erreurs[] = 'Le montant et le nombre de semaines doivent être valides.';
        } else {
            parametrerCotisation($montant, $nombre);
            flash_set('success', 'Cotisation hebdomadaire paramétrée.');
            rediriger('/gerant/dashboard');
        }
    }

    $apprenants = getApprenants();
    $paiements = getPaiements();
    $configuration = getConfigurationCotisation();
    $total = array_sum(array_column($paiements, 'montant'));
    $attendu = count($apprenants) * $configuration['montant'] * $configuration['nombre'];
    $recouvrement = $attendu > 0 ? round(($total / $attendu) * 100, 1) : 0;
    $campagnes = getCampagnes();
    $flash = flash_get();

    require __DIR__ . '/../views/gerant/dashboard.php';
}

function gerantApprenants()
{
    verifierRole('gerant');
    $erreurs = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verifier();
        $action = $_POST['action'] ?? '';

        if ($action === 'ajouter') {
            $erreurs = validerApprenant($_POST);
            if (!$erreurs) {
                ajouterApprenant([
                    'prenom' => trim($_POST['prenom']),
                    'nom' => trim($_POST['nom']),
                    'email' => strtolower(trim($_POST['email'])),
                ]);
                flash_set('success', 'Apprenant ajouté au registre.');
                rediriger('/gerant/apprenants');
            }
        }
        if ($action === 'modifier') {
            $id = $_POST['apprenant_id'] ?? '';
            $erreurs = validerApprenant($_POST);
            if (!trouverApprenant($id)) {
                $erreurs[] = 'Apprenant introuvable.';
            }
            if (!$erreurs) {
                modifierApprenant($id, [
                    'prenom' => trim($_POST['prenom']),
                    'nom' => trim($_POST['nom']),
                    'email' => strtolower(trim($_POST['email'])),
                ]);
                flash_set('success', 'Apprenant modifié.');
                rediriger('/gerant/apprenants');
            }
        }

        if ($action === 'supprimer') {
            $id = $_POST['apprenant_id'] ?? '';
            if (!supprimerApprenant($id)) {
                $erreurs[] = 'Apprenant introuvable.';
            } else {
                flash_set('success', 'Apprenant supprimé.');
                rediriger('/gerant/apprenants');
            }
        }

        if ($action === 'importer') {
            if (empty($_FILES['fichier_csv']['tmp_name']) || $_FILES['fichier_csv']['error'] !== UPLOAD_ERR_OK) {
                $erreurs[] = 'Sélectionnez un fichier CSV valide.';
            } else {
                $ajoutes = importerApprenantsCsv($_FILES['fichier_csv']['tmp_name']);
                flash_set('success', $ajoutes . ' apprenant(s) importé(s).');
                rediriger('/gerant/apprenants');
            }
        }

        if ($action === 'passif') {
        }
    }

    $apprenants = getApprenants();
    $flash = flash_get();
    require __DIR__ . '/../views/gerant/apprenants.php';
}

function gerantCampagnes()
{
    verifierRole('gerant');
    $erreurs = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verifier();
        $erreurs = validerCampagne($_POST);

        if (!$erreurs) {
            $type = $_POST['type'];
            $limite = $type === 'deces'
                ? date('c', strtotime('+7 days'))
                : ($_POST['date_limite'] . ' 23:59:59');

            ajouterCampagne([
                'type' => $type,
                'nom' => trim($_POST['nom']),
                'montant' => $type === 'deces' ? null : (float) $_POST['montant'],
                'date_limite' => $limite,
            ]);
            $campagne = ajouterCampagne([
                'type' => $type,
                'nom' => trim($_POST['nom']),
                'montant' => $type === 'deces' ? null : (float) $_POST['montant'],
                'date_limite' => $limite,
            ]);
            ajouterNotification('tous', 'Nouvelle campagne : ' . $campagne['nom']);

            flash_set('success', 'Campagne créée.');
            rediriger('/gerant/dashboard');

            flash_set('success', 'Campagne créée.');
            rediriger('/gerant/dashboard');
        }
    }

    $campagnes = getCampagnes();
    $flash = flash_get();
    require __DIR__ . '/../views/gerant/campagnes.php';
}

function gerantPaiement()
{
    verifierRole('gerant');
    $erreurs = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        csrf_verifier();
        $erreurs = validerPaiement($_POST);

        if (!$erreurs) {
            $montant = (float) $_POST['montant'];
            $type = $_POST['type'] ?? 'hebdomadaire';
            $semaines = $type === 'hebdomadaire' ? ventilerCotisation($_POST['apprenant_id'], $montant) : [];

            $campagneId = null;
            if ($type !== 'hebdomadaire') {
                $campagne = trouverCampagneOuverte($type);
                $campagneId = $campagne['id'] ?? null;
            }

            ajouterPaiement([
                'apprenant_id' => $_POST['apprenant_id'],
                'montant' => $montant,
                'type' => $type,
                'campagne_id' => $campagneId,
                'semaines' => $semaines,
            ]);

            $message = 'Paiement enregistré' . ($semaines ? ' : semaines S' . implode(', S', $semaines) . ' validées.' : '.');
            flash_set('success', $message);
            rediriger('/gerant/dashboard');
        }
    }

    $apprenants = getApprenants();
    $configuration = getConfigurationCotisation();
    $flash = flash_get();
    require __DIR__ . '/../views/gerant/saisie-paiement.php';
}
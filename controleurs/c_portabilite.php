<?php
require_once __DIR__ . '/../portabilité/bd.portabilite.inc.php';

$idMedecinConnecte = $_SESSION['idMedecin'] ?? $_SESSION['id'] ?? null;
$action = $_REQUEST['action'] ?? 'demande';
$vue = 'vues/v_portabilite.php';

if ($action === 'telecharger') {
    $export = $_SESSION['exportPortabilite'] ?? null;
    if (
        !is_array($export)
        || !isset($export['nom_fichier'], $export['contenu'])
        || !preg_match('/^\d+\.json$/D', $export['nom_fichier'])
        || !is_string($export['contenu'])
    ) {
        http_response_code(404);
        exit('Aucun fichier de portabilité disponible. Veuillez le générer à nouveau.');
    }

    header('Content-Type: application/json; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $export['nom_fichier'] . '"');
    header('Content-Length: ' . strlen($export['contenu']));
    echo $export['contenu'];
    exit;
}

switch ($action) {
    case 'demande':
        break;

    case 'generer':
        if (isset($_POST['confirmation']) && $_POST['confirmation'] === 'oui') {
            try {
                if (!is_scalar($idMedecinConnecte) || !ctype_digit((string) $idMedecinConnecte)) {
                    throw new RuntimeException("Impossible d'identifier le médecin connecté.");
                }

                $resultatExport = genererFichierJsonPortabilite($pdo, $idMedecinConnecte);
                $_SESSION['exportPortabilite'] = $resultatExport;
                $succes = true;
            } catch (Exception $e) {
                $erreur = $e->getMessage();
            }
        }
        break;

    case 'verifier':
        $vue = 'vues/v_verifier_integrite.php';
        break;
}

include $vue;
?>
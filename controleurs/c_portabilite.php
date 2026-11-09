<?php
require_once __DIR__ . '/../portabilité/bd.portabilite.inc.php';

$idMedecinConnecte = $_SESSION['idMedecin'] ?? $_SESSION['id'] ?? null;
$action = $_REQUEST['action'] ?? 'demande';
$vue = 'vues/v_portabilite.php';

switch ($action) {
    case 'demande':
        break;

    case 'generer':
        if (isset($_POST['confirmation']) && $_POST['confirmation'] === 'oui') {
            try {
                // Appel de la fonction 2 qui génère le JSON
                $resultatExport = genererFichierJsonPortabilite($pdo, $idMedecinConnecte);
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
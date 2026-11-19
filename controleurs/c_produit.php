<?php

$vue = 'vues/v_produit.php';
$lesProduits = array();
$produitAModifier = null;
$valeursProduit = array(
    'nom' => '',
    'objectif' => '',
    'information' => '',
    'effetIndesirable' => ''
);
$message = null;
$erreur = null;

if (empty($_SESSION['id'])) {
    header('Location: index.php?uc=connexion');
    exit;
}

/** @var PdoGsb $pdo */
$pdo = PdoGsb::getPdoGsb();

if (isset($_SESSION['messageProduit'])) {
    $message = $_SESSION['messageProduit'];
    unset($_SESSION['messageProduit']);
}

$action = isset($_GET['action']) && is_string($_GET['action'])
    ? $_GET['action']
    : 'liste';
$idSaisi = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? (isset($_POST['id']) ? $_POST['id'] : '')
    : (isset($_GET['id']) ? $_GET['id'] : '');
$id = is_string($idSaisi) ? filter_var($idSaisi, FILTER_VALIDATE_INT) : false;

$formulaireValide = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valeursProduit = array(
        'nom' => isset($_POST['nom']) && is_string($_POST['nom']) ? trim($_POST['nom']) : '',
        'objectif' => isset($_POST['objectif']) && is_string($_POST['objectif']) ? trim($_POST['objectif']) : '',
        'information' => isset($_POST['information']) && is_string($_POST['information']) ? trim($_POST['information']) : '',
        'effetIndesirable' => isset($_POST['effetIndesirable']) && is_string($_POST['effetIndesirable']) ? trim($_POST['effetIndesirable']) : ''
    );

    $formulaireValide = $valeursProduit['nom'] !== ''
        && preg_match('/^.{1,60}$/u', $valeursProduit['nom']) === 1
        && $valeursProduit['objectif'] !== ''
        && $valeursProduit['information'] !== ''
        && $valeursProduit['effetIndesirable'] !== '';
}

try {
    switch ($action) {
        case 'ajouter':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $erreur = "Le formulaire d'ajout doit être envoyé.";
            } elseif (!$formulaireValide) {
                $erreur = 'Renseignez tous les champs. Le nom est limité à 60 caractères.';
            } elseif (!$pdo->insérerProduit(
                $valeursProduit['nom'],
                $valeursProduit['objectif'],
                $valeursProduit['information'],
                $valeursProduit['effetIndesirable']
            )) {
                $erreur = "Le produit n'a pas pu être ajouté.";
            } else {
                $_SESSION['messageProduit'] = 'Le produit a été ajouté.';
                header('Location: index.php?uc=produit');
                exit;
            }
            break;

        case 'modifier':
            if ($id === false || $id < 1) {
                $erreur = 'Le numéro du produit est invalide.';
                break;
            }

            $produitAModifier = $pdo->donneProduitById($id);
            if ($produitAModifier === null) {
                $erreur = 'Le produit demandé est introuvable.';
                break;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $produitAModifier = array_merge(array('id' => $id), $valeursProduit);
                if (!$formulaireValide) {
                    $erreur = 'Renseignez tous les champs. Le nom est limité à 60 caractères.';
                } elseif (!$pdo->modifierProduit(
                    $id,
                    $valeursProduit['nom'],
                    $valeursProduit['objectif'],
                    $valeursProduit['information'],
                    $valeursProduit['effetIndesirable']
                )) {
                    $erreur = 'Le produit n’a pas pu être modifié.';
                } else {
                    $_SESSION['messageProduit'] = 'Le produit a été modifié.';
                    header('Location: index.php?uc=produit');
                    exit;
                }
            } else {
                $valeursProduit = $produitAModifier;
            }
            break;

        case 'supprimer':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $erreur = 'La suppression doit être envoyée par formulaire.';
            } elseif ($id === false || $id < 1) {
                $erreur = 'Le numéro du produit est invalide.';
            } elseif ($pdo->donneProduitById($id) === null) {
                $erreur = 'Le produit demandé est introuvable.';
            } elseif (!$pdo->supprimerProduit($id)) {
                $erreur = 'Le produit n’a pas pu être supprimé.';
            } else {
                $_SESSION['messageProduit'] = 'Le produit a été supprimé.';
                header('Location: index.php?uc=produit');
                exit;
            }
            break;

        case 'liste':
            break;

        default:
            $erreur = 'Cette action sur les produits est inconnue.';
            break;
    }

    $lesProduits = $pdo->donneLesProduits();
} catch (Exception $exception) {
    $erreur = $exception->getMessage();
}

include $vue;
?>

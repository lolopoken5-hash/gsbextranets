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

$action = !isset($_REQUEST['action'])
    ? 'liste'
    : (is_string($_REQUEST['action']) ? $_REQUEST['action'] : '');
$validerId = function ($valeur) {
    if (!is_string($valeur) && !is_int($valeur)) {
        return null;
    }
    $id = filter_var($valeur, FILTER_VALIDATE_INT, array(
        'options' => array('min_range' => 1)
    ));
    return $id === false ? null : $id;
};
$lireValeursProduit = function () use ($valeursProduit) {
    $valeurs = $valeursProduit;
    foreach (array('nom', 'objectif', 'information', 'effetIndesirable') as $champ) {
        $valeurs[$champ] = isset($_POST[$champ]) && is_string($_POST[$champ])
            ? trim($_POST[$champ])
            : '';
    }
    return $valeurs;
};
$lireChampsProduit = function () {
    $champs = array();
    foreach (array('nom', 'objectif', 'information', 'effetIndesirable') as $champ) {
        if (!isset($_POST[$champ]) || !is_string($_POST[$champ])) {
            return null;
        }
        $champs[$champ] = trim($_POST[$champ]);
        if ($champs[$champ] === '') {
            return null;
        }
    }

    if (preg_match('/^.{1,60}$/u', $champs['nom']) !== 1) {
        return null;
    }

    return $champs;
};

try {
    switch ($action) {
        case 'ajouter':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $erreur = "La création d'un produit doit être envoyée par formulaire.";
                break;
            }

            $champs = $lireChampsProduit();
            if ($champs === null) {
                $erreur = 'Tous les champs sont obligatoires et le nom doit contenir au maximum 60 caractères.';
                $valeursProduit = $lireValeursProduit();
                break;
            }

            if (!$pdo->insérerProduit(
                $champs['nom'],
                $champs['objectif'],
                $champs['information'],
                $champs['effetIndesirable']
            )) {
                $erreur = "Le produit n'a pas pu être ajouté.";
                $valeursProduit = $champs;
                break;
            }

            $_SESSION['messageProduit'] = 'Le produit a été ajouté.';
            header('Location: index.php?uc=produit');
            exit;

        case 'modifier':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = $validerId(isset($_POST['id']) ? $_POST['id'] : null);
                $champs = $lireChampsProduit();

                if ($id === null || $champs === null) {
                    $erreur = 'Les données du produit sont invalides.';
                    $produitAModifier = array_merge(
                        array('id' => isset($_POST['id']) && is_string($_POST['id']) ? $_POST['id'] : ''),
                        $lireValeursProduit()
                    );
                    break;
                }

                if ($pdo->donneProduitById($id) === null) {
                    $erreur = 'Le produit demandé est introuvable.';
                    break;
                }

                if (!$pdo->modifierProduit(
                    $id,
                    $champs['nom'],
                    $champs['objectif'],
                    $champs['information'],
                    $champs['effetIndesirable']
                )) {
                    $erreur = 'Le produit n’a pas pu être modifié.';
                    $produitAModifier = array_merge(array('id' => $id), $champs);
                    break;
                }

                $_SESSION['messageProduit'] = 'Le produit a été modifié.';
                header('Location: index.php?uc=produit');
                exit;
            }

            $id = $validerId(isset($_GET['id']) ? $_GET['id'] : null);
            if ($id === null) {
                $erreur = 'Le produit demandé est invalide.';
                break;
            }

            $produitAModifier = $pdo->donneProduitById($id);
            if ($produitAModifier === null) {
                $erreur = 'Le produit demandé est introuvable.';
                break;
            }
            $valeursProduit = $produitAModifier;
            break;

        case 'supprimer':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $erreur = 'La suppression doit être envoyée par formulaire.';
                break;
            }

            $id = $validerId(isset($_POST['id']) ? $_POST['id'] : null);
            if ($id === null) {
                $erreur = 'Le produit demandé est invalide.';
                break;
            }

            if ($pdo->donneProduitById($id) === null) {
                $erreur = 'Le produit demandé est introuvable.';
                break;
            }

            if (!$pdo->supprimerProduit($id)) {
                $erreur = 'Le produit n’a pas pu être supprimé.';
                break;
            }

            $_SESSION['messageProduit'] = 'Le produit a été supprimé.';
            header('Location: index.php?uc=produit');
            exit;

        case 'liste':
        case 'demande':
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
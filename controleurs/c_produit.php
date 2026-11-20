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
$nouvelleImage = null;

function televerserImageProduit($fichier)
{
    if (!isset($fichier['error']) || $fichier['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($fichier['error'] !== UPLOAD_ERR_OK
        || !isset($fichier['tmp_name'], $fichier['size'])
        || !is_uploaded_file($fichier['tmp_name'])) {
        throw new Exception("Le téléversement de l'image a échoué.");
    }

    if ($fichier['size'] > 2 * 1024 * 1024) {
        throw new Exception("L'image ne doit pas dépasser 2 Mo.");
    }

    $informationImage = @getimagesize($fichier['tmp_name']);
    if ($informationImage === false) {
        throw new Exception("Le fichier envoyé n'est pas une image valide.");
    }

    if ($informationImage['mime'] === 'image/jpeg') {
        $extension = '.jpg';
    } elseif ($informationImage['mime'] === 'image/png') {
        $extension = '.png';
    } else {
        throw new Exception('Seules les images JPG et PNG sont acceptées.');
    }

    $dossier = __DIR__ . '/../images/produits/';
    if (!is_dir($dossier) && !mkdir($dossier, 0755, true)) {
        throw new Exception("Impossible de créer le dossier des images.");
    }

    $nomFichier = bin2hex(random_bytes(16)) . $extension;
    if (!move_uploaded_file($fichier['tmp_name'], $dossier . $nomFichier)) {
        throw new Exception("Impossible d'enregistrer l'image sur le serveur.");
    }

    return 'images/produits/' . $nomFichier;
}

function supprimerImageProduit($chemin)
{
    if (empty($chemin)) {
        return true;
    }

    if (!preg_match('#^images/produits/[a-f0-9]{32}\.(jpg|png)$#', $chemin)) {
        return false;
    }

    $fichier = __DIR__ . '/../' . $chemin;
    return !file_exists($fichier) || unlink($fichier);
}

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
            } else {
                $nouvelleImage = televerserImageProduit(
                    isset($_FILES['image']) ? $_FILES['image'] : array()
                );
                if ($nouvelleImage === null) {
                    $erreur = 'Choisissez une image JPG ou PNG pour le produit.';
                } elseif (!$pdo->insérerProduit(
                    $valeursProduit['nom'],
                    $valeursProduit['objectif'],
                    $valeursProduit['information'],
                    $valeursProduit['effetIndesirable'],
                    $nouvelleImage
                )) {
                    supprimerImageProduit($nouvelleImage);
                    $nouvelleImage = null;
                    $erreur = "Le produit n'a pas pu être ajouté.";
                } else {
                    $_SESSION['messageProduit'] = 'Le produit a été ajouté.';
                    header('Location: index.php?uc=produit');
                    exit;
                }
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
                $imageExistante = $produitAModifier['image'];
                $produitAModifier = array_merge(
                    array('id' => $id, 'image' => $imageExistante),
                    $valeursProduit
                );
                if (!$formulaireValide) {
                    $erreur = 'Renseignez tous les champs. Le nom est limité à 60 caractères.';
                } else {
                    $nouvelleImage = televerserImageProduit(
                        isset($_FILES['image']) ? $_FILES['image'] : array()
                    );

                    if (!$pdo->modifierProduit(
                        $id,
                        $valeursProduit['nom'],
                        $valeursProduit['objectif'],
                        $valeursProduit['information'],
                        $valeursProduit['effetIndesirable'],
                        $nouvelleImage
                    )) {
                        if ($nouvelleImage !== null) {
                            supprimerImageProduit($nouvelleImage);
                            $nouvelleImage = null;
                        }
                        $erreur = 'Le produit n’a pas pu être modifié.';
                    } else {
                        $imageRemplacee = $nouvelleImage !== null;
                        $nouvelleImage = null;
                        $message = 'Le produit a été modifié.';
                        if ($imageRemplacee && !empty($imageExistante)
                            && !supprimerImageProduit($imageExistante)) {
                            $message .= " L'ancienne image n'a pas pu être supprimée.";
                        }
                        $_SESSION['messageProduit'] = $message;
                        header('Location: index.php?uc=produit');
                        exit;
                    }
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
            } elseif (($produitASupprimer = $pdo->donneProduitById($id)) === null) {
                $erreur = 'Le produit demandé est introuvable.';
            } elseif (!$pdo->supprimerProduit($id)) {
                $erreur = 'Le produit n’a pas pu être supprimé.';
            } else {
                if (!supprimerImageProduit($produitASupprimer['image'])) {
                    $_SESSION['messageProduit'] = "Le produit a été supprimé, mais son image n'a pas pu être supprimée.";
                } else {
                    $_SESSION['messageProduit'] = 'Le produit a été supprimé.';
                }
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
    if ($nouvelleImage !== null) {
        supprimerImageProduit($nouvelleImage);
    }
    $erreur = $exception->getMessage();
}

include $vue;
?>

<?php
if (empty($_SESSION['id'])) {
    header('Location: index.php');
    exit;
}

$lesProduits = isset($lesProduits) && is_array($lesProduits) ? $lesProduits : array();
$produitAModifier = isset($produitAModifier) && is_array($produitAModifier)
    ? $produitAModifier
    : null;
$valeursProduit = isset($valeursProduit) && is_array($valeursProduit)
    ? $valeursProduit
    : array();
$echapper = function ($valeur) {
    return htmlspecialchars((string) $valeur, ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>GSB - Gestion des produits</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <style>
        .produit-page {
            padding-bottom: 90px;
        }
        .produit-page .produit-panel {
            margin: 30px auto;
            padding: 28px;
            max-width: 1120px;
            background: #fff;
            border-radius: 6px;
            box-shadow: rgba(0, 0, 0, 0.3) 0 1px 3px, rgba(0, 0, 0, 0.2) 0 0 1px;
        }
        .produit-page h1,
        .produit-page h2 {
            color: #2c3742;
        }
        .produit-page h1 {
            margin-top: 0;
        }
        .produit-page .section-title {
            margin: 0 0 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e8e8e8;
        }
        .produit-page .form-control {
            margin-bottom: 12px;
        }
        .produit-page textarea.form-control {
            min-height: 85px;
            resize: vertical;
        }
        .produit-page .table {
            margin-bottom: 0;
        }
        .produit-page .table th {
            background: #f5f5f5;
            color: #2c3742;
        }
        .produit-page .product-actions {
            white-space: nowrap;
        }
        @media (max-width: 767px) {
            .produit-page .produit-panel {
                margin: 15px 0;
                padding: 18px;
            }
        }
    </style>
</head>
<body background="assets/img/laboratoire.jpg">
<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#navigation-produits" aria-expanded="false">
                <span class="sr-only">Navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="index.php?uc=connexion">Galaxy Swiss Bourdin</a>
        </div>
        <div class="collapse navbar-collapse" id="navigation-produits">
            <ul class="nav navbar-nav">
                <li><a href="index.php?uc=sommaire">Accueil</a></li>
                <li class="active"><a href="index.php?uc=produit">Produits</a></li>
                <li><a href="index.php?uc=portabilite&action=demande">Droit à la portabilité (RGPD)</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li><a><?php echo $echapper(isset($_SESSION['prenom']) ? $_SESSION['prenom'] : ''); ?>
                    <?php echo $echapper(isset($_SESSION['nom']) ? $_SESSION['nom'] : ''); ?></a></li>
                <li><a>Gestion du catalogue</a></li>
            </ul>
        </div>
    </div>
</nav>

<main class="container produit-page">
    <section class="produit-panel">
        <div class="row">
            <div class="col-sm-8">
                <h1>Gestion des produits</h1>
                <p class="text-muted">Consultez et gérez le catalogue des produits GSB.</p>
            </div>
            <div class="col-sm-4 text-right">
                <span class="label label-info">
                    <?php echo count($lesProduits); ?> produit<?php echo count($lesProduits) > 1 ? 's' : ''; ?>
                </span>
            </div>
        </div>

        <?php if (!empty($erreur)): ?>
            <div class="alert alert-danger"><?php echo $echapper($erreur); ?></div>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo $echapper($message); ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <h2 class="section-title">
                    <?php echo $produitAModifier === null ? 'Ajouter un produit' : 'Modifier un produit'; ?>
                </h2>
                <form method="post" action="index.php?uc=produit&action=<?php echo $produitAModifier === null ? 'ajouter' : 'modifier'; ?>">
                    <?php if ($produitAModifier !== null): ?>
                        <input type="hidden" name="id" value="<?php echo $echapper(isset($produitAModifier['id']) ? $produitAModifier['id'] : ''); ?>">
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="nom">Nom du produit</label>
                        <input class="form-control" id="nom" name="nom" type="text"
                               maxlength="60" placeholder="Nom du produit" required
                               value="<?php echo $echapper(isset($valeursProduit['nom']) ? $valeursProduit['nom'] : ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="objectif">Objectif</label>
                        <textarea class="form-control" id="objectif" name="objectif"
                                  placeholder="Objectif du produit" required><?php echo $echapper(isset($valeursProduit['objectif']) ? $valeursProduit['objectif'] : ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="information">Informations</label>
                        <textarea class="form-control" id="information" name="information"
                                  placeholder="Informations sur le produit" required><?php echo $echapper(isset($valeursProduit['information']) ? $valeursProduit['information'] : ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="effetIndesirable">Effets indésirables</label>
                        <textarea class="form-control" id="effetIndesirable" name="effetIndesirable"
                                  placeholder="Effets indésirables connus" required><?php echo $echapper(isset($valeursProduit['effetIndesirable']) ? $valeursProduit['effetIndesirable'] : ''); ?></textarea>
                    </div>
                    <button class="btn btn-primary signup" type="submit">
                        <?php echo $produitAModifier === null ? 'Ajouter le produit' : 'Enregistrer les modifications'; ?>
                    </button>
                    <?php if ($produitAModifier !== null): ?>
                        <a class="btn btn-default" href="index.php?uc=produit">Annuler</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="col-md-8">
                <h2 class="section-title">Catalogue des produits</h2>
                <?php if (count($lesProduits) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Objectif</th>
                                    <th>Informations</th>
                                    <th>Effets indésirables</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lesProduits as $produit): ?>
                                    <tr>
                                        <td><?php echo $echapper(isset($produit['nom']) ? $produit['nom'] : ''); ?></td>
                                        <td><?php echo $echapper(isset($produit['objectif']) ? $produit['objectif'] : ''); ?></td>
                                        <td><?php echo $echapper(isset($produit['information']) ? $produit['information'] : ''); ?></td>
                                        <td><?php echo $echapper(isset($produit['effetIndesirable']) ? $produit['effetIndesirable'] : ''); ?></td>
                                        <td class="product-actions">
                                            <a class="btn btn-default btn-xs"
                                               href="index.php?uc=produit&action=modifier&id=<?php echo rawurlencode(isset($produit['id']) ? $produit['id'] : ''); ?>">
                                                Modifier
                                            </a>
                                            <form method="post" action="index.php?uc=produit&action=supprimer"
                                                  onsubmit="return confirm('Confirmer la suppression de ce produit ?');"
                                                  style="display:inline">
                                                <input type="hidden" name="id"
                                                       value="<?php echo $echapper(isset($produit['id']) ? $produit['id'] : ''); ?>">
                                                <button class="btn btn-danger btn-xs" type="submit">Supprimer</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        Aucun produit à afficher pour le moment.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<script src="https://code.jquery.com/jquery.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="js/custom.js"></script>
</body>
</html>
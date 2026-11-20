<?php
if (empty($_SESSION['id'])) {
    header('Location: index.php');
    exit;
}

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
    <link href="css/produit.css" rel="stylesheet">
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
                <li><a><?php echo $echapper($_SESSION['prenom']); ?> <?php echo $echapper($_SESSION['nom']); ?></a></li>
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
                <?php include __DIR__ . '/v_produit_formulaire.php'; ?>
            </div>
            <div class="col-md-8">
                <?php include __DIR__ . '/v_produit_tableau.php'; ?>
            </div>
        </div>
    </section>
</main>

<script src="https://code.jquery.com/jquery.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script src="js/custom.js"></script>
<script src="js/produit.js"></script>
</body>
</html>

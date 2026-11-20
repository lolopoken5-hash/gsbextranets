<?php
$messageIntegrite = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fichier_recupere'])) {
    $fichierTmp = $_FILES['fichier_recupere']['tmp_name'];
    $empreinteSite = trim($_POST['empreinte_site'] ?? '');

    if (is_uploaded_file($fichierTmp)) {
        // Hachage du fichier soumis par l'utilisateur
        $empreinteFichierClient = hash_file('sha256', $fichierTmp);

        if (hash_equals(strtolower($empreinteSite), strtolower($empreinteFichierClient))) {
            $messageIntegrite = "<div class='alert alert-success'><strong>Intègre :</strong> L'empreinte du fichier correspond exactement à celle affichée sur le site !</div>";
        } else {
            $messageIntegrite = "<div class='alert alert-danger'><strong>Non Intègre :</strong> L'empreinte ne correspond pas. Le fichier a été altéré ou modifié.</div>";
        }
    }
}
?>

<div class="container mt-4">
    <h3>Vérification d'intégrité du fichier JSON</h3>

    <?= $messageIntegrite ?>

    <form method="POST" enctype="multipart/form-data" class="card p-4">
        <div class="form-group mb-3">
            <label>1. Fichier JSON récupéré :</label>
            <input type="file" name="fichier_recupere" class="form-control" accept=".json" required>
        </div>
        <div class="form-group mb-3">
            <label>2. Empreinte affichée sur le site (SHA-256) :</label>
            <input type="text" name="empreinte_site" class="form-control" placeholder="Collez l'empreinte ici..." required>
        </div>
        <button type="submit" class="btn btn-info">Vérifier l'intégrité</button>
    </form>
</div>
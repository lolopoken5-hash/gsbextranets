<div class="container mt-4">
    <h2>Droit à la portabilité des données (Article 20 RGPD)[cite: 2]</h2>

    <?php if (isset($erreur)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <?php if (!isset($succes)): ?>
        <!-- Message de demande à l'utilisateur avec validation Oui[cite: 2] -->
        <div class="card p-3 my-3">
            <p><strong>Souhaitez-vous récupérer la totalité de vos données personnelles sous format JSON ?</strong>[cite: 2]</p>
            <form action="index.php?uc=portabilite&action=generer" method="POST">
                <button type="submit" name="confirmation" value="oui" class="btn btn-success">Oui, générer mon fichier JSON</button>
                <a href="index.php?uc=sommaire" class="btn btn-secondary">Non, annuler</a>
            </form>
        </div>
    <?php else: ?>
        <!-- Affichage du lien de téléchargement et de l'empreinte[cite: 2] -->
        <div class="alert alert-success">
            <p>Votre fichier a été généré avec succès dans le dossier <code>portabilite/</code>[cite: 2].</p>
            <a href="<?= htmlspecialchars($resultatExport['chemin_relatif']) ?>" download class="btn btn-primary">
                📥 Télécharger <?= htmlspecialchars($resultatExport['nom_fichier']) ?>
            </a>
        </div>

        <div class="card p-3 bg-light">
            <h4>Empreinte pour la vérification de l'intégrité[cite: 2]</h4>
            <p>Algorithme utilisé : <strong><?= $resultatExport['algorithme'] ?></strong>[cite: 2]</p>
            <p>Empreinte SHA-256 :</p>
            <input type="text" class="form-control" value="<?= $resultatExport['empreinte'] ?>" readonly>
            <br>
            <a href="index.php?uc=portabilite&action=verifier" class="btn btn-outline-info">Vérifier l'intégrité du fichier télécharge[cite: 2]</a>
        </div>
    <?php endif; ?>
</div>
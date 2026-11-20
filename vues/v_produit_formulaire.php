<h2 class="section-title">
    <?php echo $produitAModifier === null ? 'Ajouter un produit' : 'Modifier un produit'; ?>
</h2>
<form method="post" enctype="multipart/form-data"
      action="index.php?uc=produit&action=<?php echo $produitAModifier === null ? 'ajouter' : 'modifier'; ?>">
    <?php if ($produitAModifier !== null): ?>
        <input type="hidden" name="id" value="<?php echo $echapper($produitAModifier['id']); ?>">
    <?php endif; ?>

    <div class="form-group">
        <label for="nom">Nom du produit</label>
        <input class="form-control" id="nom" name="nom" type="text" maxlength="60"
               placeholder="Nom du produit" required
               value="<?php echo $echapper($valeursProduit['nom']); ?>">
    </div>
    <div class="form-group">
        <label for="objectif">Objectif</label>
        <textarea class="form-control" id="objectif" name="objectif"
                  placeholder="Objectif du produit" required><?php echo $echapper($valeursProduit['objectif']); ?></textarea>
    </div>
    <div class="form-group">
        <label for="information">Informations</label>
        <textarea class="form-control" id="information" name="information"
                  placeholder="Informations sur le produit" required><?php echo $echapper($valeursProduit['information']); ?></textarea>
    </div>
    <div class="form-group">
        <label for="effetIndesirable">Effets indésirables</label>
        <textarea class="form-control" id="effetIndesirable" name="effetIndesirable"
                  placeholder="Effets indésirables connus" required><?php echo $echapper($valeursProduit['effetIndesirable']); ?></textarea>
    </div>

    <?php
    $imageActuelle = isset($produitAModifier['image'])
        && preg_match('#^images/produits/[a-f0-9]{32}\.(jpg|png)$#', $produitAModifier['image'])
        ? $produitAModifier['image']
        : '';
    ?>
    <div class="form-group">
        <label for="image">
            Ajouter une image du produit<?php echo $produitAModifier === null ? ' (obligatoire)' : ' (facultative)'; ?>
        </label>
        <div class="upload-area" id="uploadArea">
            <span class="upload-plus" id="uploadPlus"
                  style="<?php echo $imageActuelle === '' ? '' : 'display:none'; ?>">+</span>
            <input type="file" id="image" name="image"
                   accept=".jpg,.jpeg,.png,image/jpeg,image/png" style="display:none">
            <img id="imagePreview" class="upload-preview"
                 src="<?php echo $echapper($imageActuelle); ?>"
                 alt="<?php echo $imageActuelle === '' ? '' : 'Aperçu de l’image du produit'; ?>"
                 style="<?php echo $imageActuelle === '' ? 'display:none' : ''; ?>">
        </div>
        <p class="help-block">JPG ou PNG, 2 Mo maximum. Cliquez sur la zone pour choisir une image.</p>
    </div>

    <button class="btn btn-primary signup" type="submit">
        <?php echo $produitAModifier === null ? 'Ajouter le produit' : 'Enregistrer les modifications'; ?>
    </button>
    <?php if ($produitAModifier !== null): ?>
        <a class="btn btn-default" href="index.php?uc=produit">Annuler</a>
    <?php endif; ?>
</form>

<h2 class="section-title">Catalogue des produits</h2>
<?php if (count($lesProduits) > 0): ?>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Objectif</th>
                    <th>Informations</th>
                    <th>Effets indésirables</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lesProduits as $produit): ?>
                    <?php
                    $imageProduit = isset($produit['image'])
                        && preg_match('#^images/produits/[a-f0-9]{32}\.(jpg|png)$#', $produit['image'])
                        ? $produit['image']
                        : '';
                    ?>
                    <tr>
                        <td>
                            <?php if ($imageProduit !== ''): ?>
                                <img class="product-thumbnail"
                                     src="<?php echo $echapper($imageProduit); ?>"
                                     alt="<?php echo $echapper($produit['nom']); ?>">
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?php echo $echapper($produit['nom']); ?></td>
                        <td><?php echo $echapper($produit['objectif']); ?></td>
                        <td><?php echo $echapper($produit['information']); ?></td>
                        <td><?php echo $echapper($produit['effetIndesirable']); ?></td>
                        <td class="product-actions">
                            <a class="btn btn-default btn-xs"
                               href="index.php?uc=produit&action=modifier&id=<?php echo rawurlencode($produit['id']); ?>">
                                Modifier
                            </a>
                            <form method="post" action="index.php?uc=produit&action=supprimer"
                                  class="delete-product-form" style="display:inline">
                                <input type="hidden" name="id" value="<?php echo $echapper($produit['id']); ?>">
                                <button class="btn btn-danger btn-xs" type="submit">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <div class="alert alert-info">Aucun produit à afficher pour le moment.</div>
<?php endif; ?>

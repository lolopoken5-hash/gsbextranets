ALTER TABLE `medecinproduit`
DROP FOREIGN KEY `medecinproduit_ibfk_2`;

ALTER TABLE `produit`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `medecinproduit`
ADD CONSTRAINT `medecinproduit_ibfk_2`
FOREIGN KEY (`idProduit`) REFERENCES `produit` (`id`);

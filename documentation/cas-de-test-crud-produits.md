# Cas de test — Administration des produits GSBExtranet

## Objectif

Vérifier que le chef de produit peut consulter et gérer les produits, conformément à la mission : créer, consulter, modifier et supprimer un produit, avec une image associée.

## Préconditions

- L’application et sa base de données de test sont disponibles.
- La fonctionnalité d’administration des produits est développée.
- Un compte chef de produit autorisé est disponible.
- Un produit de test peut être créé sans utiliser de données réelles.
- Pour les tests de modification et de suppression, un produit de test existe déjà.
- Les fichiers image utilisés sont des fichiers de test.

## Consignes d’exécution

Exécuter les tests dans l’ordre utile, noter le résultat observé et choisir **Réussi** ou **Échoué**. Ne pas supprimer de données réelles. Les résultats restent à compléter après le développement et l’exécution des tests.

| ID | Fonction | Étapes et données de test | Résultat attendu | Résultat obtenu | Statut |
|---|---|---|---|---|---|
| CP-01 | Consulter la liste | Se connecter avec un compte chef de produit, puis ouvrir la page de gestion des produits. | La page s’ouvre et affiche les produits disponibles avec leurs informations principales. | À compléter | À exécuter |
| CP-02 | Consulter un produit | Depuis la liste, ouvrir la fiche d’un produit existant. | La fiche affiche les informations du produit et son image si elle en possède une. | À compléter | À exécuter |
| CP-03 | Créer un produit | Ouvrir le formulaire d’ajout, remplir les champs obligatoires avec des données de test et joindre une image valide, puis enregistrer. | Le produit est créé, un message de confirmation s’affiche et le produit apparaît dans la liste avec son image. | À compléter | À exécuter |
| CP-04 | Créer un produit sans champ obligatoire | Ouvrir le formulaire d’ajout, laisser vide un champ obligatoire, puis essayer d’enregistrer. | Une erreur compréhensible est affichée et le produit n’est pas créé. | À compléter | À exécuter |
| CP-05 | Modifier un produit | Ouvrir un produit existant, changer son nom ou une autre information, puis enregistrer. | La modification est confirmée et les nouvelles informations sont visibles dans la fiche et la liste. | À compléter | À exécuter |
| CP-06 | Modifier avec une donnée invalide | Dans la fiche de modification, saisir une valeur invalide ou laisser vide un champ obligatoire, puis enregistrer. | Une erreur compréhensible est affichée et les données précédentes restent inchangées. | À compléter | À exécuter |
| CP-07 | Remplacer l’image | Modifier un produit existant en sélectionnant une autre image valide, puis enregistrer. | La nouvelle image est enregistrée et affichée sur la fiche du produit. | À compléter | À exécuter |
| CP-08 | Refuser un fichier image invalide | Essayer d’ajouter ou de remplacer l’image par un fichier qui n’est pas une image autorisée. | Le fichier est refusé avec un message explicite et aucune image invalide n’est enregistrée. | À compléter | À exécuter |
| CP-09 | Supprimer un produit | Depuis la liste ou la fiche, demander la suppression d’un produit de test et confirmer si une confirmation est proposée. | Le produit n’apparaît plus dans la liste et un message confirme la suppression. | À compléter | À exécuter |
| CP-10 | Annuler une suppression | Demander la suppression d’un produit de test, puis annuler la confirmation. | Le produit est conservé et reste consultable. | À compléter | À exécuter |

## Bilan d’exécution

- Tests réussis : à compléter
- Tests échoués : à compléter
- Anomalies constatées : à compléter
- Date d’exécution : à compléter
- Nom de la personne ayant exécuté les tests : à compléter

## Point à vérifier lors du développement

La structure actuelle de la table `produit` contient un identifiant, un nom, un objectif, des informations et des effets indésirables, mais aucun champ d’image. Il faudra donc prévoir comment l’image sera stockée ou référencée avant de pouvoir réussir les tests CP-03, CP-07 et CP-08.

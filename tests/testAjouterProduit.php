<?php

require_once(__DIR__ . "/testProduitTransaction.php");

lancerTestProduit(function ($lePdo, $idTest) {
    var_dump($lePdo->insérerProduit(
        'Produit test ajouté',
        'Objectif de test',
        'Information de test',
        'Aucun',
        'images/produits/test.jpg'
    ));
});

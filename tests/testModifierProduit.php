<?php

require_once(__DIR__ . "/testProduitTransaction.php");

lancerTestProduit(function ($lePdo, $idTest) {
    var_dump($lePdo->modifierProduit(
        $idTest,
        'Produit test modifié',
        'Objectif modifié',
        1
    ));
});

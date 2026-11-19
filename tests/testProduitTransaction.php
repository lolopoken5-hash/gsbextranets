<?php

require_once(__DIR__ . "/../include/class.pdogsb.inc.php");

function lancerTestProduit(callable $test)
{
    $connexion = null;

    try {
        $lePdo = PdoGsb::getPdoGsb();

        $reflection = new ReflectionClass('PdoGsb');
        $proprietePdo = $reflection->getProperty('monPdo');
        $proprietePdo->setAccessible(true);
        $connexion = $proprietePdo->getValue();

        $connexion->beginTransaction();

        $requete = $connexion->prepare("SELECT COALESCE(MAX(id), 0) + 1 FROM produit");
        $requete->execute();
        $idTest = (int) $requete->fetchColumn();

        $requete = $connexion->prepare(
            "INSERT INTO produit (id, nom, objectif, information, effetIndesirable)
             VALUES (:id, :nom, :objectif, :information, :effetIndesirable)"
        );
        $requete->execute(array(
            ':id' => $idTest,
            ':nom' => 'Produit test CRUD',
            ':objectif' => 'Objectif initial',
            ':information' => 'Information initiale',
            ':effetIndesirable' => 'Aucun'
        ));

        try {
            $test($lePdo, $idTest);
        } catch (Throwable $erreur) {
            var_dump(array(
                'reussi' => false,
                'erreur' => $erreur->getMessage()
            ));
        }
    } catch (Throwable $erreur) {
        var_dump(array(
            'reussi' => false,
            'erreur' => $erreur->getMessage()
        ));
    } finally {
        if ($connexion instanceof PDO && $connexion->inTransaction()) {
            $connexion->rollBack();
        }
    }
}

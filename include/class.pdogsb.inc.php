<?php

/** 
 * Classe d'accès aux données. 
 * Utilise les services de la classe PDO
 * pour l'application GSB
 * Les attributs sont tous statiques,
 * les 4 premiers pour la connexion
 * $monPdo de type PDO 
 * $monPdoGsb qui contiendra l'unique instance de la classe
 *
 * @package default
 * @author Cheri Bibi
 * @version    1.0
 * @link       http://www.php.net/manual/fr/book.pdo.php
 */

class PdoGsb{           
    
    private static $serveur='mysql:host=127.0.0.1;port=3306';
    private static $bdd='dbname=gsbextranet';           
    private static $user='gsbextranetAdmin';    
    private static $mdp='123';   
    private static $monPdo;
    private static $monPdoGsb=null;
        
/**
 * Constructeur privé, crée l'instance de PDO qui sera sollicitée
 * pour toutes les méthodes de la classe
 */            
    private function __construct(){
        PdoGsb::$monPdo = new PDO(PdoGsb::$serveur.';'.PdoGsb::$bdd, PdoGsb::$user, PdoGsb::$mdp); 
        PdoGsb::$monPdo->query("SET CHARACTER SET utf8");
    }

    public function __destruct(){
        PdoGsb::$monPdo = null;
    }

/**
 * Fonction statique qui crée l'unique instance de la classe
 * Appel : $instancePdoGsb = PdoGsb::getPdoGsb();
 * @return l'unique objet de la classe PdoGsb
 */
    public static function getPdoGsb(): PdoGsb {
        if(PdoGsb::$monPdoGsb==null){
            PdoGsb::$monPdoGsb= new PdoGsb();
        }
        return PdoGsb::$monPdoGsb;  
    }

/**
 * vérifie si le login et le mot de passe sont corrects
 * renvoie true si les 2 sont corrects
 * @param string $login
 * @param string $pwd
 * @return bool
 * @throws Exception
 */
    public function checkUser($login,$pwd):bool {
        if (!is_string($login) || !is_string($pwd) || $login === '' || $pwd === '') {
            return false;
        }

        $pdo = PdoGsb::$monPdo;
        $statement = $pdo->prepare(
            "SELECT id, motDePasse FROM medecin WHERE mail = :login AND token IS NULL"
        );
        $statement->bindValue(':login', $login, PDO::PARAM_STR);
        if (!$statement->execute()) {
            throw new Exception("erreur dans la requête");
        }

        $utilisateur = $statement->fetch(PDO::FETCH_ASSOC);
        if (!is_array($utilisateur) || !is_string($utilisateur['motDePasse'])) {
            return false;
        }

        if (password_verify($pwd, $utilisateur['motDePasse'])) {
            return true;
        }

        if (hash_equals($utilisateur['motDePasse'], $pwd)) {
            $motDePasseHache = password_hash($pwd, PASSWORD_DEFAULT);
            $miseAJour = $pdo->prepare(
                "UPDATE medecin SET motDePasse = :motDePasse WHERE id = :id"
            );
            $miseAJour->bindValue(':motDePasse', $motDePasseHache, PDO::PARAM_STR);
            $miseAJour->bindValue(':id', $utilisateur['id'], PDO::PARAM_INT);
            if (!$miseAJour->execute()) {
                throw new Exception("Impossible de sécuriser le mot de passe existant.");
            }
            return true;
        }

        return false;
    }

    public function donneLeMedecinByMail($login) {
        $pdo = PdoGsb::$monPdo;
        $monObjPdoStatement=$pdo->prepare("SELECT id, nom, prenom,mail FROM medecin WHERE mail= :login");
        $bvc1=$monObjPdoStatement->bindValue(':login',$login,PDO::PARAM_STR);
        if ($monObjPdoStatement->execute()) {
            $unUser=$monObjPdoStatement->fetch();
        }
        else {
            throw new Exception("erreur dans la requête");
        }
        return $unUser;   
    }

    public function tailleChampsMail() {
        $pdoStatement = PdoGsb::$monPdo->prepare(
            "SELECT CHARACTER_MAXIMUM_LENGTH
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE table_name='medecin'
            AND COLUMN_NAME='mail'"
        );
        $pdoStatement->execute();
        $leResultat = $pdoStatement->fetch();
        return $leResultat[0];
    }

    public function ajouterConsentement($idMedecin, $idVersion = 1) {
        $pdoStatement = PdoGsb::$monPdo->prepare(
            "INSERT INTO Consentement
            (id_Medecin, DateConsentement, id_version)
            VALUES (:idMedecin, NOW(), :idVersion)"
        );
        $pdoStatement->bindValue(':idMedecin', $idMedecin, PDO::PARAM_INT);
        $pdoStatement->bindValue(':idVersion', $idVersion, PDO::PARAM_INT);
        return $pdoStatement->execute();
    }

    public function creeMedecin($email, $mdp) {
        $pdoStatement = PdoGsb::$monPdo->prepare(
            "INSERT INTO medecin
            (id, mail, motDePasse, dateCreation, dateConsentement)
            VALUES
            (NULL, :leMail, :leMdp, NOW(), NOW())"
        );
        $pdoStatement->bindValue(':leMail', $email);

        $mdpHache = password_hash($mdp, PASSWORD_DEFAULT);
        $pdoStatement->bindValue(':leMdp', $mdpHache);
        $execution = $pdoStatement->execute();
        if ($execution) {
            $idMedecin = PdoGsb::$monPdo->lastInsertId();
            $this->ajouterConsentement($idMedecin, 1);
        }
        return $execution;
    }

    public function connexionInitiale($mail) {
        $pdo = PdoGsb::$monPdo;
        $medecin= $this->donneLeMedecinByMail($mail);
        $id = $medecin['id'];
        return $this->ajouteConnexionInitiale($id);
    }

    public function ajouteConnexionInitiale($id) {
        $pdoStatement = PdoGsb::$monPdo->prepare("INSERT INTO historiqueconnexion "
            . "VALUES (:leMedecin, now(), now())");
        $bv1 = $pdoStatement->bindValue(':leMedecin', $id);
        $execution = $pdoStatement->execute();
        return $execution;
    }

    public function donneinfosmedecin($id) {
        $pdo = PdoGsb::$monPdo;
        $monObjPdoStatement=$pdo->prepare("SELECT id,nom,prenom FROM medecin WHERE id= :lId");
        $bvc1=$monObjPdoStatement->bindValue(':lId',$id,PDO::PARAM_INT);
        if ($monObjPdoStatement->execute()) {
            $unUser=$monObjPdoStatement->fetch();
            return $unUser;
        }
        else {
            throw new Exception("erreur");
        }
    }


    public function donneLesProduits() {
      $pdoStatement = PdoGsb::$monPdo->query(
          "SELECT id, nom, objectif, information, effetIndesirable, image
           FROM produit
           ORDER BY nom"
      );
      if ($pdoStatement === false) {
          throw new Exception("Impossible de récupérer les produits.");
      }
      return $pdoStatement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function donneProduitById($id) {
      $pdoStatement = PdoGsb::$monPdo->prepare(
          "SELECT id, nom, objectif, information, effetIndesirable, image
           FROM produit
           WHERE id = :id"
      );
      $pdoStatement->bindValue(':id', $id, PDO::PARAM_INT);
      if (!$pdoStatement->execute()) {
          throw new Exception("Impossible de récupérer le produit.");
      }
      $produit = $pdoStatement->fetch(PDO::FETCH_ASSOC);
      return $produit ? $produit : null;
    }

    public function insérerProduit($nom, $objectif, $information, $effetIndesirable, $image) {
      $pdoStatement = PdoGsb::$monPdo->prepare(
          "INSERT INTO produit (nom, objectif, information, effetIndesirable, image)
           VALUES (:nom, :objectif, :information, :effetIndesirable, :image)"
      );
      $pdoStatement->bindValue(':nom', $nom, PDO::PARAM_STR);
      $pdoStatement->bindValue(':objectif', $objectif, PDO::PARAM_STR);
      $pdoStatement->bindValue(':information', $information, PDO::PARAM_STR);
      $pdoStatement->bindValue(':effetIndesirable', $effetIndesirable, PDO::PARAM_STR);
      $pdoStatement->bindValue(':image', $image, PDO::PARAM_STR);
      return $pdoStatement->execute();
    }

    public function supprimerProduit($id) {
      $pdoStatement = PdoGsb::$monPdo->prepare(
          "DELETE FROM produit WHERE id = :id"
      );
      $pdoStatement->bindValue(':id', $id, PDO::PARAM_INT);
      return $pdoStatement->execute();
    }

    public function modifierProduit($id, $nom, $objectif, $information, $effetIndesirable, $image) {
      $pdoStatement = PdoGsb::$monPdo->prepare(
          "UPDATE produit
           SET nom = :nom, objectif = :objectif, information = :information,
               effetIndesirable = :effetIndesirable,
               image = COALESCE(:image, image)
           WHERE id = :id"
      );
      $pdoStatement->bindValue(':id', $id, PDO::PARAM_INT);
      $pdoStatement->bindValue(':nom', $nom, PDO::PARAM_STR);
      $pdoStatement->bindValue(':objectif', $objectif, PDO::PARAM_STR);
      $pdoStatement->bindValue(':information', $information, PDO::PARAM_STR);
      $pdoStatement->bindValue(':effetIndesirable', $effetIndesirable, PDO::PARAM_STR);
      $pdoStatement->bindValue(
          ':image',
          $image,
          $image === null ? PDO::PARAM_NULL : PDO::PARAM_STR
      );
      return $pdoStatement->execute();
    }

    public function nbrConsultationProduit($idProduit) {
      $pdoStatement = PdoGsb::$monPdo->prepare(
          "SELECT COUNT(*) AS nbrConsultations FROM consultation WHERE id_produit = :idProduit"
      );
      $pdoStatement->bindValue(':idProduit', $idProduit, PDO::PARAM_INT);
      if ($pdoStatement->execute()) {
          $resultat = $pdoStatement->fetch();
          return $resultat['nbrConsultations'];
      } else {
          throw new Exception("Erreur dans la requête");
      }
    }

    public function RéinitialiserMotDePasse($email, $nouveauMotDePasse) {
        $pdoStatement = PdoGsb::$monPdo->prepare(
            "UPDATE medecin SET motDePasse = :nouveauMotDePasse WHERE mail = :email"
        );
        $mdpHache = password_hash($nouveauMotDePasse, PASSWORD_DEFAULT);
        $pdoStatement->bindValue(':nouveauMotDePasse', $mdpHache);
        $pdoStatement->bindValue(':email', $email);
        return $pdoStatement->execute();
    }

}
?>
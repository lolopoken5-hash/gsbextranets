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
    public static function getPdoGsb(){
        if(PdoGsb::$monPdoGsb==null){
            PdoGsb::$monPdoGsb= new PdoGsb();
        }
        return PdoGsb::$monPdoGsb;  
    }

/**
 * vérifie si le login et le mot de passe sont corrects
 * renvoie true si les 2 sont corrects
 * @param type $lePDO
 * @param type $login
 * @param type $pwd
 * @return bool
 * @throws Exception
 */
    public function checkUser($login, $pwd): bool {
        $pdo = PdoGsb::$monPdo;
        $requete = $pdo->prepare(
            "SELECT id, motDePasse FROM medecin WHERE mail = :login AND token IS NULL"
        );
        $requete->bindValue(':login', $login, PDO::PARAM_STR);
        if (!$requete->execute()) {
            throw new Exception("erreur dans la requête");
        }

        $medecin = $requete->fetch(PDO::FETCH_ASSOC);
        if (!is_array($medecin) || !is_string($medecin['motDePasse'])) {
            return false;
        }

        $motDePasseEnregistre = $medecin['motDePasse'];
        if (password_verify($pwd, $motDePasseEnregistre)) {
            return true;
        }

        if (hash_equals($motDePasseEnregistre, $pwd)) {
            $nouveauHash = password_hash($pwd, PASSWORD_DEFAULT);
            if ($nouveauHash === false) {
                throw new RuntimeException("Impossible de sécuriser le mot de passe.");
            }

            $miseAJour = $pdo->prepare(
                "UPDATE medecin SET motDePasse = :motDePasse WHERE id = :id"
            );
            $miseAJour->bindValue(':motDePasse', $nouveauHash, PDO::PARAM_STR);
            $miseAJour->bindValue(':id', $medecin['id'], PDO::PARAM_INT);
            if (!$miseAJour->execute()) {
                throw new Exception("Impossible de mettre à jour le mot de passe.");
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
        $motDePasseHache = password_hash($mdp, PASSWORD_DEFAULT);
        if ($motDePasseHache === false) {
            throw new RuntimeException("Impossible de sécuriser le mot de passe.");
        }

        $pdoStatement = PdoGsb::$monPdo->prepare(
            "INSERT INTO medecin
            (id, mail, motDePasse, dateCreation, dateConsentement)
            VALUES
            (NULL, :leMail, :leMdp, NOW(), NOW())"
        );
        $pdoStatement->bindValue(':leMail', $email);
        $pdoStatement->bindValue(':leMdp', $motDePasseHache);
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

    public function donneLesDonneesMedecin(int $idMedecin): ?array {
        $pdoStatement = PdoGsb::$monPdo->prepare(
            "SELECT * FROM medecin WHERE id = :id"
        );
        $pdoStatement->bindValue(':id', $idMedecin, PDO::PARAM_INT);
        $pdoStatement->execute();

        $medecin = $pdoStatement->fetch(PDO::FETCH_ASSOC);
        return $medecin ?: null;
    }

}
?>
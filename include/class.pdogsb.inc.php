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
    public function checkUser($login,$pwd):bool {
        //AJOUTER TEST SUR TOKEN POUR ACTIVATION DU COMPTE
        $user=false;
        $pdo = PdoGsb::$monPdo;
        $monObjPdoStatement=$pdo->prepare("SELECT motDePasse FROM medecin WHERE mail= :login AND token IS NULL");
        $bvc1=$monObjPdoStatement->bindValue(':login',$login,PDO::PARAM_STR);
        if ($monObjPdoStatement->execute()) {
            $unUser=$monObjPdoStatement->fetch();
            if (is_array($unUser)){
               if ($pwd==$unUser['motDePasse'])
                    $user=true;
            }
        }
        else {
            throw new Exception("erreur dans la requête");
        }
        return $user;   
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
        $pdoStatement->bindValue(':leMdp', $mdp);
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

}
?>
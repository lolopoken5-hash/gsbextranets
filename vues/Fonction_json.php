<?php

$host = 'localhost';     
$dbname = 'gsbextranet'; 
$username = 'login4277';      
$password = '123';         

try {
    $pdo = new PDO("mysql:host=$host;dbname=gsbextranet;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}



 
 
function getInformation(PDO $pdo, int $idMedecin): ?array {
    $requete = $pdo->prepare("SELECT * FROM medecin WHERE id = :id");
    $requete->bindValue(':id', $idMedecin, PDO::PARAM_INT);
    $requete->execute();
    
    $medecin = $requete->fetch(); 
    
    if (!$medecin) {
        return null;
    }
    

    unset($medecin['id'], $medecin['password']); 
    
  
    return $medecin;
}

function genererFichierPortabilite(PDO $pdo, int $idMedecin): array {
    
    $donneesMedecin = getInformation($pdo, $idMedecin);
    
    if (!$donneesMedecin) {
        throw new Exception("Médecin introuvable.");
    }
    
 
    $jsonData = json_encode($donneesMedecin, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    $dossier = __DIR__ . 'gsbextranetB3/portabilite/';

    
    
   
    $filename = $idMedecin . '.json';
    $filepath = $dossier . $filename;
    
    // Enregistrement du fichier sur le serveur
    file_put_contents($filepath, $jsonData);
    
    // Calcul de l'empreinte (SHA-256) pour garantir l'intégrité
    $empreinte = hash_file('sha256', $filepath);
    
    // On retourne les informations utiles pour l'affichage dans le contrôleur
    return [
        'chemin_relatif' => 'portabilite/' . $filename,
        'nom_fichier' => $filename,
        'empreinte' => $empreinte,
        'algorithme' => 'SHA-256'
    ];
}


?>








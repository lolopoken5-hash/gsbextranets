<?php

/**
 * Fonction 1 : Récupère les données du médecin en excluant l'id et le mot de passe
 */
function getDonneesMedecin(PDO $pdo, int $idMedecin): ?array {
    $req = $pdo->prepare("SELECT * FROM medecin WHERE id = :id");
    $req->bindValue(':id', $idMedecin, PDO::PARAM_INT);
    $req->execute();
    
    $medecin = $req->fetch(PDO::FETCH_ASSOC);
    if (!$medecin) {
        return null;
    }
    
    // Exclusion obligatoire de l'ID et de l'empreinte du mot de passe
    unset($medecin['id'], $medecin['mdp'], $medecin['password'], $medecin['empreinte']);
    
    return $medecin;
}

/**
 * Fonction 2 : Appelle getDonneesMedecin(), génère le fichier id.json et retourne l'empreinte
 */
function genererFichierJsonPortabilite(PDO $pdo, int $idMedecin): array {
    // 1. Appel de la première fonction
    $donnees = getDonneesMedecin($pdo, $idMedecin);
    if (!$donnees) {
        throw new Exception("Erreur : Médecin introuvable dans la base de données.");
    }

    // 2. Vérification et création du dossier 'portabilite'
    $dossier = __DIR__ . '/../portabilite/';
    if (!is_dir($dossier)) {
        mkdir($dossier, 0777, true);
    }

    // 3. Génération du fichier id.json
    $nomFichier = $idMedecin . '.json';
    $cheminComplet = $dossier . $nomFichier;
    $jsonData = json_encode($donnees, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    file_put_contents($cheminComplet, $jsonData);

    // 4. Calcul de l'empreinte (hachage SHA-256) pour l'intégrité
    $empreinte = hash_file('sha256', $cheminComplet);

    return [
        'nom_fichier'    => $nomFichier,
        'chemin_relatif' => 'portabilite/' . $nomFichier,
        'empreinte'      => $empreinte,
        'algorithme'     => 'SHA-256'
    ];
}
?>
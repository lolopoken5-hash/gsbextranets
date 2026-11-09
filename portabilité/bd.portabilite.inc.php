<?php

/**
 * Fonction 1 : Récupère les données du médecin en excluant l'id et le mot de passe
 */
function getDonneesMedecin(PdoGsb $pdo, int $idMedecin): ?array {
    $medecin = $pdo->donneLesDonneesMedecin($idMedecin);
    if (!$medecin) {
        return null;
    }
    
    // Exclusion obligatoire de l'ID et de l'empreinte du mot de passe
    unset($medecin['id'], $medecin['mdp'], $medecin['password'], $medecin['empreinte'], $medecin['motDePasse']);
    
    return $medecin;
}

/**
 * Fonction 2 : Prépare le JSON à télécharger et retourne son empreinte
 */
function genererFichierJsonPortabilite(PdoGsb $pdo, int $idMedecin): array {
    $donnees = getDonneesMedecin($pdo, $idMedecin);
    if (!$donnees) {
        throw new Exception("Erreur : Médecin introuvable dans la base de données.");
    }

    $jsonData = json_encode(
        $donnees,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
    );

    return [
        'nom_fichier' => $idMedecin . '.json',
        'contenu' => $jsonData,
        'empreinte' => hash('sha256', $jsonData),
        'algorithme' => 'SHA-256'
    ];
}
?>
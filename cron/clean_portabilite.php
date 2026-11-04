<?php
// Script de nettoyage des fichiers JSON[cite: 1]
$dossier = __DIR__ . '/../portabilite/';

if (is_dir($dossier)) {
    $fichiers = glob($dossier . '*.json');
    foreach ($fichiers as $fichier) {
        if (is_file($fichier)) {
            unlink($fichier);
        }
    }
    echo "[" . date('Y-m-d H:i:s') . "] Nettoyage du dossier portabilité effectué.\n";
}
?>
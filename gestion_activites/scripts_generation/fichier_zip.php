<?php
$dossier = __DIR__ . '/../../pdfs_temp/';
$chemin_zip = $dossier . 'fichier_zip.zip';


// 🧪 Sélection manuelle des fichiers à inclure
$fichiers_a_inclure = [
    $dossier . 'liste_des_RIB.pdf',
    $dossier . 'liste_des_RIB_enrichie.pdf', // ajoute si nécessaire
    // Ajoute ici d'autres fichiers générés, selon leur nom exact
];

// 📦 Création de l'archive ZIP
$zip = new ZipArchive();
if ($zip->open($chemin_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
    foreach ($fichiers_a_inclure as $fichier) {
        if (file_exists($fichier)) {
            $zip->addFile($fichier, basename($fichier));
        }
    }
    $zip->close();

    echo "✅ Archive créée avec les documents sélectionnés : ";
   echo "<a href='/gestion_paiements/pdfs_temp/fichier_zip.zip' target='_blank'>Télécharger le ZIP</a>";

} else {
    echo "❌ Impossible de créer l'archive ZIP.";
}

<?php
session_start();
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

/** Générations des fichiers à inclure dans le document fusionné */

if (!valider_id('get', 'id', '', 'participations_activites')) {
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}
$id_activite = dechiffrer($_GET['id']);

/** Note de service */
$fichiers[] = genererNoteService($id_activite, false);

// /** Attestation collective */
$fichiers[] = genererAttestation($id_activite, false);

// /**Etat de paiement */
$fichiers[] = genererEtatPaiement2($id_activite, false);

// /** Ordres de virement */
$banques = listeBanques($id_activite);
foreach ($banques as $banque) {
    $chemin_fichier = genererOrdreVirement($id_activite, $banque, false);
    $fichiers[] = $chemin_fichier;
}

// /** Synthèse des ordres de virement */
$fichiers[] = genererSyntheseOrdres($id_activite, false);

// /** Liste des RIBS */
$fichiers[] = genererListeRIBS($id_activite, false);


// Dossiers
$repertoire_pdfs = $dossier_exports_temp;
$dossier_archives = __DIR__ . '/';

// // Création du nom unique pour l'archive ZIP
$nom_zip = 'Les documents.zip';
$chemin_zip = $dossier_archives . $nom_zip;

// Création de l'objet Zip
$zip = new ZipArchive();
if ($zip->open($chemin_zip, ZipArchive::CREATE) !== TRUE) {
    die("Impossible de créer l'archive ZIP.");
}

// Récupération des fichiers PDF
$fichiers = glob($repertoire_pdfs . '/*.pdf');
if (empty($fichiers)) {
    die("Aucun fichier PDF trouvé.");
}

// Ajout des fichiers PDF dans l'archive
foreach ($fichiers as $fichier) {
    $nom_fichier = basename($fichier);
    $zip->addFile($fichier, $nom_fichier);
}
$zip->close();

// Suppression des fichiers PDF originaux après la création du ZIP
foreach ($fichiers as $fichier) {
    unlink($fichier); // Supprime le fichier
}

// Forcer le téléchargement de l'archive ZIP
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . basename($chemin_zip) . '"');
header('Content-Length: ' . filesize($chemin_zip));
readfile($chemin_zip);

// Suppression du ZIP après téléchargement (optionnel)
unlink($chemin_zip);
header('location:' . $_SESSION['previous_url']);
exit;

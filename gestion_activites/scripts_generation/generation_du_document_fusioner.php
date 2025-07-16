<?php
session_start();
require_once(__DIR__ . '/../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php'); // fonction genererHeader
require_once __DIR__ . '/../../vendor/autoload.php';

use setasign\Fpdi\Tcpdf\Fpdi;

if (!valider_id('get', 'id', '', 'participations_activites')) {
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}
$id_activite = $_GET['id'];

$banques = listeBanques($id_activite); // pour l'ordre de virement

// Crée un nouveau PDF
$pdf = new Fpdi();

// $portion_url = obtenirURLcourant(true).'/gestion_activites/scripts_generation/';
$portion_url = 'localhost:3000/gestion_activites/scripts_generation/';

$params =[
    'id' => 2,
    'banque' => $banques[0]
];

$url = $portion_url .'ordre_virement.php?'. http_build_query($params);

$urls = [
    $url
];

$tempFiles = [];

foreach ($urls as $url) {
    // Récupère le contenu PDF généré à partir de l'URL
    $pdfContent = file_get_contents($url);

    if ($pdfContent === false) {
        die("Erreur lors de la récupération du PDF depuis $url");
    }

    // Sauvegarde temporaire du fichier
    $tempFile = tempnam(sys_get_temp_dir(), 'pdf_');
    file_put_contents($tempFile, $pdfContent);
    $tempFiles[] = $tempFile;
}

// Fusion
foreach ($tempFiles as $file) {
    $pageCount = $pdf->setSourceFile($file);
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $pdf->AddPage();
        $tplIdx = $pdf->importPage($pageNo);
        $pdf->useTemplate($tplIdx);
    }
}

// Nettoyage
foreach ($tempFiles as $file) {
    unlink($file);
}

$pdf->Output('fusion.pdf', 'I');



// Liste de tes scripts ou fichiers PDF
// $pdfFiles = [
//     'ordre_virement.php?id=' . $id_activite . '&banque=' . $banques[0], // ou 'fichier1.pdf' s’ils existent déjà
// ];

// Chemin temporaire pour stocker les PDF à fusionner
// $tempFiles = [];

// foreach ($pdfFiles as $script) {
//     // Exécute le script et capture le PDF généré
//     ob_start();
//     include($script); // Ce script doit générer un PDF et faire un echo de son contenu binaire
//     $pdfData = ob_get_clean();

//     $tempFile = tempnam(sys_get_temp_dir(), 'pdf_');
//     file_put_contents($tempFile, $pdfData);
//     $tempFiles[] = $tempFile;
// }

// // Fusion des fichiers PDF temporairement créés
// foreach ($tempFiles as $file) {
//     $pageCount = $pdf->setSourceFile($file);
//     for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
//         $pdf->AddPage();
//         $tplIdx = $pdf->importPage($pageNo);
//         $pdf->useTemplate($tplIdx);
//     }
// }

// // Nettoyage des fichiers temporaires
// foreach ($tempFiles as $file) {
//     unlink($file);
// }

// // Envoie le PDF fusionné
// $pdf->Output('fusion.pdf', 'I');



// ob_start();
// session_start();
// require_once(__DIR__ . '/../../tcpdf/tcpdf.php');
// require_once(__DIR__ . '/../../includes/bdd.php');
// require_once(__DIR__ . '/../../includes/constantes_utilitaires.php'); // fonction genererHeader

// if(!valider_id('get', 'id', '', 'participations_activites')){
//     redirigerVersPageErreur(404, $_SESSION['previous_url']);
// }
// $id_activite = $_GET['id'];

// // 🔹 Récupération du titre
// $sqlTitre = "SELECT nom FROM activites WHERE id = :id_activite";
// $stmtTitre = $bdd->prepare($sqlTitre);
// $stmtTitre->bindParam(':id_activite', $id_activite, PDO::PARAM_INT);
// $stmtTitre->execute();
// $titre_activite = $stmtTitre->fetchColumn();

// // 🔹 Récupération des participants
// $sql = "
// SELECT 
//     p.nom, 
//     p.prenoms,
//     ib.numero_compte AS rib
// FROM participations pa
// INNER JOIN participants p ON pa.id_participant = p.id_participant
// INNER JOIN informations_bancaires ib ON pa.id_compte_bancaire = ib.id
// WHERE pa.id_activite = :id_activite
// ORDER BY p.nom ASC
// ";
// $stmt = $bdd->prepare($sql);
// $stmt->bindParam(':id_activite', $id_activite, PDO::PARAM_INT);
// $stmt->execute();
// $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// // 🔹 Création du PDF
// $pdf = new TCPDF('P', 'mm', 'A4');
// $pdf->setPrintHeader(false);
// $pdf->setMargins(15, 25, 15);
// $pdf->setAutoPageBreak(true, 25);

// $fusion = true;

// // =========================
// // ✅ Page 1 : Ordres de virements
// // =========================
// $banques = listeBanques($id_activite);

// $pdf->AddPage();

// $banque = 'BOA';
// include 'ordre_virement.php';

// $pdf->AddPage();

// $banque = 'UBA';
// include 'ordre_virement.php';

// foreach ($banques as $banque) {
//     echo $banque;
// }
// $pdf->AddPage();
// $infos_header = ['titre' => $titre_activite, 'banque' => $banque];
// genererHeader($pdf, 'Liste_des_RIB', $infos_header, $id_activite);
// $pdf->Ln(15);

// $largeur = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
// $tailles = [0.07, 0.55, 0.28, 0.10]; // N°, Nom, RIB, Observation
// foreach ($tailles as &$t) $t *= $largeur;

// $pdf->SetFont('helvetica', 'B', 11);
// $pdf->SetFillColor(242, 242, 242);
// $pdf->Cell($tailles[0], 10, 'N°', 1, 0, 'C', true);
// $pdf->Cell($tailles[1], 10, 'NOM ET PRENOMS', 1, 0, 'C', true);
// $pdf->Cell($tailles[2], 10, 'RIB', 1, 0, 'C', true);
// $pdf->Cell($tailles[3], 10, 'Observation', 1, 1, 'C', true);

// $pdf->SetFont('helvetica', '', 10);
// foreach ($resultats as $i => $ligne) {
//     $pdf->Cell($tailles[0], 10, $i + 1, 1, 0, 'C');
//     $pdf->Cell($tailles[1], 10, $ligne['nom'] . ' ' . $ligne['prenoms'], 1);
//     $pdf->Cell($tailles[2], 10, $ligne['rib'], 1);
//     $pdf->Cell($tailles[3], 10, '2', 1, 1); // Valeur fixe "2"
// }

// =========================
// ✅ Page 2 : Sans Observation
// =========================
// $pdf->AddPage();
// genererHeader($pdf, 'Liste_des_RIB', $infos_header, $id_activite);
// $pdf->Ln(15);

// $tailles = [0.07, 0.55, 0.38]; // N°, Nom, RIB
// foreach ($tailles as &$t) $t *= $largeur;

// $pdf->SetFont('helvetica', 'B', 11);
// $pdf->SetFillColor(242, 242, 242);
// $pdf->Cell($tailles[0], 10, 'N°', 1, 0, 'C', true);
// $pdf->Cell($tailles[1], 10, 'NOM ET PRENOMS', 1, 0, 'C', true);
// $pdf->Cell($tailles[2], 10, 'RIB', 1, 1, 'C', true);

// $pdf->SetFont('helvetica', '', 10);
// foreach ($resultats as $i => $ligne) {
//     $pdf->Cell($tailles[0], 10, $i + 1, 1, 0, 'C');
//     $pdf->Cell($tailles[1], 10, $ligne['nom'] . ' ' . $ligne['prenoms'], 1);
//     $pdf->Cell($tailles[2], 10, $ligne['rib'], 1, 1);
// }

// 🔚 Sortie
// ob_end_clean();
// $pdf->Output('', 'I');

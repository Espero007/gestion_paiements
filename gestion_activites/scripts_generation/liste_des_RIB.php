<?php
ob_start();
session_start();
require_once(__DIR__ . '/../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

$id_activite = 3;  
$banque = 'UBA'; 

// 🏷️ Récupération du titre de l'activité
$sqlTitre = "SELECT nom FROM activites WHERE id = :id_activite";
$stmtTitre = $bdd->prepare($sqlTitre);
$stmtTitre->bindParam(':id_activite', $id_activite, PDO::PARAM_INT);
$stmtTitre->execute();
$titre_activite = $stmtTitre->fetchColumn();

// 📋 Requête principale
$sql = "
SELECT 
    p.nom, 
    p.prenoms,
    ib.numero_compte AS rib
FROM participations pa
INNER JOIN participants p ON pa.id_participant = p.id_participant
INNER JOIN informations_bancaires ib ON pa.id_compte_bancaire = ib.id
WHERE pa.id_activite = :id_activite
ORDER BY p.nom ASC
";
$stmt = $bdd->prepare($sql);
$stmt->bindParam(':id_activite', $id_activite, PDO::PARAM_INT);
$stmt->execute();
$resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 📄 Création du PDF
$pdf = new TCPDF('P', 'mm', 'A4');
$pdf->setPrintHeader(false);
$pdf->setMargins(15, 25, 15);
$pdf->setAutoPageBreak(true, 25);
$pdf->AddPage();

// 📌 Appel du header
$infos_header = ['titre' => $titre_activite, 'banque' => $banque];
genererHeader($pdf, 'Liste_des_RIB', $infos_header);
$pdf->Ln(15);

// 📐 Définition des tailles
$largeur = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
$tailles = [0.07, 0.45, 0.48];
foreach ($tailles as &$t) { $t *= $largeur; }

$hauteur = 10; // hauteur constante

// 🧾 En-tête du tableau
$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetFillColor(230, 230, 230);
$pdf->MultiCell($tailles[0], $hauteur, 'N°', 1, 'C', true, 0);
$pdf->MultiCell($tailles[1], $hauteur, 'NOM ET PRENOMS', 1, 'C', true, 0);
$pdf->MultiCell($tailles[2], $hauteur, 'RIB', 1, 'C', true, 1);

// 🧍‍♂️ Contenu du tableau
$pdf->SetFont('helvetica', '', 10);
$pdf->SetFillColor(255, 255, 255);

foreach ($resultats as $i => $ligne) {
    $nomPrenoms = $ligne['nom'] . ' ' . $ligne['prenoms'];
    $rib = $ligne['rib'];

    $pdf->MultiCell($tailles[0], $hauteur, $i + 1, 1, 'C', false, 0);
    $pdf->MultiCell($tailles[1], $hauteur, $nomPrenoms, 1, 'C', false, 0);
    $pdf->MultiCell($tailles[2], $hauteur, $rib, 1, 'C', false, 1);
}

// 📤 Sortie du document
ob_end_clean();
$pdf->Output('', 'I');

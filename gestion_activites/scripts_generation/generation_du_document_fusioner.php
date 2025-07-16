<?php
ob_start();
session_start();
require_once(__DIR__ . '/../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php'); // fonction genererHeader

$id_activite = 3;
$banque = 'UBA';

// 🔹 Récupération du titre
$sqlTitre = "SELECT nom FROM activites WHERE id = :id_activite";
$stmtTitre = $bdd->prepare($sqlTitre);
$stmtTitre->bindParam(':id_activite', $id_activite, PDO::PARAM_INT);
$stmtTitre->execute();
$titre_activite = $stmtTitre->fetchColumn();

// 🔹 Récupération des participants
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

// 🔹 Création du PDF
$pdf = new TCPDF('P', 'mm', 'A4');
$pdf->setPrintHeader(false);
$pdf->setMargins(15, 25, 15);
$pdf->setAutoPageBreak(true, 25);

// =========================
// ✅ Page 1 : Avec colonne Observation
// =========================
$pdf->AddPage();
$infos_header = ['titre' => $titre_activite, 'banque' => $banque];
genererHeader($pdf, 'Liste_des_RIB', $infos_header);
$pdf->Ln(15);

$largeur = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
$tailles = [0.07, 0.55, 0.28, 0.10]; // N°, Nom, RIB, Observation
foreach ($tailles as &$t) $t *= $largeur;

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetFillColor(242, 242, 242);
$pdf->Cell($tailles[0], 10, 'N°', 1, 0, 'C', true);
$pdf->Cell($tailles[1], 10, 'NOM ET PRENOMS', 1, 0, 'C', true);
$pdf->Cell($tailles[2], 10, 'RIB', 1, 0, 'C', true);
$pdf->Cell($tailles[3], 10, 'Observation', 1, 1, 'C', true);

$pdf->SetFont('helvetica', '', 10);
foreach ($resultats as $i => $ligne) {
    $pdf->Cell($tailles[0], 10, $i + 1, 1, 0, 'C');
    $pdf->Cell($tailles[1], 10, $ligne['nom'] . ' ' . $ligne['prenoms'], 1);
    $pdf->Cell($tailles[2], 10, $ligne['rib'], 1);
    $pdf->Cell($tailles[3], 10, '2', 1, 1); // Valeur fixe "2"
}

// =========================
// ✅ Page 2 : Sans Observation
// =========================
$pdf->AddPage();
genererHeader($pdf, 'Liste_des_RIB', $infos_header);
$pdf->Ln(15);

$tailles = [0.07, 0.55, 0.38]; // N°, Nom, RIB
foreach ($tailles as &$t) $t *= $largeur;

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetFillColor(242, 242, 242);
$pdf->Cell($tailles[0], 10, 'N°', 1, 0, 'C', true);
$pdf->Cell($tailles[1], 10, 'NOM ET PRENOMS', 1, 0, 'C', true);
$pdf->Cell($tailles[2], 10, 'RIB', 1, 1, 'C', true);

$pdf->SetFont('helvetica', '', 10);
foreach ($resultats as $i => $ligne) {
    $pdf->Cell($tailles[0], 10, $i + 1, 1, 0, 'C');
    $pdf->Cell($tailles[1], 10, $ligne['nom'] . ' ' . $ligne['prenoms'], 1);
    $pdf->Cell($tailles[2], 10, $ligne['rib'], 1, 1);
}

// 🔚 Sortie
ob_end_clean();
$pdf->Output('', 'I');

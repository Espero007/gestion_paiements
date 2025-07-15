<?php
ob_start();
session_start();
require_once(__DIR__ . '/../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php'); // Inclut la fonction genererHeader

$id_activite = 2;//l'id de l'activite est a recuperer avec un get  
$banque = 'UBA'; // fixe ou via GET/POST si besoin

// Récupération du titre de l'activité
$sqlTitre = "SELECT nom FROM activites WHERE id = :id_activite";
$stmtTitre = $bdd->prepare($sqlTitre);
$stmtTitre->bindParam(':id_activite', $id_activite, PDO::PARAM_INT);
$stmtTitre->execute();
$titre_activite = $stmtTitre->fetchColumn();

// Requête principale
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

// Création du PDF
$pdf = new TCPDF('P', 'mm', 'A4');
$pdf->setPrintHeader(false);
$pdf->setMargins(15, 25, 15);
$pdf->setAutoPageBreak(true, 25);
$pdf->AddPage();

// 🧾 Appel du header
$infos_header = ['titre' => $titre_activite, 'banque' => $banque];
genererHeader($pdf, 'Liste_des_RIB', $infos_header);
$pdf->Ln(15);

// Tableau
$largeur = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
$tailles = [0.07, 0.45, 0.48];
foreach ($tailles as &$t) { $t *= $largeur; }

$pdf->SetFont('helvetica', 'B', 11);
$pdf->SetFillColor(242, 242, 242);
$pdf->MultiCellCell($tailles[0], 10, 'N°', 1, 0, 'C', true);
$pdf->MultiCellCell($tailles[1], 10, 'NOM ET PRENOMS', 1, 0, 'C', true);
$pdf->MultiCellCell($tailles[2], 10, 'RIB', 1, 1, 'C', true);

$pdf->SetFont('helvetica', '', 10);
$pdf->SetFillColor(255, 255, 255);
foreach ($resultats as $i => $ligne) {
    $pdf->Cell($tailles[0], 10, $i + 1, 1, 0, 'C');
    $pdf->Cell($tailles[1], 10, $ligne['nom'] . ' ' . $ligne['prenoms'], 1);
    $pdf->Cell($tailles[2], 10, $ligne['rib'], 1, 1);
}

// Sortie
ob_end_clean();

    $pdf->Output('', 'I');





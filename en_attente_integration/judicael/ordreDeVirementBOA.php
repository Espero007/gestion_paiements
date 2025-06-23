<?php
require_once('../../tcpdf/tcpdf.php');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=gestion_paiements;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}


$sql = "
SELECT 
    CONCAT(p.nom, ' ', p.prenoms) AS nom_prenom,
    t.nom AS qualite,
    i.banque,
    i.numero_compte AS rib,
    (a.taux_journalier * pa.nombre_jours + a.taux_taches * pa.nombre_taches) AS montant
FROM participations pa
JOIN participants p ON pa.id_participant = p.id_participant
JOIN titres t ON pa.id_titre = t.id_titre
JOIN informations_bancaires i ON pa.id_compte_bancaire = i.id
JOIN activites a ON pa.id_activite = a.id
WHERE i.banque = 'BOA'
";

$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);


$total = 0;
if (!empty($rows)) {
    $total = array_sum(array_map(function ($r) {
        return is_numeric($r['montant']) ? $r['montant'] : 0;
    }, $rows));
}


$pdf = new TCPDF();
$pdf->AddPage();
$pageWidth = $pdf->getPageWidth();
$lineHeight = 6;
$pdf->SetFont('helvetica', 'B', 12);


$pdf->Cell($pageWidth / 2, $lineHeight, "REPUBLIQUE DU BENIN", 0, 0, 'L');
$pdf->Cell($pageWidth / 2, $lineHeight, "COTONOU, LE MARDI 26 DÉCEMBRE 2023", 0, 1, 'R');
$pdf->Cell($pageWidth / 2, $lineHeight, "* * * * *", 0, 1, 'L');
$pdf->Cell($pageWidth / 2, $lineHeight, "MINISTÈRE DE ......................", 0, 1, 'L');
$pdf->Cell($pageWidth / 2, $lineHeight, "* * * * *", 0, 1, 'L');
$pdf->Cell($pageWidth / 2, $lineHeight, "DIRECTION DES .....................", 0, 0, 'L');
$pdf->Cell($pageWidth / 2, $lineHeight, "ORDRE DE VIREMENT BOA", 0, 1, 'R');
$pdf->Cell($pageWidth / 2, $lineHeight, "* * * * *", 0, 1, 'L');
$pdf->Cell($pageWidth / 2, $lineHeight, "SERVICE ...........................", 0, 1, 'L');
$pdf->Cell($pageWidth / 2, $lineHeight, "* * * * *", 0, 1, 'L');

$pdf->Ln(2);
$pdf->SetFont('helvetica', '', 11);
$pdf->MultiCell($pageWidth, 6, "DES INDEMNITÉS ET FRAIS D’ENTRETIEN ACCORDÉS AUX\nMEMBRES DE LA COMMISSION CHARGÉE DE …", 0, 'C');
$pdf->Ln(4);


if (empty($rows)) {
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, "Aucune donnée trouvée pour ce virement.", 0, 1, 'C');
} else {
    
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(10, 8, 'N°', 1, 0, 'C');
    $pdf->Cell(40, 8, 'NOM ET PRÉNOMS', 1, 0, 'C');
    $pdf->Cell(30, 8, 'QUALITÉ', 1, 0, 'C');
    $pdf->Cell(30, 8, 'MONTANT', 1, 0, 'C');
    $pdf->Cell(20, 8, 'BANQUE', 1, 0, 'C');
    $pdf->Cell(60, 8, 'RIB', 1, 1, 'C');

    $pdf->SetFont('helvetica', '', 10);
    $n = 1;
    foreach ($rows as $r) {
        $pdf->Cell(10, 8, $n++, 1);
        $pdf->Cell(40, 8, $r['nom_prenom'], 1);
        $pdf->Cell(30, 8, $r['qualite'], 1);
        $pdf->Cell(30, 8, number_format($r['montant'], 0, ',', ' ') . ' FCFA', 1, 0, 'R');
        $pdf->Cell(20, 8, $r['banque'], 1);
        $pdf->Cell(60, 8, $r['rib'], 1, 1);
    }

   
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(80, 8, 'TOTAL', 1);
    $pdf->Cell(110, 8, number_format($total, 0, ',', ' ') . ' FCFA', 1, 1, 'R');


    $pdf->Ln(8);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(190, 6, utf8_decode("Arrêté le présent ordre de virement à la somme de ......... Francs CFA"), 0, 'L');
    $pdf->Ln(5);
    $pdf->MultiCell(90, 8, "LE C/GAP-CEC\nChun BIANG", 0, 'L', false, 0);
    $pdf->MultiCell(90, 8, "LE CMAP\nHui P. BOBO", 0, 'R', false, 1);
}

ob_end_clean();
$pdf->Output('ordre_virement_boa.pdf', 'I');
?>
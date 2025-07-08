<?php
require_once(__DIR__ . '/../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../includes/bdd.php');



// Récupération des données
$sql = "
SELECT 
    nom_prenom,
    qualite,
    banque,
    rib,
    montant
FROM virements_uba
WHERE banque = 'UBA'
";
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul du total
$total = array_sum(array_column($rows, 'montant'));

// Initialisation PDF
$pdf = new TCPDF();
$pdf->setPrintHeader(false);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);

// En-tête institutionnel
$bloc_gauche = <<<EOD
RÉPUBLIQUE DU BÉNIN
    *-*-*-*-*
MINISTÈRE DE L’ENSEIGNEMENT SUPÉRIEUR ET SECONDAIRE
    *-*-*-*-*
DIRECTION DES …
    *-*-*-*-*
SERVICE …
EOD;

$largeurBloc = ($pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right']) / 2;
$x_gauche = $pdf->getMargins()['left'];
$x_droite = $x_gauche + $largeurBloc;
$y_depart = $pdf->GetY();

$pdf->SetXY($x_gauche, $y_depart);
$pdf->MultiCell($largeurBloc, 6, $bloc_gauche, 0, 'L', false);

$date = strtoupper("COTONOU, LE " . strftime('%A %d %B %Y'));
$pdf->SetXY($x_droite, $y_depart);
$pdf->MultiCell($largeurBloc, 5, $date, 0, 'R', false);
$pdf->Ln(5);
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetX($x_droite);
$pdf->MultiCell($largeurBloc, 6, "ORDRE DE VIREMENT BOA", 0, 'R', false);
$pdf->Ln(5);
$pdf->SetFont('helvetica', '', 11);
$pdf->SetX($x_droite);
$pdf->MultiCell($largeurBloc, 5, strtoupper("DES INDEMNITÉS ET FRAIS D’ENTRETIEN ACCORDÉS AUX MEMBRES DE LA COMMISSION CHARGÉE DE …"), 0, 'C');

$pdf->Ln(15);

// En-tête du tableau
$pdf->SetFont('helvetica', 'B', 9);
$pdf->Cell(8, 8, 'N°', 1, 0, 'C');
$pdf->Cell(45, 8, 'NOM ET PRÉNOMS', 1, 0, 'C');
$pdf->Cell(30, 8, 'QUALITÉ', 1, 0, 'C');
$pdf->Cell(35, 8, 'MONTANT', 1, 0, 'C');
$pdf->Cell(18, 8, 'BANQUE', 1, 0, 'C');
$pdf->Cell(54, 8, 'RIB', 1, 1, 'C');

// Corps du tableau
$pdf->SetFont('helvetica', '', 9);
$n = 1;
foreach ($rows as $r) {
    // Gestion de saut de page
    if ($pdf->GetY() > ($pdf->getPageHeight() - 30)) {
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(8, 8, 'N°', 1, 0, 'C');
        $pdf->Cell(45, 8, 'NOM ET PRÉNOMS', 1, 0, 'C');
        $pdf->Cell(30, 8, 'QUALITÉ', 1, 0, 'C');
        $pdf->Cell(35, 8, 'MONTANT', 1, 0, 'C');
        $pdf->Cell(18, 8, 'BANQUE', 1, 0, 'C');
        $pdf->Cell(54, 8, 'RIB', 1, 1, 'C');
        $pdf->SetFont('helvetica', '', 9);
    }

    $pdf->Cell(8, 8, $n++, 1, 0, 'C');
    $pdf->Cell(45, 8, $r['nom_prenom'], 1, 0, 'L');
    $pdf->Cell(30, 8, $r['qualite'], 1, 0, 'C');
    $pdf->Cell(35, 8, number_format($r['montant'], 0, ',', ' ') . ' FCFA', 1, 0, 'R');
    $pdf->Cell(18, 8, $r['banque'], 1, 0, 'C');
    $pdf->Cell(54, 8, $r['rib'], 1, 1, 'L');
}

// Ligne de total
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(83, 8, 'TOTAL    (  )', 1, 0, 'C'); // 8 + 45 + 30
$pdf->Cell(35, 8, number_format($total, 0, ',', ' ') . ' FCFA', 1, 0, 'R');
$pdf->Cell(18, 8, '', 1, 0);
$pdf->Cell(54, 8, '', 1, 1);

    $pdf->Ln(8);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(190, 6, "Arrêté le présent ordre de virement à la somme de ( $total ) Francs CFA", 0, 'C');
    $pdf->Ln(5);

    $pdf->MultiCell(90, 8, strtoupper("YO") . "\n \n \n" . "YO", 0, 'L', false, 0);
    $pdf->MultiCell(90, 8, strtoupper("YO") . "\n \n \n" . "YO", 0, 'R', false, 1);

ob_end_clean();
$pdf->Output('ordre_virement_uba.pdf', 'I');
?>

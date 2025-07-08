<?php
require_once(__DIR__.'/../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../includes/bdd.php');


// Requête : récupération des données
$sql = "SELECT * FROM informations_bancaires";
$stmt = $bdd->query($sql);
$banques = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcul du total (si la table n'est pas vide)
$total = empty($banques) ? 0 : array_sum(array_column($banques, 'montant'));

// Création du PDF
$pdf = new TCPDF('P', 'mm', 'A1');
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, "TABLEAU RÉCAPITULATIF PAR BANQUE", 0, 1, 'C');
$pdf->Ln(5);

// Gestion du cas vide
if (empty($banques)) {
    $pdf->SetFont('helvetica', 'I', 12);
    $pdf->Cell(0, 10, "Aucune donnée disponible dans la table 'recap_bancaire'.", 0, 1, 'C');
} else {
    // Largeur dynamique
    $nbBanques = count($banques);
    $largeurCellule = floor(40); // 170 au lieu de 190 pour marges
    $largeurLabel = 30;

    // Ligne 1 : entête des banques
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell($largeurLabel, 8, '', 1); // Coin vide
    foreach ($banques as $row) {
        $pdf->Cell($largeurCellule, 8, $row['banque'], 1, 0, 'C');
    }
    $pdf->Ln();

    // Ligne 2 : montants par banque
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell($largeurLabel, 8, 'MONTANT', 1, 0, 'L');
    foreach ($banques as $row) {
        $pdf->Cell($largeurCellule, 8, number_format($row['montant'], 0, ',', ' ') . ' FCFA', 1, 0, 'R');
    }
    $pdf->Ln();

    // Ligne 3 : total
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell($largeurLabel, 8, 'TOTAL', 1, 0, 'L');
    $pdf->Cell($largeurCellule * $nbBanques, 8, number_format($total, 0, ',', ' ') . ' FCFA', 1, 1, 'R');
}

ob_end_clean();
$pdf->Output('recapitulatif_par_banque_colonnes.pdf', 'I');
?>

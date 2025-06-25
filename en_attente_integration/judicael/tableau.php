<?php
require_once(__DIR__.'/../../tcpdf/tcpdf.php');

try {
    $pdo = new PDO('mysql:host=localhost;dbname=gestion_paiements;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}


$sql = "
    SELECT 
        i.banque,
        SUM(a.taux_journalier * pa.nombre_jours + a.taux_taches * pa.nombre_taches) AS montant
    FROM participations pa
    JOIN informations_bancaires i ON pa.id_compte_bancaire = i.id
    JOIN activites a ON pa.id_activite = a.id
    GROUP BY i.banque
    ORDER BY montant DESC
";

$stmt = $pdo->query($sql);
$banques = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = array_sum(array_column($banques, 'montant'));

// Création du PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, "TABLEAU RÉCAPITULATIF PAR BANQUE", 0, 1, 'C');
$pdf->Ln(3);


if (empty($banques)) {
    $pdf->SetFont('helvetica', 'I', 12);
    $pdf->Cell(0, 10, "Aucune participation enregistrée pour le moment.", 0, 1, 'C');
} else {
   
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(10, 8, 'N°', 1, 0, 'C');
    $pdf->Cell(120, 8, 'BANQUE', 1, 0, 'C');
    $pdf->Cell(60, 8, 'MONTANT', 1, 1, 'C');

   
    $pdf->SetFont('helvetica', '', 11);
    $n = 1;
    foreach ($banques as $row) {
        $pdf->Cell(10, 8, $n++, 1, 0, 'C');
        $pdf->Cell(120, 8, $row['banque'], 1);
        $pdf->Cell(60, 8, number_format($row['montant'], 0, ',', ' ') . ' FCFA', 1, 1, 'R');
    }


    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(130, 8, 'TOTAL GÉNÉRAL', 1, 0, 'R');
    $pdf->Cell(60, 8, number_format($total, 0, ',', ' ') . ' FCFA', 1, 1, 'R');
}

ob_end_clean();
$pdf->Output('recapitulatif_par_banque.pdf', 'I');
?>

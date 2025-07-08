<?php
require_once('tcpdf/tcpdf.php');

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

$activiteStmt = $pdo->query("SELECT titre_financier, financier, titre_responsable, premier_responsable FROM activites LIMIT 1");
$activite = $activiteStmt->fetch(PDO::FETCH_ASSOC);

$titreFinancier = $activite['titre_financier'] ?? '';
$nomFinancier = $activite['financier'] ?? '';
$titreResponsable = $activite['titre_responsable'] ?? '';
$nomResponsable = $activite['premier_responsable'] ?? '';

$pdf = new TCPDF();
$pdf->setPrintHeader(false);
$pdf->AddPage();
$pdf->SetFont('helvetica', '', 10);


$bloc_gauche = <<<EOD
RÉPUBLIQUE DU BÉNIN
    *-*-*-*-*
MINISTÈRE DE L’ENSEIGNEMENT SUPÉRIEUR ET SECONDAIRE
    *-*-*-*-*
DIRECTION DES …
    *-*-*-*-*
SERVICE …
EOD;


$largeurPage = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
$largeurBloc = $largeurPage / 2;
$x_gauche = $pdf->getMargins()['left'];
$x_droite = $x_gauche + $largeurBloc;
$y_depart = $pdf->GetY();

$pdf->SetXY($x_gauche, $y_depart);
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell($largeurBloc, 6, $bloc_gauche, 0, 'L', false);


setlocale(LC_TIME, 'fr_FR.utf8', 'fra');
$date = strtoupper("COTONOU, LE " . strftime('%A %d %B %Y'));

$pdf->SetXY($x_droite, $y_depart);
$pdf->SetFont('helvetica', '', 10);
$pdf->MultiCell($largeurBloc, 5, $date, 0, 'R', false);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetX($x_droite);
$pdf->MultiCell($largeurBloc, 6, "ORDRE DE VIREMENT BOA", 0, 'R', false);

$pdf->SetFont('helvetica', '', 11);
$ligne3 = strtoupper("DES INDEMNITÉS ET FRAIS D’ENTRETIEN ACCORDÉS AUX MEMBRES DE LA COMMISSION CHARGÉE DE …");
$pdf->SetX($x_droite);
$pdf->MultiCell($largeurBloc, 5, $ligne3, 0, 'C');

$pdf->SetY(max($pdf->GetY(), $pdf->GetY()) + 5);


$hauteurPage = $pdf->getPageHeight() - $pdf->getMargins()['top'] - $pdf->getMargins()['bottom'];
$yTableStart = $pdf->GetY();
$hauteurDispo = $hauteurPage - ($yTableStart - $pdf->getMargins()['top']);


$nbLignes = count($rows) + 2; 


$hauteurLigne = max(8, $hauteurDispo / $nbLignes);


if (empty($rows)) {
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, "Aucune donnée trouvée pour ce virement.", 0, 1, 'C');
} else {
    
    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(8, $hauteurLigne, 'N°', 1, 0, 'C');
    $pdf->Cell(45, $hauteurLigne, 'NOM ET PRÉNOMS', 1, 0, 'C');
    $pdf->Cell(35, $hauteurLigne, 'QUALITÉ', 1, 0, 'C');
    $pdf->Cell(30, $hauteurLigne, 'MONTANT', 1, 0, 'C');
    $pdf->Cell(18, $hauteurLigne, 'BANQUE', 1, 0, 'C');
    $pdf->Cell(54, $hauteurLigne, 'RIB', 1, 1, 'C');

    
    $pdf->SetFont('helvetica', '', 9);
    $n = 1;
    foreach ($rows as $r) {
        $pdf->Cell(8, $hauteurLigne, $n++, 1, 0, 'C');
        $pdf->Cell(45, $hauteurLigne, $r['nom_prenom'], 1, 0, 'L'); 
        $pdf->Cell(35, $hauteurLigne, $r['qualite'], 1, 0, 'C');
        $pdf->Cell(30, $hauteurLigne, number_format($r['montant'], 0, ',', ' ') . ' FCFA', 1, 0, 'C');
        $pdf->Cell(18, $hauteurLigne, $r['banque'], 1, 0, 'C');
        $pdf->Cell(54, $hauteurLigne, $r['rib'], 1, 1, 'C');

    }

  
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(80, $hauteurLigne, 'TOTAL  ( )', 1);
    $pdf->Cell(110, $hauteurLigne, number_format($total, 0, ',', ' ') . ' FCFA', 1, 1, 'C');

    $pdf->Ln(8);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->MultiCell(190, 6, utf8_decode("Arrêté le présent ordre de virement à la somme de ......... Francs CFA"), 0, 'L');
    $pdf->Ln(5);

    $pdf->MultiCell(90, 8, strtoupper($titreFinancier) . "\n" . $nomFinancier, 0, 'L', false, 0);
    $pdf->MultiCell(90, 8, strtoupper($titreResponsable) . "\n" . $nomResponsable, 0, 'R', false, 1);
}

ob_end_clean();
$pdf->Output('ordre_virement_boa.pdf', 'I');
?>

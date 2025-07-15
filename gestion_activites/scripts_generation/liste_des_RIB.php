<?php
// Inclusions
session_start();
require_once(__DIR__ . '/../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

// // Validations pour les informations à récupérer par GET
// $redirect = true;

// if (valider_id('get', 'id', $bdd, 'participations_activites')) {
//     // Il faut maintenant s'assurer que la banque reçue est valable
//     $id_activite = $_GET['id'];
//     if (isset($_GET['banque']) && in_array($_GET['banque'], listeBanques($id_activite))) {
//         $banque = $_GET['banque'];
//         $redirect = false;
//     }
// }

// if ($redirect) {
//     redirigerVersPageErreur(404, $_SESSION['previous_url']);
// }

// $banque = $_GET['banque'] ?? 'Coris Bénin';
$id_activite = 3;

// Récupérons les participants associés à l'activité qui ont comme banque UBA

$stmt = "
SELECT
    pa.id_participant,
    a.type_activite,
    p.nom, 
    p.prenoms,
    t.nom as qualite,
    t.indemnite_forfaitaire,
    ib.banque,
    ib.numero_compte as rib,
    a.taux_journalier,
    a.taux_taches,
    a.frais_deplacement_journalier as fdj,
    pa.nombre_jours,
    pa.nombre_taches,
    a.nom as titre_activite,
    a.titre_financier,
    a.financier,
    a.premier_responsable,
    a.titre_responsable
FROM participations pa
INNER JOIN participants p ON pa.id_participant=p.id_participant
INNER JOIN titres t ON pa.id_titre = t.id_titre
INNER JOIN informations_bancaires ib ON p.id_participant=ib.id_participant
INNER JOIN activites a ON pa.id_activite=a.id
WHERE pa.id_activite=$id_activite
";
$stmt = $bdd->prepare($stmt);
//$stmt->bindParam('banque', $banque);
$stmt->execute();
$resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Configuration du document
$pdf = new TCPDF('P', 'mm', 'A4');
$pdf->AddFont('trebucbd', '', 'trebucbd.php');
$pdf->setPrintHeader(false); // Retrait de la ligne du haut qui s'affiche par défaut sur une page
//configuration_pdf($pdf, $_SESSION['nom'] . ' ' . $_SESSION['prenoms'], 'Ordre de virement ' . $banque);
$pdf->setMargins(15, 25, 15, true);
$pdf->setAutoPageBreak(true, 25); // marge bas = 25 pour footer
$pdf->AddPage();

;

$pdf->Ln(20);

$largeurPage = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
$tailles_colonnes = [0.05, 0.22, 0.15, 0.15, 0.15, 0.28];

foreach ($tailles_colonnes as $index => $taille) {
    $tailles_colonnes[$index] = $taille * $largeurPage;
}

// Tableau
// Entête
$pdf->setFont('trebuc', '', 10);
$pdf->setFillColor(242, 242, 242); // #f2f2f2
$hauteur = 10;
$pdf->Cell($tailles_colonnes[0], $hauteur, 'N°', 1, 0, 'C', true); // 5%
$pdf->Cell($tailles_colonnes[1], $hauteur, strtoupper('Nom et prenoms'), 1, 0, 'C', true);
$pdf->Cell($tailles_colonnes[5], $hauteur, strtoupper('Rib'), 1, 0, 'C', true);
$pdf->Ln();

$total = 0;

for ($i = 0; $i < count($resultats); $i++) {
    $ligne = $resultats[$i];
    
    $pdf->setFont('trebuc', '', 10);
    $pdf->setFillColor(255, 255, 255); // #fff
    $pdf->Cell($tailles_colonnes[0], $hauteur, $i + 1, 1, 0, 'C');
    $pdf->Cell($tailles_colonnes[1], $hauteur, $ligne['nom'] . ' ' . $ligne['prenoms'], 1);
    $pdf->Cell($tailles_colonnes[5], $hauteur, $ligne['rib'], 1, 0, 'C');
    $pdf->Ln();
}

// //Sortie du pdf
$pdf->Output('Ordre de virement ' . '.pdf', 'I');

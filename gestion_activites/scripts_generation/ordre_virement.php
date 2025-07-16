<?php
// Inclusions
session_start();
require_once(__DIR__ . '/../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

// Validations pour les informations à récupérer par GET
$redirect = true;

if (valider_id('get', 'id', $bdd, 'participations_activites')) {
    // Il faut maintenant s'assurer que la banque reçue est valable
    $id_activite = $_GET['id'];
    if (isset($_GET['banque']) && in_array($_GET['banque'], listeBanques($id_activite))) {
        $banque = $_GET['banque'];
        $redirect = false;
    }
}

if ($redirect) {
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}

// $banque = $_GET['banque'] ?? 'Coris Bénin';
// $id_activite = $_GET['id'] ?? 2;

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
WHERE pa.id_activite=$id_activite AND ib.banque =:banque 
";
$stmt = $bdd->prepare($stmt);
$stmt->bindParam('banque', $banque);
$stmt->execute();
$resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Configuration du document
$pdf = new TCPDF('P', 'mm', 'A4');
$pdf->AddFont('trebucbd', '', 'trebucbd.php');
$pdf->setPrintHeader(false); // Retrait de la ligne du haut qui s'affiche par défaut sur une page
configuration_pdf($pdf, $_SESSION['nom'] . ' ' . $_SESSION['prenoms'], 'Ordre de virement ' . $banque);
$pdf->setMargins(15, 25, 15, true);
$pdf->setAutoPageBreak(true, 25); // marge bas = 25 pour footer
$pdf->AddPage();

// Header
$informations_necessaires = ['titre' => $resultats[0]['titre_activite'], 'banque' => $banque];
// $informations_necessaires = ['titre' => $resultats[0]['titre_activite']];
genererHeader($pdf, 'ordre_virement', $informations_necessaires, $id_activite);
// genererHeader($pdf, 'note_service', $informations_necessaires);

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
$pdf->Cell($tailles_colonnes[2], $hauteur, strtoupper('Qualite'), 1, 0, 'C', true);
$pdf->Cell($tailles_colonnes[3], $hauteur, strtoupper('Montant'), 1, 0, 'C', true);
$pdf->Cell($tailles_colonnes[4], $hauteur, strtoupper('Banque'), 1, 0, 'C', true);
$pdf->Cell($tailles_colonnes[5], $hauteur, strtoupper('Rib'), 1, 0, 'C', true);
$pdf->Ln();

$total = 0;

for ($i = 0; $i < count($resultats); $i++) {
    $ligne = $resultats[$i];
    $montant = montantParticipant($ligne['id_participant'], $id_activite);

    // Une ligne

    $pdf->setFont('trebuc', '', 10);
    $pdf->setFillColor(255, 255, 255); // #fff
    // N°
    $pdf->Cell($tailles_colonnes[0], $hauteur, $i + 1, 1, 0, 'C');
    // Nom et prénoms
    $pdf->Cell($tailles_colonnes[1], $hauteur, $ligne['nom'] . ' ' . $ligne['prenoms'], 1);
    // Qualité
    $pdf->Cell($tailles_colonnes[2], $hauteur, $ligne['qualite'], 1, 0, 'C');
    // Montant
    $pdf->Cell($tailles_colonnes[3], $hauteur, number_format($montant, 0, ',', '.'), 1, 0, 'C');
    // Banque
    $pdf->Cell($tailles_colonnes[4], $hauteur, $banque, 1, 0, 'C');
    // Rib
    $pdf->Cell($tailles_colonnes[5], $hauteur, $ligne['rib'], 1, 0, 'C');
    $pdf->Ln();

    $total += $montant;
}

// Dernière ligne du tableau pour le total

$pdf->setFont('trebucbd', '', 10);
$pdf->setFillColor(242, 242, 242); // #f2f2f2
// Total ( )
$pdf->Cell($tailles_colonnes[0] + $tailles_colonnes[1] + $tailles_colonnes[2], $hauteur, strtoupper('Total ( )'), 1, 0, 'C', true);
// Montant
$pdf->Cell($tailles_colonnes[3], $hauteur, number_format($total, 0, ',', '.'), 1, 0, 'C', true);
// Banque
$pdf->Cell($tailles_colonnes[4], $hauteur, '', 1, 0, 'C', true);
// Rib
$pdf->Cell($tailles_colonnes[5], $hauteur, '', 1, 0, 'C', true);
$pdf->Ln(15);

// On s'attaque à la phrase en dessous du tableau
$formatter = new NumberFormatter('fr_FR', NumberFormatter::SPELLOUT);
$pdf->MultiCell(0, 10, "Arrêté le présent ordre de virement à la somme de " . mb_strtoupper($formatter->format($total), 'UTF-8') . "\n (" . number_format($total, 0, ',', '.') . ") Francs CFA", 0, 'C');
$pdf->Ln(8);

// Bloc du bas avec le financier et le premier responsable

$bloc_gauche = mb_strtoupper($resultats[0]['financier']);
$bloc_droite = mb_strtoupper($resultats[0]['premier_responsable']);
afficherTexteDansDeuxBlocs($pdf, $bloc_gauche, $bloc_droite, 'trebucbd', 10, 5, 'C', '', 'C', '');

$bloc_gauche = mb_strtoupper($resultats[0]['titre_financier']);
$bloc_droite = mb_strtoupper($resultats[0]['titre_responsable']);
afficherTexteDansDeuxBlocs($pdf, $bloc_gauche, $bloc_droite, 'trebucbd', 10, 2, 'C', 'U', 'C', 'U');

// //Sortie du pdf
$pdf->Output('Ordre de virement ' . $banque . '.pdf', 'I');

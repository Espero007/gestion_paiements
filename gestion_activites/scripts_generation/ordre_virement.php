<?php
// Inclusions
require_once(__DIR__ . '/../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

// Header

class Ordre_Virement extends TCPDF
{
    public function Header()
    {
        // Pour le formattage de la date en français

        $formatter = new IntlDateFormatter(
            'fr_FR',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            'Africa/Lagos',
            IntlDateFormatter::GREGORIAN
        );

        // Charger la police du pdf
        $this->setFont('trebuc', '', 11);
        $this->setY(8); // on descend de 5mm du haut avant de débuter le dessin du header

        // Gestion des deux blocs du header
        $bloc_gauche =  strtoupper("REPUBLIQUE DU BENIN\n*-*-*-*-*\nMINISTERE DE L'ENSEIGNEMENT SUPERIEUR ET SECONDAIRE\n*-*-*-*-*\nDIRECTION DES ............\n*-*-*-*-*\nSERVICE ............");

        $bloc_droite = strtoupper("Cotonou, le " . $formatter->format(new DateTime()) . "\n");

        //Largeur totale disponible entre les marges
        $largeurPage = $this->getPageWidth() - $this->getMargins()['left'] - $this->getMargins()['right'];

        // Largeur d'un bloc
        $largeurBloc = $largeurPage / 2;

        // Sauvegarder la position Y
        $y = $this->GetY();
        // Sauvegarder la position de x à droite
        $x_droite = $this->getMargins()['left'] + $largeurBloc;

        $this->setXY($this->getMargins()['left'], $y);
        $this->MultiCell($largeurBloc, 5, $bloc_gauche, 0, 'C');

        //Bloc de droite (sur la même ligne que le bloc de gauche)
        $this->setXY($this->getMargins()['left'] + $largeurBloc, $y);
        // $this->MultiCell($largeurBloc, 5, $bloc_droite, 0, 'C');

        // Ligne 1
        $ligne1 = strtoupper("Cotonou, le " . $formatter->format(new DateTime()));
        $this->Cell(0, 5, $ligne1, 0, 1, 'C');
        $this->Ln(5);

        // $this->setXY($this->getMargins()['left'], $y);
        // Ligne 2
        $ligne2 = strtoupper("ordre de virement UBA");
        $this->setFont('trebucbd', '', '12');
        $this->setX($x_droite);
        $this->Cell(0, 5, $ligne2, 0, 1, 'C');
        $this->Ln(5);

        // Ligne 3
        $ligne3 = strtoupper("des indemnites et frais d'entretien accordes aux membres de la commission chargee de ...");
        $this->setFont('trebuc', '', '11');
        $this->setX($x_droite);
        $this->MultiCell($largeurBloc, 5, $ligne3, 0, 'C');

        // Ligne de séparation
        // $this->Ln(15);
        // $this->Line($this->getMargins()['left'], $this->GetY(), $this->getPageWidth()-$this->getMargins()['right'], $this->GetY());
        // $this->Ln(2);

    }
}

$pdf = new Ordre_Virement('P', 'mm', 'A4');
$pdf->AddFont('trebucbd', '', 'trebucbd.php');

// Configuration du document
configuration_pdf($pdf, 'Tobi', 'Ordre de virement');
$pdf->setMargins(15, 20, 15);
$pdf->setAutoPageBreak(true, 25); // marge bas = 25 pour footer
$pdf->AddPage();

$pdf->Ln(40);

$banque = 'UBA';
$id_activite = 49;

// Récupérons les participants associés à l'activité qui ont comme banque UBA

$stmt = "
SELECT a.type_activite, p.nom, p.prenoms, t.nom as qualite, t.indemnite_forfaitaire, ib.banque, ib.numero_compte as rib
FROM participations pa
INNER JOIN participants p ON pa.id_participant=p.id_participant
INNER JOIN titres t ON pa.id_titre = t.id_titre
INNER JOIN informations_bancaires ib ON pa.id_compte_bancaire=ib.id
INNER JOIN activites a ON pa.id_activite=a.id
WHERE pa.id_activite=$id_activite AND ib.banque =:banque 
";
$stmt = $bdd->prepare($stmt);
$stmt->bindParam('banque', $banque);
$stmt->execute();
$resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tableau
// Entête
$pdf->setFont('trebuc', '', 11);
$pdf->setFillColor(242, 242, 242); // #f2f2f2
$pdf->Cell(10, 8, 'N°', 1, 0, 'C', true);
$pdf->Cell(40, 8, strtoupper('Nom et prenoms'), 1, 0, 'C', true);
$pdf->Cell(25, 8, strtoupper('Qualite'), 1, 0, 'C', true);
$pdf->Cell(25, 8, strtoupper('Montant'), 1, 0, 'C', true);
$pdf->Cell(25, 8, strtoupper('Banque'), 1, 0, 'C', true);
$pdf->Cell(60, 8, strtoupper('Rib'), 1, 0, 'C', true);
$pdf->Ln();


for ($i=0; $i<count($resultats); $i++) {
    $ligne = $resultats[$i];
    $montant = 0;
    // Une ligne

    $pdf->setFont('trebuc', '', 11);
    $pdf->setFillColor(255, 255, 255); // #f2f2f2
    // N°
    $pdf->Cell(10, 8, $i+1, 1, 0, 'C');
    // Nom et prénoms
    $pdf->Cell(40, 8, $ligne['nom'].' '.$ligne['prenoms'], 1);
    // Qualité
    $pdf->Cell(25, 8, $ligne['qualite'], 1, 0, 'C');
    // Montant
    $pdf->Cell(25, 8, $montant, 1, 0, 'C');
    // Banque
    $pdf->Cell(25, 8, $banque, 1, 0, 'C');
    // Rib
    $pdf->Cell(60, 8, $ligne['rib'], 1, 0, 'C');
}

// //Sortie du pdf
$pdf->Output('ordre de virement.pdf', 'I');
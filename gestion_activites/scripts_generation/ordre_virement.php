<?php
// Inclusions
session_start();
require_once(__DIR__ . '/../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

$banque = $_GET['banque'] ?? 'BOA';
$id_activite = $_GET['id'] ?? 50;

// Récupérons les participants associés à l'activité qui ont comme banque UBA

$stmt = "
SELECT 
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

$titre_activite = $resultats[0]['titre_activite'];

// Header

class Ordre_Virement extends TCPDF
{
    public function Header()
    {
        global $banque, $titre_activite;
        // Pour le formattage de la date en français

        $formatter = new IntlDateFormatter(
            'fr_FR',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            'Africa/Lagos',
            IntlDateFormatter::GREGORIAN
        );

        // Charger la police du pdf
        $this->setFont('trebuc', '', 10);
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
        $ligne2 = mb_strtoupper("ordre de virement $banque", 'UTF-8');
        $this->setFont('trebucbd', '', '11');
        $this->setX($x_droite);
        $this->Cell(0, 5, $ligne2, 0, 1, 'C');
        $this->Ln(5);

        // Ligne 3
        $ligne3 = mb_strtoupper("des indemnites et frais d'entretien accordes aux membres de la commission chargee de $titre_activite", 'UTF-8');
        $this->setFont('trebuc', '', '10');
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
configuration_pdf($pdf, $_SESSION['nom'].' '.$_SESSION['prenoms'], 'Ordre de virement');
$pdf->setMargins(15, 20, 15);
$pdf->setAutoPageBreak(true, 25); // marge bas = 25 pour footer
$pdf->AddPage();

$pdf->Ln(40);

$largeurPage = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
$tailles_colonnes = [0.05, 0.2, 0.15, 0.15, 0.15, 0.3];

foreach ($tailles_colonnes as $index => $taille) {
    $tailles_colonnes[$index] = $taille * $largeurPage;
}

// Tableau
// Entête
$pdf->setFont('trebuc', '', 10);
$pdf->setFillColor(242, 242, 242); // #f2f2f2
$pdf->Cell($tailles_colonnes[0], 8, 'N°', 1, 0, 'C', true); // 5%
$pdf->Cell($tailles_colonnes[1], 8, strtoupper('Nom et prenoms'), 1, 0, 'C', true);
$pdf->Cell($tailles_colonnes[2], 8, strtoupper('Qualite'), 1, 0, 'C', true);
$pdf->Cell($tailles_colonnes[3], 8, strtoupper('Montant'), 1, 0, 'C', true);
$pdf->Cell($tailles_colonnes[4], 8, strtoupper('Banque'), 1, 0, 'C', true);
$pdf->Cell($tailles_colonnes[5], 8, strtoupper('Rib'), 1, 0, 'C', true);
$pdf->Ln();

$total = 0;

for ($i = 0; $i < count($resultats); $i++) {
    $ligne = $resultats[$i];

    $montant = 0;
    if ($ligne['type_activite'] == 1) {
        $montant = $ligne['taux_journalier'] * $ligne['nombre_jours'];
    } elseif ($ligne['type_activite'] == 2) {
        $montant = $ligne['taux_journalier'] * $ligne['nombre_jours'] + $ligne['indemnite_forfaitaire'];
    } elseif ($ligne['type_activite'] == 3) {
        $montant = $ligne['taux_taches'] * $ligne['nombre_taches'] + $ligne['fdj'] * $ligne['nombre_jours'] + $ligne['indemnite_forfaitaire'];
    }

    // Une ligne

    $pdf->setFont('trebuc', '', 10);
    $pdf->setFillColor(255, 255, 255); // #fff
    // N°
    $pdf->Cell($tailles_colonnes[0], 8, $i + 1, 1, 0, 'C');
    // Nom et prénoms
    $pdf->Cell($tailles_colonnes[1], 8, $ligne['nom'] . ' ' . $ligne['prenoms'], 1);
    // Qualité
    $pdf->Cell($tailles_colonnes[2], 8, $ligne['qualite'], 1, 0, 'C');
    // Montant
    $pdf->Cell($tailles_colonnes[3], 8, number_format($montant), 1, 0, 'C');
    // Banque
    $pdf->Cell($tailles_colonnes[4], 8, $banque, 1, 0, 'C');
    // Rib
    $pdf->Cell($tailles_colonnes[5], 8, $ligne['rib'], 1, 0, 'C');
    $pdf->Ln();

    $total += $montant;
}

// Dernière ligne du tableau pour le total

$pdf->setFont('trebucbd', '', 10);
$pdf->setFillColor(242, 242, 242); // #f2f2f2
// Total ( )
$pdf->Cell($tailles_colonnes[0] + $tailles_colonnes[1] + $tailles_colonnes[2], 8, strtoupper('Total ( )'), 1, 0, 'C', true);
// Montant
$pdf->Cell($tailles_colonnes[3], 8, number_format($total), 1, 0, 'C', true);
// Banque
$pdf->Cell($tailles_colonnes[4], 8, '', 1, 0, 'C', true);
// Rib
$pdf->Cell($tailles_colonnes[5], 8, '', 1, 0, 'C', true);
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
$pdf->Output('ordre de virement ' . $banque . '.pdf', 'I');

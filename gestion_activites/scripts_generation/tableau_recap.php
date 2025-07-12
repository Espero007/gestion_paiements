<?php

// Inclusions
session_start();
require_once(__DIR__ . '/../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

// Validations 

if(!valider_id('get', 'id', $bdd, 'participations_activites')){
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}

// Il me faut la liste des banques et les montants associés à chacune de ces banques

$id_activite = $_GET['id'];
$liste_banques = listeBanques($id_activite);

foreach ($liste_banques as $banque) {
    $totaux_banques[$banque] = totalBanque($id_activite, $banque);
}

$stmt = $bdd->query('SELECT nom FROM activites WHERE id='.$id_activite);
$titre_activite = $stmt->fetch(PDO::FETCH_NUM);
$titre_activite = $titre_activite[0];
$stmt->closeCursor();

// Maintenant je peux construire le tableau mais je le mettrai sur la verticale à cause de l'espace qui peut venir à manquer
// Configuration du document
$pdf = new TCPDF('P', 'mm', 'A4');
$pdf->AddFont('trebucbd', '', 'trebucbd.php');
$pdf->setPrintHeader(false); // Retrait de la ligne du haut qui s'affiche par défaut sur une page
configuration_pdf($pdf, $_SESSION['nom'] . ' ' . $_SESSION['prenoms'], 'Tableau récapitulatif');
$pdf->setMargins(15, 20, 15);
$pdf->setAutoPageBreak(true, 25); // marge bas = 25 pour footer
$pdf->AddPage();

// Titre de la page

$pdf->setFont('trebucbd', '', 16);
$pdf->Cell(0, 10, mb_strtoupper('Tableau récapitulatif', 'UTF-8'), 0, 1, 'C');
$pdf->Ln(8);

// Tableau

// Ecriture de l'entête

$largeurPage = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
$tailles_colonnes = [0.3];

foreach ($tailles_colonnes as $index => $taille) {
    $tailles_colonnes[$index] = $taille * $largeurPage;
}

// $pdf->setFont('trebucbd', '', 10);
// $pdf->setFillColor(242, 242, 242);
// $hauteur = 10;
// $pdf->Cell($tailles_colonnes[0], $hauteur, strtoupper('element'), 1, 0, 'C', true);

// $pdf->Ln();
// // Les colonnes

// $pdf->setFont('trebucbd', '', 10);
// $titre = mb_strtoupper('indemnités et frais d\'entretien aux membres de la commission chargée de '.$titre_activite, 'UTF-8');
// $pdf->Cell($tailles_colonnes[0], $hauteur, $titre, 1, 0, 'C');

// On va essayer une sortie avec HTML
$pdf->setFont('trebuc', '', 10);

$html = '
    <style>
        th{
            background-color : #f2f2f2;
        }
    </style>

    <table border="1" cellpadding="5" width="100%">
        <thead>
            <tr>
                <th width="30%">Nom complet</th>
                <th width="40%">Adresse</th>
                <th width="30%">Observation</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Jean Kouadio</td>
                <td>123 Rue des Manguiers, Quartier Zongo, Cotonou</td>
                <td>Aucun</td>
            </tr>
            <tr>
                <td>Sophie Hounkpatin</td>
                <td>Lot 7, Quartier Yénawa<br>Abomey-Calavi</td>
                <td style="color:red;">À revoir pour inscription</td>
            </tr>
            <tr>
                <td>Benoît Alidou</td>
                <td>BP 1095 Porto-Novo</td>
                <td>Absence justifiée</td>
            </tr>
        </tbody>
    </table>
';

// Affichage du tableau
$pdf->writeHTML($html, true, false, true, false, '');

// //Sortie du pdf
$pdf->Output('Tableau récapitulatif.pdf', 'I');
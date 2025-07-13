<?php

// Inclusions
session_start();
require_once(__DIR__ . '/../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

// Validations 

if (!valider_id('get', 'id', $bdd, 'participations_activites')) {
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}

// Il me faut la liste des banques et les montants associés à chacune de ces banques

$id_activite = $_GET['id'];
$liste_banques = listeBanques($id_activite);

foreach ($liste_banques as $banque) {
    $totaux_banques[$banque] = totalBanque($id_activite, $banque);
}

$stmt = $bdd->query('SELECT nom FROM activites WHERE id=' . $id_activite);
$titre_activite = $stmt->fetch(PDO::FETCH_NUM);
$titre_activite = $titre_activite[0];
$stmt->closeCursor();

// Maintenant je peux construire le tableau mais je le mettrai sur la verticale à cause de l'espace qui peut venir à manquer
// Configuration du document
$pdf = new TCPDF('P', 'mm', 'A4');
$pdf->AddFont('trebucbd', '', 'trebucbd.php');
$pdf->setPrintHeader(false); // Retrait de la ligne du haut qui s'affiche par défaut sur une page
configuration_pdf($pdf, $_SESSION['nom'] . ' ' . $_SESSION['prenoms'], 'Tableau récapitulatif');
$pdf->setMargins(25, 25, 15, true);
$pdf->setAutoPageBreak(true, 25); // marge bas = 25 pour footer
$pdf->AddPage();

// Titre de la page

$pdf->setFont('trebucbd', '', 16);
$pdf->Cell(0, 10, mb_strtoupper('Tableau récapitulatif', 'UTF-8'), 0, 1, 'C');
$pdf->Ln(8);

// Tableau

// Ecriture de l'entête

$liste_banques = ['BOA', 'CorisBenin', 'Atlantique Bénin', 'CCP', 'NSIA', 'ORABANK', 'SGB', 'UBA'];
$totaux_banques = [0, 0, 0, 0, 0, 0, 0, 0];

$largeurPage = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
$tailles_colonnes = [40, 60];
// $compteur = 1;
// foreach ($liste_banques as $banque) {
//     $compteur++;
//     $tailles_colonnes[] = $compteur < 4 ? (100 - $tailles_colonnes[0])/4 : (100/); // chaque banque aura cette largeur en pourcentage
// }

// $nbr_lignes = count($liste_banques) > 4 ? 0 : 1;

// On va essayer une sortie avec HTML
// Constitution du bloc HTML à output

$pdf->setFont('trebucbd', '', 10);

$html = '
<style>
.header{
background-color : #f2f2f2;
text-transform : capitalize;
}
td{
text-align : center;
}
</style>
<table border="1" cellpadding="5" width="100%">
<tbody>
<tr>
<td class="header">'.mb_strtoupper('Titre de l\'activité', 'UTF-8').'</td>
<td>'. mb_strtoupper($titre_activite, 'UTF-8').'</td>
</tr>';
for ($i=0; $i < count($liste_banques); $i++) { 
    $html .='
<tr>
<td class="header">'. mb_strtoupper($liste_banques[$i], 'UTF-8').'</td>
<td>'. mb_strtoupper($totaux_banques[$i], 'UTF-8'). '</td>
</tr>    
';
}
$html .='
</tbody>
</table>
';

// $html = '
// <style>
// th{
// background-color : #f2f2f2;
// text-align : center;
// }
// td{
// text-align : center;
// }
// </style>

// <table border="1" cellpadding="5" width="100%">
// <thead>
// <tr>
// <th width="' . $tailles_colonnes[0] . '%">Titre de l\'activité</th>
// </tr>
// ';
// for ($i = 1; $i <= count($liste_banques); $i++) {
//     $banque = $liste_banques[$i - 1];
//     $html .= '
// <th width="' . $tailles_colonnes[$i] . '%">' . $banque . '</th>
// ';
//     if ($i == count($liste_banques)) {
//         $html .= '
// </tr>
// </thead>';
//     }
// }

// $html .= '
// <tbody>
// <tr>
// <td width="'.$tailles_colonnes[0].'%">'.$titre_activite.'</td>';

// for ($i = 1; $i <= count($liste_banques); $i++) {
//     $total = $totaux_banques[$i - 1];
//     $html .= '
// <td width="' . $tailles_colonnes[$i] . '%">' . $total . '</td>
// ';
//     if ($i == count($liste_banques)) {
//         $html .= '
// </tr>';
//     }
// }

// $html .= '
// </tbody>
// </table>';

// Affichage du tableau
$pdf->writeHTML($html, true, false, true, false, '');

// //Sortie du pdf
$pdf->Output('Tableau récapitulatif.pdf', 'I');

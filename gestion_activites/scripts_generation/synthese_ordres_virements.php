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
    $totaux_banques[] = totalBanque($id_activite, $banque);
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
$pdf->setMargins(15, 25, 15, true);
$pdf->setAutoPageBreak(true, 25); // marge bas = 25 pour footer
$pdf->AddPage();

// Titre de la page

$pdf->setFont('trebucbd', '', 16);
$pdf->Cell(0, 10, mb_strtoupper('Tableau récapitulatif', 'UTF-8'), 0, 1, 'C');
$pdf->Ln(8);

// Tableau

// Ecriture de l'entête

$pdf->setFont('trebucbd', '', 10);

// $liste_banques = ['BOA', 'CorisBenin', 'Atlantique Bénin', 'CCP', 'NSIA', 'ORABANK', 'SGB', 'UBA', 'CorisBenin1', 'Atlantique Bénin1', 'CCP1', 'NSIA1', 'ORABANK1', 'SGB1', 'UBA1'];
// $liste_banques = ['BOA', 'CorisBenin', 'Atlantique Bénin'];
// $totaux_banques = [0.1, 0.2, 0.3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 15];

$largeurPage = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
$tailles_colonnes = [40, 20];
$nbr_banques_par_ligne = 3;

$nbr_banques = count($liste_banques);
$nbr_lignes = $nbr_banques <= $nbr_banques_par_ligne ? 1 : intdiv($nbr_banques, $nbr_banques_par_ligne) -1;

// echo $nbr_lignes;

$style = '
<style>
th{
background-color : #f2f2f2;
text-align : center;
}
td{
text-align : center;
}
</style>
';

$compteur = 0;
$compteur_2 = 0;

for ($i = 0; $i < $nbr_lignes; $i++) {
    // Chaque ligne
    $pourcentage = $i == 0 ? 100 - $tailles_colonnes[0] : 100;
    $nouvelle_ligne = false; // pour le montant total

    $html = $style . '
<table border="1" cellpadding="5" width="100%">
<thead>
<tr>';
    // Header
    if ($i == 0) {
        $html .= '
<th width="' . $tailles_colonnes[0] . '%">ELEMENT</th>';
        $max_j = 3;
    } else {
        $max_j = $nbr_banques > 5 ? 5 : $nbr_banques;
    }

    for ($j = 0; $j < $max_j; $j++) {
        $html  .= '
<th width="' . $tailles_colonnes[1] . '%">' . mb_strtoupper($liste_banques[$compteur], 'UTF-8') . '</th>';
        $compteur++;
        $nbr_banques--;
        $pourcentage -= $tailles_colonnes[1];
    }

    if ($i == $nbr_lignes - 1 && $pourcentage != 0) {
        // Dernière ligne et il reste encore de la place
        $html .= '
    <th width="' . $pourcentage . '%">MONTANT TOTAL</th>
            ';
    } elseif ($i == $nbr_lignes - 1 && $pourcentage == 0) {
        $nouvelle_ligne = true;
    }

    $html .= '
</tr>
</thead>
<tbody>
<tr>';
    // Body
    if ($i == 0) {
        $html .= '
<td width="' . $tailles_colonnes[0] . '%">' . mb_strtoupper($titre_activite, 'UTF-8') . '</td>';
    }

    for ($j = 0; $j < $max_j; $j++) {
        $html  .= '
<td width="' . $tailles_colonnes[1] . '%">' . number_format($totaux_banques[$compteur_2], 0, ',', '.') . '</td>';
        $compteur_2++;
    }

    if ($i == $nbr_lignes - 1 && $pourcentage !=0) {
        // Dernière ligne et il reste encore de la place
        $html .= '
    <td width="' . $pourcentage . '%">'.number_format(array_sum($totaux_banques), 0, ',', '.').' FCFA</td>
            ';
    }
    $html .= '
</tr>
</tbody>
</table>
    ';
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Ln();

    if ($nouvelle_ligne) {
        $html =
            $style .
            '
    <table border="1" cellpadding="5" width="100%">
    <thead>
    <tr>
    <th>MONTANT TOTAL</th>
    </tr>
    </thead>
    <tbody>
    <tr>
    <td>'. number_format(array_sum($totaux_banques), 0, ',', '.').' FCFA</td>
    </tr>
    </tbody>
    </table>
    ';
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Ln();
    }
}

// //Sortie du pdf
$pdf->Output('Tableau récapitulatif.pdf', 'I');

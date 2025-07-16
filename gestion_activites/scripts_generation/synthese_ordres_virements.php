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

// $liste_banques = ['BOA', 'CorisBenin', 'Atlantique Bénin', 'CCP', 'NSIA', 'ORABANK', 'SGB', 'UBA', 'CorisBenin1', 'Atlantique Bénin 1', 'CCP1', 'NSIA1', 'ORABANK1', 'SGB1', 'UBA1'];
// $totaux_banques = [20000, 40000, 30000, 2000, 1000, 1000, 1000, 10000, 5000, 30000, 40000, 20000, 30000, 10000, 5000];

// $largeurPage = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
$tailles_colonnes = [40, 20];

$nbr_banques = count($liste_banques);
$var_inter = $nbr_banques;

// Détermination du nombre de lignes du tableau

if ($var_inter <= 3) {
    // Il y a moins de 3 banques donc une seule ligne suffit
    $nbr_lignes = 1;
} elseif (3 < $var_inter && $var_inter <= 8) {
    // On est entre 3 et 8 banques, autres en dehors des trois banques affichées sur la première ligne, il y a encore entre 1 et 5 banques à afficher. Une seconde ligne suffira pour cela
    $nbr_lignes = 2;
} elseif ($var_inter > 8) {
    // On a plus de 8 banques donc on est déjà sur une troisième ligne. De là on retranche d'abord le nombre de banques qui seront affichées sur les lignes 1 et 2 à savoir 8, on détermine le reste et on boucle sur la valeur obtenue. Donc tant que ce reste sera supérieur à 5, on ajoutera une ligne au tableau.
    $nbr_lignes = 3;
    $var_inter -= 8;
    // Supposons qu'en dehors des 8 banques il reste encore 12 banques à afficher
    // A la première itération, le nombre de lignes passera à 4, nous permettant d'afficher 5 banques parmi les 12. Il en restera 7
    // A la seconde itération (puisque le nombre de banques est toujours supérieur à 5), le nombre de lignes passera à 5 permettant d'afficher aussi 5 banques. Il en restera 2 à afficher. On sortira de la boucle while avec un 5 lignes en tout pour le tableau, un nombre suffisant dans le cas d'espèce.

    // Supposons par contre qu'il y ait 17 banques plutôt
    // Première itération : $nbr_lignes -> 4, $var_inter -> 12
    // Seconde itération : $nbr_lignes -> 5, $var_inter -> 7
    // Troisième itération : $nbr_lignes -> 6, $var_inter -> 2
    // Je présume que c'est bon. En tout cas jusque là le nombre de lignes obtenu est valide

    while ($var_inter > 5) {
        $nbr_lignes++;
        $var_inter -= 5;
    }
}

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
        $max_j = $nbr_banques < 3 ? $nbr_banques : 3;
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

    if ($i == $nbr_lignes - 1 && $pourcentage != 0) {
        // Dernière ligne et il reste encore de la place
        $html .= '
    <td width="' . $pourcentage . '%">' . number_format(array_sum($totaux_banques), 0, ',', '.') . ' FCFA</td>
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
    <td>' . number_format(array_sum($totaux_banques), 0, ',', '.') . ' FCFA</td>
    </tr>
    </tbody>
    </table>
    ';
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Ln();
    }
}

//Sortie du pdf
$pdf->Output('Tableau récapitulatif.pdf', 'I');

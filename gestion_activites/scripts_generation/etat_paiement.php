
<?php
/*
session_start();
require_once(__DIR__ . '/../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

function convertir_en_lettres($nombre)
{
    $fmt = new NumberFormatter("fr", NumberFormatter::SPELLOUT);
    return ucfirst($fmt->format($nombre));
}

if (!valider_id('get', 'id', '', 'participations_activites')) {
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}

$id_activite = $_GET['id'];
$stmt = $bdd->query('SELECT type_activite FROM activites a WHERE a.id=' . $id_activite);
$resultat = $stmt->fetch(PDO::FETCH_NUM);
$id_type_activite = $resultat[0];
//echo $id_type_activite;
$stmt->closeCursor();

if ($id_type_activite === 1) {
    // Requête SQL
    $sql = "
        SELECT 
            p.nom AS nom_participant,
            p.prenoms,
            t.nom AS titre_participant,
            a.taux_journalier,
            pa.nombre_jours,
            a.nom AS nom_activite,
            (a.taux_journalier * pa.nombre_jours) AS montant,
            ib.banque,
            ib.numero_compte AS rib,
            a.premier_responsable,
            a.titre_responsable,
            a.financier,
            a.titre_financier
        FROM participants p
        JOIN participations pa ON p.id_participant = pa.id_participant
        JOIN activites a ON pa.id_activite = a.id
        LEFT JOIN titres t ON pa.id_titre = t.id_titre
        LEFT JOIN informations_bancaires ib ON p.id_participant = ib.id_participant
        WHERE a.type_activite = :type_activite
        ";

    $stmt = $bdd->prepare($sql);
    $stmt->execute(['type_activite' => $id_type_activite]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $nom_activite = isset($data[0]['nom_activite']) ? htmlspecialchars($data[0]['nom_activite']) : '';

    // Création du PDF
    $pdf = new TCPDF('P', 'mm', 'A4');
    $pdf->AddFont('trebucbd', '', 'trebucbd.php');
    $pdf->setPrintHeader(false); // Retrait de la ligne du haut qui s'affiche par défaut sur une page
    $pdf->setMargins(15, 25, 15, true);
    configuration_pdf($pdf, $_SESSION['nom'] . ' ' . $_SESSION['prenoms'], 'Etat de paiement');
    $pdf->setAutoPageBreak(true, 25); // marge bas = 25 pour footer
    $pdf->AddPage();
    $pdf->SetFont('trebuc', '', 10);

    $compteurFile = __DIR__ . '/compteur.txt';

    if (!file_exists($compteurFile)) {
        file_put_contents($compteurFile, "1"); // initialiser si absent
    }

    $numero = (int)file_get_contents($compteurFile);
    $numeroEtat = str_pad($numero, 4, '0', STR_PAD_LEFT); // 0001, 0002, ...

    // Titre
    $formatter = new IntlDateFormatter("fr_FR", IntlDateFormatter::LONG, IntlDateFormatter::NONE, "Europe/Paris", IntlDateFormatter::GREGORIAN);
    $dateFr = $formatter->format(new DateTime());

    $information_supplementaire = ['type' => $nom_activite];
    genererHeader($pdf, 'etat_paiement_1', $information_supplementaire, $id_activite);

    $pdf->Ln(10);
    $html = '<p align="center"><b>REF NS N°0569/MES/DC/SGM/DEC/SAFM/SIS/SEMC/SA DU 04 DECEMBRE 2023 PORTANT CONSTITUTION DES COMMISSIONS CHARGEES DE ' . mb_strtoupper($nom_activite, 'UTF-8') . '</b></p><br>';

    // Initialisations
    $rowsPerPage = 25;
    $pageTotal = 0;
    $cumulativeTotal = 0;
    $i = 0;

    // Début du tableau
    function startTable()
    {
        return '
            <style>
                td { font-weight: normal; }
                th { font-weight: bold; }
            </style>
            <table border="1" cellpadding="4" align="center">
                <thead>
                    <tr style="background-color: #f2f2f2; font-size:8px;">
                        <th width="6%">N°</th>
                        <th width="20%">NOM ET PRENOMS</th>
                        <th width="15%">QUALITE</th>
                        <th width="8%">TAUX/ JOUR</th>
                        <th width="6%">NBRE JOUR</th>
                        <th width="12%">MONTANT</th>
                        <th width="10%">BANQUE</th>
                        <th width="23%">RIB</th>
                    </tr>
                </thead>
                <tbody>';
    }

    $pdf->SetFont('trebuc', '', 8);
    $html .= startTable();
    $pdf->SetFont('trebucbd', '', 8);

    if (empty($data)) {
        // Fermer le tableau proprement même s'il est vide
        $html .= '<tr>
                <td colspan="8" style="text-align:center;">Aucune donnée disponible</td>
            </tr>';
        $html .= '</tbody></table>';
    } else {
        foreach ($data as $index => $row) {
            $i++;
            $pageTotal += $row['montant'];

            $html .= '
            <tr>
                <td width="6%"><span style="font-family: trebuc;">' . $i . '</span></td>
                <td width="20%"><span style="font-family: trebuc;">' . htmlspecialchars($row['nom_participant'] . ' ' . $row['prenoms']) . '</span></td>
                <td width="15%"><span style="font-family: trebuc;">' . htmlspecialchars($row['titre_participant']) . '</span></td>
                <td width="8%"><span style="font-family: trebuc;">' . number_format($row['taux_journalier'], 0, ',', '.') . '</span></td>
                <td width="6%"><span style="font-family: trebuc;">' . (int)$row['nombre_jours'] . '</span></td>
                <td width="12%"><span style="font-family: trebuc;">' . number_format($row['montant'], 0, ',', '.') . '</span></td>
                <td width="10%"><span style="font-family: trebuc;">' . htmlspecialchars($row['banque']) . '</span></td>
                <td width="23%"><span style="font-family: trebuc;">' . htmlspecialchars($row['rib']) . '</span></td>
            </tr>';

            $isLastLine = ($index + 1 === count($data));
            $isPageFull = (($index + 1) % $rowsPerPage === 0);

            if ($isPageFull || $isLastLine) {
                $html .= '
                <tr style="background-color: #f2f2f2;>
                    <td colspan="5" width="55%"><strong>Total de cette page</strong></td>
                    <td width="12%"><strong>' . number_format($pageTotal, 0, ',', '.') . '</strong></td>
                    <td colspan="2" width="33%"></td>
                </tr>';

                $cumulativeTotal += $pageTotal;
                $pageTotal = 0;

                $html .= '</tbody></table>';

                if (!$isLastLine) {
                    $pdf->writeHTML($html, true, false, true, false, '');
                    $pdf->AddPage();
                    $html = '<p><strong>Cumul précédent :</strong> ' . number_format($cumulativeTotal, 0, ',', '.') . ' FCFA</p>';
                    $pdf->SetFont('trebuc', '', 8);
                    $html .= startTable();
                    $pdf->SetFont('trebucbd', '', 8);
                }
            }
        }
    }

    // Total général
    $total = $cumulativeTotal + $pageTotal;
    $html .= '<br><br>
        <table border="1" cellpadding="4" align="center">
            <tr style="background-color: #f2f2f2;>
                <td colspan="5" width="55%"><strong>Total général</strong></td>
                <td width="12%"><strong>' . number_format($total, 0, ',', '.') . '</strong></td>
                <td colspan="2" width="33%"></td>
            </tr>
        </table>';

    // Total en lettres
    $fmt = new NumberFormatter('fr', NumberFormatter::SPELLOUT);
    $totalEnLettres = ucfirst($fmt->format($total));

    $pdf->SetFont('trebuc', '', 10);
    $html .= '<br><p align="center"><b><span style="font-weight: bold;">Arrêté le présent état de paiement à la somme de : ' . mb_strtoupper($totalEnLettres, 'UTF-8') . ' (' . number_format($total, 0, ',', '.') . ') Francs CFA</span></b></p>';
    $pdf->SetFont('trebucbd', '', 10);

    // Signatures
    $pr_nom = htmlspecialchars($data[0]['premier_responsable'] ?? '');
    $pr_titre = htmlspecialchars($data[0]['titre_responsable'] ?? '');
    $fin_nom = htmlspecialchars($data[0]['financier'] ?? '');
    $fin_titre = htmlspecialchars($data[0]['titre_financier'] ?? '');

    $html .= '
        <br><br><br>
        <table border="0" align="center">
            <tr>
                <td style="border:none; text-align:center;">
                    <h4 style="margin-bottom:1em">' . htmlspecialchars($fin_titre) . '</h4>
                    <br>
                    <h4 style="text-decoration:underline;">' . htmlspecialchars($fin_nom) . '</h4>
                </td>
                <td style="border:none; text-align:center;">
                    <h4 style="margin-bottom:1em">' . htmlspecialchars($pr_titre) . '</h4>
                    <br>
                    <h4 style="text-decoration:underline;">' . htmlspecialchars($pr_nom) . '</h4>
                </td>
            </tr>
        </table>';

    $pdf->writeHTML($html, true, false, true, false, '');

    ob_end_clean(); // Nettoyer le tampon de sortie
    $pdf->Output('Etat de paiement.pdf', 'I');
}

elseif ($id_type_activite === 2) {

// Requête
$sql = "
    SELECT 
        p.nom AS nom_participant,
        p.prenoms,
        t.nom AS titre_participant,
        t.indemnite_forfaitaire,
        a.nom AS nom_activite,
        a.taux_journalier,
        pa.nombre_jours,
        (a.taux_journalier * pa.nombre_jours + IFNULL(t.indemnite_forfaitaire, 0)) AS montant,
        ib.banque,
        ib.numero_compte AS rib,
        a.premier_responsable,
        a.titre_responsable,
        a.financier,
        a.titre_financier,
        a.centre,
        a.reference
    FROM participants p
    JOIN participations pa ON p.id_participant = pa.id_participant
    JOIN activites a ON pa.id_activite = a.id
    LEFT JOIN titres t ON pa.id_titre = t.id_titre
    LEFT JOIN informations_bancaires ib ON p.id_participant = ib.id_participant
    WHERE a.type_activite = :type_activite AND a.id = :id_activite
";

$stmt = $bdd->prepare($sql);
$stmt->execute([
    'type_activite' => $id_type_activite,
    'id_activite' => $id_activite
]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialisation PDF
$pdf = new TCPDF('P', 'mm', 'A4');
$pdf->AddFont('trebucbd', '', 'trebucbd.php');
$pdf->setPrintHeader(false);
$pdf->setMargins(15, 25, 15, true);
configuration_pdf($pdf, $_SESSION['nom'] . ' ' . $_SESSION['prenoms'], 'Etat de paiement');
$pdf->setAutoPageBreak(true, 25);
$pdf->AddPage();

// Header
$informations_necessaires = ['titre' => $data[0]['nom_activite']];
genererHeader($pdf, 'etat_paiement_2', $informations_necessaires, $id_activite);

$pdf->Ln(10);

$entete_editee = false;
$stmt = $bdd->query('SELECT * FROM informations_entete WHERE id_activite=' . $id_activite);
if ($stmt->rowCount() != 0) {
    $informations_entete = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $informations_entete = $informations_entete[0];
    $entete_editee = true;
}

$pdf->MultiCell(0, 0, mb_strtoupper('NS N°' . $data[0]['reference'] . ' DU ' . (!$entete_editee ? '24 août 2023' : $informations_entete['date2']) . ' portant Constitution des commissions chargées de superviser le déroulement des épreuves écrites de ' . $data[0]['nom_activite']), 0, '', false, 1);

$pdf->setFont('trebuc', '', 10);

// Fonction en-tête tableau
function generateTableHeader()
{
    return '
    <style>
        td { font-weight: normal; }
        th { font-weight: bold; }
    </style>
    <br><br><br>
    <table border="1" cellpadding="4" align="center">
        <thead>
            <tr style="background-color:#f2f2f2; font-size:8px;">
                <th width="5%">N°</th>
                <th width="18%">NOM ET PRENOMS</th>
                <th width="11%">QUALITE</th>
                <th width="7%">TAUX/JOUR</th>
                <th width="8%">NOMBRE DE JOURS</th>
                <th width="12%">INDEMNITE FORFAITAIRE</th>
                <th width="11%">MONTANT</th>
                <th width="10%">BANQUE</th>
                <th width="18%">RIB</th>
            </tr>
        </thead>
        <tbody>
    ';
}

//$pdf->SetFont('trebucbd', '', 8);

// Affichage tableau
$ligne_par_page = 25;
$total_general = 0;
$total_partiel = 0;
$numero = 0;
$cumul_precedent = 0;

$pdf->SetFont('trebuc', '', 8);
$tableHtml = generateTableHeader(); // commence le tableau
$pdf->SetFont('trebucbd', '', 8);


if (empty($data)) {
    // Fermer le tableau proprement même s'il est vide
    $tableHtml .= '<tr>
        <td colspan="9" style="text-align:center;">Aucune donnée disponible</td>
    </tr>';
    $tableHtml .= '</tbody></table>';
    $pdf->writeHTML($tableHtml, true, false, true, false, '');
} else {
    foreach ($data as $index => $row) {
        $numero++;
        $indemnite = $row['indemnite_forfaitaire'] ?? 0;
        $montant = $row['montant'];
        $total_general += $montant;
        $total_partiel += $montant;

        $tableHtml .= '<tr>
            <td width="5%"><span style="font-family: trebuc;">' . $numero . '</span></td>
            <td width="18%"><span style="font-family: trebuc;">' . htmlspecialchars($row['nom_participant'] . ' ' . $row['prenoms']) . '</span></td>
            <td width="11%"><span style="font-family: trebuc;">' . htmlspecialchars($row['titre_participant']) . '</span></td>
            <td width="7%"><span style="font-family: trebuc;">' . number_format($row['taux_journalier'], 0, ',', '.') . '</span></td>
            <td width="8%"><span style="font-family: trebuc;">' . (int)$row['nombre_jours'] . '</span></td>
            <td width="12%"><span style="font-family: trebuc;">' . number_format($indemnite, 0, ',', '.') . '</span></td>
            <td width="11%"><span style="font-family: trebuc;">' . number_format($montant, 0, ',', '.') . '</span></td>
            <td width="10%"><span style="font-family: trebuc;">' . htmlspecialchars($row['banque']) . '</span></td>
            <td width="18%"><span style="font-family: trebuc;">' . htmlspecialchars($row['rib']) . '</span></td>
        </tr>';

        // Si page pleine ou dernière ligne
        $fin_page = ($numero % $ligne_par_page === 0) || ($index === count($data) - 1);

        if ($fin_page) {
            $tableHtml .= '<tr style="background-color: #f2f2f2;">
                <td colspan="6" width="61%"><b>Total partiel</b></td>
                <td width="11%"><b>' . number_format($total_partiel, 0, ',', '.') . '</b></td>
                <td width="28%"></td>
            </tr></tbody></table>';

            $pdf->writeHTML($tableHtml, true, false, true, false, '');

            if ($index !== count($data) - 1) {
                $cumul_precedent += $total_partiel;
                $total_partiel = 0;
                $pdf->AddPage();

                $pdf->writeHTML('<p style="text-align:right; font-size:10px;"><b>Total cumulé précédent : ' . number_format($cumul_precedent, 0, ',', '.') . '</b></p>', true, false, true, false, '');
                $tableHtml = generateTableHeader();
            }
        }
    }
}

// Total général
$fin_nom = $data[0]['financier'] ?? '';
$fin_titre = $data[0]['titre_financier'] ?? '';
$pr_nom = $data[0]['premier_responsable'] ?? '';
$pr_titre = $data[0]['titre_responsable'] ?? '';

$total_en_lettres = convertir_en_lettres($total_general);
$total_formate = number_format($total_general, 0, ',', '.');

$footerHtml = '<br><br>
<table border="1" cellpadding="4" align="center">
    <tr style="background-color: #f2f2f2;">
        <td colspan="6" width="61%"><b>Total général</b></td>
        <td width="11%"><b>' . number_format($total_general, 0, ',', '.') . '</b></td>
        <td width="28%"></td>
    </tr>
</table>';

// Montant en lettres
$footerHtml .= '<br><p align="center"><span style="font-weight: bold; font-size:10px;">Arrêté le présent état de paiement à la somme de : ' . mb_strtoupper($total_en_lettres, 'UTF-8') . ' (' . $total_formate . ') Francs CFA</span></p>';

// Signatures
$footerHtml .= '
<br><br><br>
<table border="0" align="center">
    <tr>
        <td style="border:none; text-align:center; font-size:10px;">
            <h4 >' . htmlspecialchars($data[0]['titre_financier']) . '</h4>
            <br>
            <br>
            <h4 style="text-decoration:underline;">' . htmlspecialchars($data[0]['financier']) . '</h4>
        </td>
        <td style="border:none; text-align:center; font-size:10px;">
            <h4 >' . htmlspecialchars($data[0]['titre_responsable']) . '</h4>
            <br>
            <br>
            <h4 style="text-decoration:underline;">' . htmlspecialchars($data[0]['premier_responsable']) . '</h4>
        </td>
    </tr>
</table>';

$pdf->writeHTML($footerHtml, true, false, true, false, '');
ob_end_clean();
$pdf->Output('Etat de paiement.pdf', 'I');

} elseif ($id_type_activite === 3) {

    //echo "Bonjour 1";

    // Requête SQL
    $sql = "
    SELECT 
        p.nom AS nom_participant,
        p.prenoms,
        t.nom AS titre_participant,
        t.indemnite_forfaitaire,
        a.nom AS nom_activite,
        a.taux_taches,
        pa.nombre_taches,
        a.frais_deplacement_journalier,
        pa.nombre_jours,
        (a.taux_taches * pa.nombre_taches + IFNULL(t.indemnite_forfaitaire, 0) + a.frais_deplacement_journalier * pa.nombre_jours) AS montant,
        ib.banque,
        ib.numero_compte AS rib,
        a.premier_responsable,
        a.titre_responsable,
        a.financier,
        a.titre_financier,
        a.centre,
        a.date_debut,
        a.date_fin
    FROM participants p
    JOIN participations pa ON p.id_participant = pa.id_participant
    JOIN activites a ON pa.id_activite = a.id
    LEFT JOIN titres t ON pa.id_titre = t.id_titre
    LEFT JOIN informations_bancaires ib ON p.id_participant = ib.id_participant
    WHERE a.type_activite = :type_activite AND a.id = :id_activite";

    $stmt = $bdd->prepare($sql);
    $stmt->execute([
        'type_activite' => $id_type_activite,
        'id_activite' => $id_activite
    ]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $titre_activite = isset($data[0]['nom_activite']) ? htmlspecialchars($data[0]['nom_activite']) : '';
    $centre = isset($data[0]['centre']) ? htmlspecialchars($data[0]['centre']) : '';
    $debut = isset($data[0]['date_debut']) ? htmlspecialchars($data[0]['date_debut']) : '';
    $fin = isset($data[0]['date_fin']) ? htmlspecialchars($data[0]['date_fin']) : '';

    // Création du PDF
    $pdf = new TCPDF('L', 'mm', 'A4');
    $pdf->AddFont('trebucbd', '', 'trebucbd.php');
    $pdf->setPrintHeader(false); // Retrait de la ligne du haut qui s'affiche par défaut sur une page
    $pdf->setMargins(15, 25, 15, true);
    configuration_pdf($pdf, $_SESSION['nom'] . ' ' . $_SESSION['prenoms'], 'Etat de paiement');
    $pdf->setAutoPageBreak(true, 25); // marge bas = 25 pour footer
    $pdf->AddPage();
    $pdf->SetFont('trebuc', '', 10);


    $compteurFile = __DIR__ . '/compteur.txt';

    if (!file_exists($compteurFile)) {
        file_put_contents($compteurFile, "1"); // initialiser si absent
    }

    $numero = (int)file_get_contents($compteurFile);
    $numeroEtat = str_pad($numero, 4, '0', STR_PAD_LEFT); // 0001, 0002, ...



    $formatter = new IntlDateFormatter("fr_FR", IntlDateFormatter::LONG, IntlDateFormatter::NONE, "Europe/Paris", IntlDateFormatter::GREGORIAN);
    $dateFr = $formatter->format(new DateTime());

    // J'ai déplacé la fonction qui était ici dans le fichier constantes_uilitaires


    // Nécessaire pour les mois en français
    setlocale(LC_TIME, 'fr_FR.UTF-8');

    // Génération du header

    $informations_necessaires = ['titre' => $data[0]['nom_activite']];
    genererHeader($pdf, 'etat_paiement_3', $informations_necessaires, $id_activite);

    /*
    $html = '
    <style>
        
        h1 { text-align: center; font-size: 16pt; }
        h2 { text-align: center; font-size: 14pt; }
        table { border-collapse: collapse; width: 100%; }
        td, th { border: 1px solid #000; padding: 5px; }
        tr {font-size:8px; }
        p {font-size:11px; }
        
    </style>
    <table border="0">
        <tr>
            <td style="width: 50%; font-size: 10pt; text-align: center; border: none;">
                <p><b>REPUBLIQUE DU BENIN</b><br></p>
                <p> <b>MINISTÈRE ...</b><br></p>
                <p><b>DIRECTION ... </b><br></p>
                <p><b>SERVICE ... </b><br></p>
            </td>
            <td style="width: 50%; font-size: 10pt; text-align: center; border: none;">
                <p>Cotonou, le ' . $dateFr . '</p>
                <h2>ETAT DE PAIEMENT N°' . $numeroEtat . '</h2>
                <h4>INDEMNITES  ET FRAIS D\'ENTRETIEN  ACCORDES  AUX  MEMBRES DE LA COMMISSION CHARGEE DE ' . mb_strtoupper($titre_activite, 'UTF-8') . '</h4>
            </td>
        </tr>
        <tr>
        <td style="border:none;"></td>
        <td style="text-align:left; border:none;">
        <p> <b><u>PERIODE:</u> DU ' . formaterPeriode($debut, $fin) . ' </b></p>
        <p> <b><u>CENTRE:</u> ' . mb_strtoupper($centre) . '</b> </p>
        </td>
    </tr>
    </table>';
    */

/*$information_supplementaire = ['type' => $titre_activite] ;
    genererHeader($pdf,'etat_paiement',$information_supplementaire );*/


/* Commentaire à enlever apès test


    $pdf->Ln(10);
    $html = '
    <p><b> NS  N° 2548/PR/DC/SGM/DAF/DEC/SAF/SIS/SEC/SD/SA DU 31 DECEMBRE 2020 </b><br></p>';

    // Titre


    // Initialisations
    $rowsPerPage = 6;
    $pageTotal = 0;
    $cumulativeTotal = 0;

    $i = 0;
    //font-size:7px;
    // Début du tableau
    function startTable()
    {
        return '<style>
        td { font-weight: normal; }
        th { font-weight: bold; }
    </style>
    <table border="1" cellpadding="5" align="center">
        <thead>
            <tr style="background-color:#f2f2f2;">
                <th width="5%">N°</th>
                <th width="13%">NOM ET PRENOM</th>
                <th width="9%">TITRE</th>
                <th width="6%">TAUX/ TÂCHE</th>
                <th width="7%">NOMBRE DE TÂCHE</th>
                <th width="9%">FRAIS ENTRETIENS PAR JOURS</th>
                <th width="7%">NOMBRE DE JOURS</th>
                <th width="13%">INDEMNITE FORFAITAIRE</th>
                <th width="8%">MONTANT</th>
                <th width="8%">BANQUE</th>
                <th width="15%">RIB</th>
            </tr>
        </thead>
        <tbody>';
    }

    //$pdf->SetFont('trebuc', '', 8);
    $html .= startTable();

    if (empty($data)) {
        // Fermer le tableau proprement même s'il est vide
        $html .= '<tr>
            <td colspan="12" style="text-align:center;">Aucune donnée disponible</td>
        </tr>';
        $html .= '</tbody></table>';
    } else {

        foreach ($data as $index => $row) {
            $i++;
            $pageTotal += $row['montant'];
            $indemnite = $row['indemnite_forfaitaire'] ?? 0;
            $montant = $row['montant'];

           $html .= '<tr>
    <td width="5%"><span style="font-family: trebuc;">' . $i . '</span></td>
    <td width="13%"><span style="font-family: trebuc;">' . htmlspecialchars($row['nom_participant']) . ' ' . htmlspecialchars($row['prenoms']) . '</span></td>
    <td width="9%"><span style="font-family: trebuc;">' . htmlspecialchars($row['titre_participant']) . '</span></td>
    <td width="6%"><span style="font-family: trebuc;">' . number_format($row['taux_taches'], 0, ',', '.') . '</span></td>
    <td width="7%"><span style="font-family: trebuc;">' . (int)$row['nombre_taches'] . '</span></td>
    <td width="9%"><span style="font-family: trebuc;">' . number_format($row['frais_deplacement_journalier'], 0, ',', '.') . '</span></td>
    <td width="7%"><span style="font-family: trebuc;">' . (int)$row['nombre_jours'] . '</span></td>
    <td width="13%"><span style="font-family: trebuc;">' . number_format($indemnite, 0, ',', '.') . '</span></td>
    <td width="8%"><span style="font-family: trebuc;">' . number_format($montant, 0, ',', '.') . '</span></td>
    <td width="8%"><span style="font-family: trebuc;">' . htmlspecialchars($row['banque']) . '</span></td>
    <td width="15%"><span style="font-family: trebuc;">' . htmlspecialchars($row['rib']) . '</span></td>
</tr>';

            $isLastLine = ($index + 1 === count($data));
            $isPageFull = (($index + 1) % $rowsPerPage === 0);

            if ($isPageFull || $isLastLine) {
                // Total de la page
                $html .= '<tr>
                <td colspan="8" width="69%" ><strong>Total de cette page</strong></td>
                <td width="8%"><strong>' . number_format($pageTotal, 0, ',', '.') . '</strong></td>
                <td colspan="2" width="23%"></td>
            </tr>';

                $cumulativeTotal += $pageTotal;
                $pageTotal = 0;

                $html .= '</tbody></table>';

                // Dernière page : on continue sans sauter de page
                if (!$isLastLine) {
                    $pdf->writeHTML($html, true, false, true, false, '');
                    $pdf->AddPage();

                    // Afficher le cumul précédent
                    $html = '<p><strong>Cumul précédent :</strong> ' . number_format($cumulativeTotal, 0, ',', '.') . ' FCFA</p>';
                    $html .= startTable();
                }
            }
        }
    }

    // Total général (sous le dernier tableau, sans saut de page ni <br><br>)
    $total = $cumulativeTotal;

    $html .= '<br><br><table border="1" cellpadding="5"  align="center">
    <tr>
        <td colspan="8" width="69%"><strong>Total</strong></td>
        <td width="8%"><strong>' . number_format($total, 0, ',', '.') . '</strong></td>
        <td colspan="2" width="23%"></td>
    </tr>
    </table>';

    // Total en lettres
    $fmt = new NumberFormatter('fr', NumberFormatter::SPELLOUT);
    $totalEnLettres = ucfirst($fmt->format($total));

$html .= '<p align="center"><span style="font-weight: bold;">Arrêté le présent état de paiement à la somme de : ' . mb_strtoupper($totalEnLettres, 'UTF-8') . ' (' . number_format($total, 0, ',', '.') . ') Francs CFA</span></p>';
    // Signatures
    $pr_nom = $data[0]['premier_responsable'] ?? '';
    $pr_titre = $data[0]['titre_responsable'] ?? '';
    $fin_nom = $data[0]['financier'] ?? '';
    $fin_titre = $data[0]['titre_financier'] ?? '';

    $html .= ' 
    <br><br><table border="0"  align="center">
        <tr>
            <td style=" border:none;">
                <h4 >' . htmlspecialchars($fin_titre) . '</h4>
                <br><br><br> <br><br><br> 
                
                <h4 style="text-decoration:underline">' . htmlspecialchars($fin_nom) . '</h4>
            </td>
            <td style=" border:none;">
                <h4 >' . htmlspecialchars($pr_titre) . '</h4>
                <br><br><br> <br><br><br> 
                <h4 style="text-decoration:underline">'  . htmlspecialchars($pr_nom) . '</h4>
            </td>
        </tr>
    </table>';

    // Écriture finale
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('Etat de paiement.pdf', 'I');
} */




// Test de grok pour le système multipage


session_start();
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

if (!valider_id('get', 'id', '', 'participations_activites')) {
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}

$id_activite = dechiffrer($_GET['id']);

genererEtatPaiement($id_activite);

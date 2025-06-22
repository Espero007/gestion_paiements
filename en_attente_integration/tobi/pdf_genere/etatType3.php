<?php
require_once(__DIR__.'/../../../tcpdf/tcpdf.php');
require_once(__DIR__ .'/../../../includes/bdd.php');

// ID du type d'activité à filtrer (modifier selon ton cas)
$id_type_activite = 2;
$id_activite = 2;

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
$stmt->execute(['type_activite' => $id_type_activite,
    'id_activite' => $id_activite]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$titre_activite = isset($data[0]['nom_activite'])? htmlspecialchars($data[0]['nom_activite']): '';
$centre = isset($data[0]['centre'])? htmlspecialchars($data[0]['centre']): '';
$debut = isset($data[0]['date_debut'])? htmlspecialchars($data[0]['date_debut']): '';
$fin = isset($data[0]['date_fin'])? htmlspecialchars($data[0]['date_fin']): '';

// Création du PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 10);

$compteurFile = __DIR__ . '/compteur.txt';

if (!file_exists($compteurFile)) {
    file_put_contents($compteurFile, "1"); // initialiser si absent
}

$numero = (int)file_get_contents($compteurFile);
$numeroEtat = str_pad($numero, 4, '0', STR_PAD_LEFT); // 0001, 0002, ...



$formatter = new IntlDateFormatter("fr_FR", IntlDateFormatter::LONG, IntlDateFormatter::NONE,"Europe/Paris",IntlDateFormatter::GREGORIAN);
$dateFr = $formatter->format(new DateTime());

function formaterPeriode($dateDebut, $dateFin) {
    $debut = new DateTime($dateDebut);
    $fin   = new DateTime($dateFin);

    $jourDebut = $debut->format('j');
    $jourFin   = $fin->format('j');

    $moisDebut = strftime('%B', $debut->getTimestamp());
    $moisFin   = strftime('%B', $fin->getTimestamp());

    $anneeDebut = $debut->format('Y');
    $anneeFin   = $fin->format('Y');

    // Période dans le même mois et année
    if ($moisDebut === $moisFin && $anneeDebut === $anneeFin) {
        return "$jourDebut au $jourFin $moisFin $anneeFin";
    }
    // Même année mais mois différents
    elseif ($anneeDebut === $anneeFin) {
        return "$jourDebut $moisDebut au $jourFin $moisFin $anneeFin";
    }
    // Mois et années différents
    else {
        return "$jourDebut $moisDebut $anneeDebut au $jourFin $moisFin $anneeFin";
    }
}

// Nécessaire pour les mois en français
setlocale(LC_TIME, 'fr_FR.UTF-8');

$html = '
<style>
    h1 { text-align: center; font-size: 16pt; }
    h2 { text-align: center; font-size: 14pt; }
    table { border-collapse: collapse; width: 100%; }
    td, th { border: 1px solid #000; padding: 5px; }
    
</style>
<table border="0">
    <tr>
        <td style="width: 50%; font-size: 10pt; text-align: center; border: none;">
            <p><b>REPUBLIQUE DU BENIN</b><br>**********</p>
            <p> <b>MINISTÈRE ...</b><br>********** </p>
            <p><b>DIRECTION ... </b><br>**********</p>
            <p><b>SERVICE ... </b><br>**********</p>
        </td>
        <td style="width: 50%; font-size: 10pt; text-align: center; border: none;">
            <p>Cotonou, le ' . $dateFr . '</p>
            <h2>ETAT DE PAIEMENT N°'. $numeroEtat .'</h2>
            <h4>INDEMNITES  ET FRAIS D\'ENTRETIEN  ACCORDES  AUX  MEMBRES DE LA COMMISSION CHARGEE DE ' . mb_strtoupper($titre_activite, 'UTF-8') . '</h4>
        </td>
    </tr>
    <tr>
    <td style="border:none;"></td>
    <td style="text-align:left; border:none;">
    <p> <b><span style="text-decoration:underline">  PERIODE: </span> DU '.formaterPeriode($debut,$fin) .' </b></p>
    <p> <b><span style="text-decoration:underline"> CENTRE: </span> '. mb_strtoupper($centre) .'</b> </p>
    </td>
</tr>
</table>';

$html .= '
<p><b> NS  N° 2548/PR/DC/SGM/DAF/DEC/SAF/SIS/SEC/SD/SA DU 31 DECEMBRE 2020 </b><br> <br></p>';


// Titre


// Initialisations
$rowsPerPage = 25;
$pageTotal = 0;
$cumulativeTotal = 0;

$i = 0;

// Début du tableau
function startTable() {
    return '<table border="1" cellpadding="4" align="center">
        <thead>
            <tr style="background-color:#f0f0f0; font-size:7px; ">
            <th width="6%">N°</th>
            <th width="13%">Nom & Prénoms</th>
            <th width="10%">Titre</th>
            <th width="6%">Taux/Tâche</th>
            <th width="6%">Nombre de Tâches</th>
            <th width="8%">Frais Entretiens par jours</th>
            <th width="7%">Nombre de Jours</th>
            <th width="7%">Indemnité forfaitaire</th>
            <th width="9%">Montant</th>
            <th width="9%">Banque</th>
            <th width="17%">RIB</th>
            </tr>
        </thead>
        <tbody>';
}

$html .= startTable();

foreach ($data as $index => $row) {
    $i++;
    $pageTotal += $row['montant'];
    $indemnite = $row['indemnite_forfaitaire'] ?? 0;
    $montant = $row['montant'];

    $html .= '<tr>
        <td width="6%">' . $i . '</td>
        <td width="13%">' . htmlspecialchars($row['nom_participant']) . ' '.htmlspecialchars($row['prenoms']) .'</td>
        <td width="10%">' . htmlspecialchars($row['titre_participant']) . '</td>
        <td width="6%">' . number_format($row['taux_taches'], 2, ',', ' ') . '</td>
        <td width="6%">' . (int)$row['nombre_taches'] . '</td>
        <td width="8%">' . number_format($row['frais_deplacement_journalier'], 2, ',', ' ') . '</td>
        <td width="7%">' . (int)$row['nombre_jours'] . '</td>
        <td width="7%">' . number_format($indemnite, 2, ',', ' ') . '</td>
        <td width="9%">' . number_format($montant, 2, ',', ' ') . '</td>
        <td width="9%">' . htmlspecialchars($row['banque']) . '</td>
        <td width="17%">' . htmlspecialchars($row['rib']) . '</td>
    </tr>';

    $isLastLine = ($index + 1 === count($data));
    $isPageFull = (($index + 1) % $rowsPerPage === 0);

    if ($isPageFull || $isLastLine) {
        // Total de la page
        $html .= '<tr>
            <td colspan="8" ><strong>Total de cette page</strong></td>
            <td><strong>' . number_format($pageTotal, 2, ',', ' ') . '</strong></td>
            <td colspan="2"></td>
        </tr>';

        $cumulativeTotal += $pageTotal;
        $pageTotal = 0;

        $html .= '</tbody></table>';

        // Dernière page : on continue sans sauter de page
        if (!$isLastLine) {
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->AddPage();

            // Afficher le cumul précédent
            $html = '<p><strong>Cumul précédent :</strong> ' . number_format($cumulativeTotal, 2, ',', ' ') . ' FCFA</p>';
            $html .= startTable();
        }
    }
}

// Total général (sous le dernier tableau, sans saut de page ni <br><br>)
$total = $cumulativeTotal;

$html .= '<br><br><table border="1" cellpadding="4"  align="center">
<tr>
    <td colspan="6" width="63%"><strong>Total général</strong></td>
    <td width="9%"><strong>' . number_format($total, 2, ',', ' ') . '</strong></td>
    <td colspan="2" width="26%"></td>
</tr>
</table>';

// Total en lettres
$fmt = new NumberFormatter('fr', NumberFormatter::SPELLOUT);
$totalEnLettres = ucfirst($fmt->format($total));

$html .= '<p><strong>Arrêté le présent état de paiement à la somme de :</strong> ' . $totalEnLettres .' (' . number_format($total, 0, ',', ' ') . ') FCFA</p>';

// Signatures
$pr_nom = $data[0]['premier_responsable'] ?? '';
$pr_titre = $data[0]['titre_responsable'] ?? '';
$fin_nom = $data[0]['financier'] ?? '';
$fin_titre = $data[0]['titre_financier'] ?? '';

$html .= ' 
<br><br><table border="0"  align="center">
     <tr>
        <td style=" border:none;">
           <h4 style="margin-bottom:3em"> ' . htmlspecialchars($fin_titre) .' </h4>  
           <h4 style="text-decoration:underline">' . htmlspecialchars($fin_nom) . '</h4>
        </td>
        <td style=" border:none;">
            <h4 style="margin-bottom:3em">' . htmlspecialchars($pr_titre) . '</h4>
            <h4 style="text-decoration:underline">'  . htmlspecialchars($pr_nom) . '</h4>
        </td>
    </tr>
</table>';

// Écriture finale
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output(__DIR__.'/Etat_de_correction.pdf', 'I');
?>





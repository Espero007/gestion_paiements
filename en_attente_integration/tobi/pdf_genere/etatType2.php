<?php
ob_start();
require_once(__DIR__.'/../../../tcpdf/tcpdf.php');
require_once(__DIR__ .'/../../../includes/bdd.php');

function convertir_en_lettres($nombre) {
    $fmt = new NumberFormatter("fr", NumberFormatter::SPELLOUT);
    return ucfirst($fmt->format($nombre));
}

// Récupération des paramètres
$id_type_activite = isset($_GET['type_activite']) ? (int)$_GET['type_activite'] : 2;
$id_activite = isset($_GET['id']) ? (int)$_GET['id'] : 2;

// Requête SQL
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
    a.centre
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

if (!$data) {
    die("Aucune donnée trouvée.");
}

// Initialisation TCPDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 10);

$titre_activite = isset($data[0]['nom_activite'])? htmlspecialchars($data[0]['nom_activite']): '';
$centre = isset($data[0]['centre'])? htmlspecialchars($data[0]['centre']): '';

$compteurFile = __DIR__ . '/compteur.txt';

if (!file_exists($compteurFile)) {
    file_put_contents($compteurFile, "1"); // initialiser si absent
}

$numero = (int)file_get_contents($compteurFile);
$numeroEtat = str_pad($numero, 4, '0', STR_PAD_LEFT); // 0001, 0002, ...



$formatter = new IntlDateFormatter("fr_FR", IntlDateFormatter::LONG, IntlDateFormatter::NONE,"Europe/Paris",IntlDateFormatter::GREGORIAN);
$dateFr = $formatter->format(new DateTime());
$html = '
<style>
h1 { text-align: center; font-size: 16pt; }
h2 { text-align: center; font-size: 14pt; }
table { border-collapse: collapse; width: 100%; }
td, th { border: 1px solid #000; padding: 5px; }
</style>
 
<table border="0">
<tr>
<td style="width: 50%; font-size: 10pt; text-align:center;border: none; ">
    <p><b>REPUBLIQUE DU BENIN </b> <br/>********** </p>
    <p> <b>MINISTÈRE ... </b><br/> **********</p>
    <p> <b>DIRECTION ...</b> <br/> **********</p>
    <p> <b> SERVICE ...</b><br/> **********</p>
</td>
<td style="width: 50%; font-size: 10pt; text-align:center; border: none;">
    <p>Cotonou, le'. $dateFr.'. </p>
    <h2>ETAT DE PAIEMENT N°'. $numeroEtat .'</h2>
    <h4>DES INDEMNITES ET FRAIS D\'ENTRETIEN ACCORDES AUX  MEMBRES D\'ENCADREMENT DANS LE CADRE DE '.mb_strtoupper($titre_activite) .'</h4>
</td>
</tr>
<tr>
    <td style="border:none;">
    </td>
    <td style="text-align:left; border:none;">
    <p> <b> <span style="text-decoration:underline"> JOURNEE: </span> '.$dateFr .' </b></p>
    <p> <b> <span style="text-decoration:underline"> CENTRE: </span> '. mb_strtoupper($centre) .' </b></p>
    </td>
</tr>
</table> ';

$html .= '
<p><b> NS  N°0416/GHIC/DC/SGM/DPAF/DEC/SAFM/SIS/SEMC/SA DU 24 AOÛT 2023 portant Constitution des commissions chargées de superviser le déroulement de '. htmlspecialchars($titre_activite).' </b><br> <br></p>';

$ligne_par_page = 25; // nombre de lignes par page
$total_general = 0;
$total_partiel = 0;
$cumul_precedent = 0;
$numero = 0;

function generateTableHeader() {
    return '
    
    <table border="1" cellpadding="4" align="center"  >
        <thead>
            <tr style="background-color:#f0f0f0; ; font-size:8px;">
                <th width="7%">N°</th>
                <th width="19%">Nom et Prénoms</th>
                <th width="12%">Qualité</th>
                <th width="6%">Taux/Jour</th>
                <th width="6%">Nbre_Jours</th>
                <th width="8%">Indemnité forfaitaire</th>
                <th width="12%">Montant</th>
                <th width="10%">Banque</th>
                <th width="20%">RIB</th>
            </tr>
        </thead>
        <tbody>
    ';
}

// Variables de cumul


$html .= generateTableHeader();

foreach ($data as $index => $row) {
    $numero++;
    $indemnite = $row['indemnite_forfaitaire'] ?? 0;
    $montant = $row['montant'];
    $total_general += $montant;
    $total_partiel += $montant;

    $html .= '<tr>
        <td width="7%">' . $numero . '</td>
        <td width="19%">' . htmlspecialchars($row['nom_participant']) . ' '. htmlspecialchars($row['prenoms']) .'</td>
        <td width="12%">' . htmlspecialchars($row['titre_participant']) . '</td>
        <td width="6%">' . number_format($row['taux_journalier'], 2, ',', ' ') . '</td>
        <td width="6%">' . (int)$row['nombre_jours'] . '</td>
        <td width="8%">' . number_format($indemnite, 2, ',', ' ') . '</td>
        <td width="12%">' . number_format($montant, 2, ',', ' ') . '</td>
        <td width="10%">' . htmlspecialchars($row['banque']) . '</td>
        <td width="20%">' . htmlspecialchars($row['rib']) . '</td>
    </tr>';

    // Fin de page ou fin des données
    if (($numero % $ligne_par_page === 0) || ($index === count($data) - 1)) {
        // Total partiel en bas du tableau
        $html .= '<tr>
            <td colspan="7" > <b>Total partiel </b></td>
            <td> <b>' . number_format($total_partiel, 2, ',', ' ') . '</b></td>
            <td colspan="2"></td>
        </tr>';

        $html .= '</tbody></table>';

        // Écriture du tableau et du total partiel dans le PDF
        $pdf->writeHTML($html, true, false, true, false, '');

        // Mettre à jour cumul précédent
        $cumul_precedent += $total_partiel;

        // Saut de page sauf si dernière page
        if ($index !== count($data) - 1) {
            $pdf->AddPage();
            // Afficher en haut le cumul des totaux des pages précédentes
            $html = '<p style="text-align:right; font-weight:bold;">Total cumulé précédent : ' 
                . number_format($cumul_precedent, 2, ',', ' ') . ' FCFA</p>';
            
            // Recommencer un nouveau tableau sur nouvelle page
            $html = generateTableHeader();

            // Réinitialiser total partiel pour nouvelle page
            $total_partiel = 0;
        }
    }
}

// À la fin, afficher le total général sous le dernier tableau
$html = '<br><br><table border="1" cellpadding="4" align="center" >
    <tr>
        <td colspan="7" > <b>Total général </b></td>
        <td> <b>' . number_format($total_general, 2, ',', ' ') . ' </b></td>
        <td colspan="2"></td>
    </tr>
</table>';

// Ajouter le total général en lettres et les signatures
$html .= '<br><p><strong>Arrêté le présent état de paiement à la somme de : ' 
    . convertir_en_lettres($total_general) . ' (' . number_format($total_general, 0, ',', ' ') . ') FCFA</strong></p>';

$pr_nom = $data[0]['premier_responsable'] ?? '';
$pr_titre = $data[0]['titre_responsable'] ?? '';
$fin_nom = $data[0]['financier'] ?? '';
$fin_titre = $data[0]['titre_financier'] ?? '';

$html .= ' 
<br><br><br><table border="0" align="center">
    <tr>
        <td style="border:none;">
           <h4 style="margin-bottom:3em"> ' . htmlspecialchars($fin_titre) .'</h4>  
           <h4 style="text-decoration:underline"> ' . htmlspecialchars($fin_nom) . '</h4>
        </td>
        <td style=" border:none;">
            <h4 style="margin-bottom:3em">' . htmlspecialchars($pr_titre) . ' </h4>
            <h4 style="text-decoration:underline">'  . htmlspecialchars($pr_nom) . '</h4>
        </td>
    </tr>
    </table>
    ';


// Écriture finale dans le PDF
$pdf->writeHTML($html, true, false, true, false, '');
ob_end_clean();
$pdf->Output(__DIR__.'/Etat_de_paiement.pdf', 'I');
?>


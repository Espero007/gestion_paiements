<?php
ob_start();
require_once(__DIR__.'/../../../tcpdf/tcpdf.php');
require_once(__DIR__ .'/../../../includes/bdd.php');

function convertir_en_lettres($nombre) {
    $fmt = new NumberFormatter("fr", NumberFormatter::SPELLOUT);
    return ucfirst($fmt->format($nombre));
}

// Paramètres
$id_type_activite = isset($_GET['type_activite']) ? (int)$_GET['type_activite'] : 2;
$id_activite = isset($_GET['id']) ? (int)$_GET['id'] : 2;

// Requête
$sql = "SELECT 
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

// Initialisation PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('trebuc', '', 10);

// Infos générales
$titre_activite = htmlspecialchars($data[0]['nom_activite'] ?? '');
$centre = htmlspecialchars($data[0]['centre'] ?? '');

$compteurFile = __DIR__ . '/compteur.txt';
if (!file_exists($compteurFile)) file_put_contents($compteurFile, "1");
$numero = (int)file_get_contents($compteurFile);
$numeroEtat = str_pad($numero, 4, '0', STR_PAD_LEFT);

$formatter = new IntlDateFormatter("fr_FR", IntlDateFormatter::LONG, IntlDateFormatter::NONE,"Europe/Paris",IntlDateFormatter::GREGORIAN);
$dateFr = $formatter->format(new DateTime());

// En-tête HTML
$headerHtml = <<<HTML
<style>
    h1 { text-align: center; font-size: 16pt; }
    h2 { text-align: center; font-size: 14pt; }
    table { border-collapse: collapse; width: 100%; }
    td, th { border: 1px solid #000; padding: 5px; }
    tr {font-size:8px; }
    p {font-size:11px}
</style>
<table>
<tr>
    <td style="width: 50%; text-align:center;border: none;">
        <p><b>REPUBLIQUE DU BENIN <br>*******</b></p>
        <p><b>MINISTÈRE ... <br>*******</b></p>
        <p><b>DIRECTION ... <br>*******</b></p>
        <p><b>SERVICE ... <br>*******</b></p>
    </td>
    <td style="width: 50%; text-align:center; border: none;">
        <p>Cotonou, le $dateFr</p>
        <h2>ETAT DE PAIEMENT N°$numeroEtat</h2>
        <h4>DES INDEMNITÉS ET FRAIS D’ENTRETIEN ACCORDÉS AUX MEMBRES D’ENCADREMENT DANS LE CADRE DE <br><br> <u>{$titre_activite}</u></h4>
    </td>
</tr>
<tr>
    <td style="border:none;"></td>
    <td style="text-align:left; border:none;">
        <p><b><u>JOURNEE</u>: $dateFr</b></p>
        <p><b><u>CENTRE</u>: $centre</b></p>
    </td>
</tr>
</table>
<p><b>NS N°0416/... portant Constitution des commissions chargées de superviser le déroulement de {$titre_activite}</b></p>
<br>
HTML;

$pdf->writeHTML($headerHtml, true, false, true, false, '');



// Fonction en-tête tableau
function generateTableHeader() {
    return '
    <table border="1" cellpadding="4" align="center">
        <thead>
            <tr style="background-color:#f0f0f0; font-size:8px;">
                <th width="5%">N°</th>
                <th width="18%">NOM ET PRENOMS</th>
                <th width="12%">QUALITE</th>
                <th width="6%">TAUX/JOUR</th>
                <th width="7%">NOMBRE DE JOURS</th>
                <th width="11%">INDEMNITE FORFAITAIRE</th>
                <th width="11%">MONTANT</th>
                <th width="10%">BANQUE</th>
                <th width="20%">RIB</th>
            </tr>
        </thead>
        <tbody>
    ';
}

// Affichage tableau


$ligne_par_page = 25;
$total_general = 0;
$total_partiel = 0;
$numero = 0;
$cumul_precedent = 0;

$tableHtml = generateTableHeader(); // commence le tableau

$numero = 0;
$ligne_par_page = 25;
$total_general = 0;
$total_partiel = 0;
$cumul_precedent = 0;

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
        <td width="5%">' . $numero . '</td>
        <td width="18%">' . htmlspecialchars($row['nom_participant'] . ' ' . $row['prenoms']) . '</td>
        <td width="12%">' . htmlspecialchars($row['titre_participant']) . '</td>
        <td  width="6%">' . number_format($row['taux_journalier'], 2, ',', ' ') . '</td>
        <td width="7%">' . (int)$row['nombre_jours'] . '</td>
        <td width="11%">' . number_format($indemnite, 2, ',', ' ') . '</td>
        <td width="11%">' . number_format($montant, 2, ',', ' ') . '</td>
        <td width="10%">' . htmlspecialchars($row['banque']) . '</td>
        <td width="20%">' . htmlspecialchars($row['rib']) . '</td>
    </tr>';

    // Si page pleine ou dernière ligne
    $fin_page = ($numero % $ligne_par_page === 0) || ($index === count($data) - 1);

    if ($fin_page) {
        $tableHtml .= '<tr>
            <td colspan="7" width="59%"><b>Total partiel</b></td>
            <td width="11%"><b>' . number_format($total_partiel, 2, ',', ' ') . '</b></td>
            <td width="30%"></td>
        </tr></tbody></table>';

        $pdf->writeHTML($tableHtml, true, false, true, false, '');

        if ($index !== count($data) - 1) {
            $cumul_precedent += $total_partiel;
            $total_partiel = 0;
            $pdf->AddPage();

            $pdf->writeHTML('<p style="text-align:right;"><b>Total cumulé précédent : ' . number_format($cumul_precedent, 2, ',', ' ') . ' FCFA</b></p>', true, false, true, false, '');
            $tableHtml = generateTableHeader();
        }
    }
}
}
// Pieds de tableau

$fin_nom = $data[0]['financier'] ?? '';
$fin_titre = $data[0]['titre_financier'] ?? '';
$pr_nom = $data[0]['premier_responsable'] ?? '';
$pr_titre = $data[0]['titre_responsable'] ?? '';

$total_en_lettres = convertir_en_lettres($total_general);
$total_formate = number_format($total_general, 0, ',', ' ');

// Total général
$footerHtml = '<br><br>
<table border="1" cellpadding="4" align="center">
    <tr>
        <td colspan="7" width="59%"><b>Total général</b></td>
        <td width="11%"><b>' . number_format($total_general, 2, ',', ' ') . '</b></td>
        <td width="30%"></td>
    </tr>
</table>';

// Montant en lettres
$footerHtml .= '<br><p><strong>Arrêté le présent état de paiement à la somme de : '
    . $total_en_lettres . ' (' . $total_formate . ') FCFA</strong></p>';

// Signatures
$footerHtml .= '
<br><br><br>
<table border="0" align="center">
    <tr>
        <td style="border:none; text-align:center;">
            <h4>' . htmlspecialchars($data[0]['titre_financier']) . '</h4>
            <h4 style="text-decoration:underline;">' . htmlspecialchars($data[0]['financier']) . '</h4>
        </td>
        <td style="border:none; text-align:center;">
            <h4>' . htmlspecialchars($data[0]['titre_responsable']) . '</h4>
            <h4 style="text-decoration:underline;">' . htmlspecialchars($data[0]['premier_responsable']) . '</h4>
        </td>
    </tr>
</table>';

$pdf->writeHTML($footerHtml, true, false, true, false, '');
ob_end_clean();
$pdf->Output(__DIR__.'/Etat_de_paiement.pdf', 'I');
?>
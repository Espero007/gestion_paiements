<?php
//ob_start(); // Démarrer le tampon de sortie
require_once(__DIR__.'/../../../tcpdf/tcpdf.php');
require_once(__DIR__ .'/../../../includes/bdd.php');

// ID du type d'activité à filtrer
$id_type_activite = 1;
$id_activite = 1;

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


if (empty($data)) {
    die('Aucune donnée trouvée pour générer le PDF.');
}

$nom_activite = isset($data[0]['nom_activite']) ? htmlspecialchars($data[0]['nom_activite']) : '';

// Création du PDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetFont('dejavusans', '', 10);

$compteurFile = __DIR__ . '/compteur.txt';

if (!file_exists($compteurFile)) {
    file_put_contents($compteurFile, "1"); // initialiser si absent
}

$numero = (int)file_get_contents($compteurFile);
$numeroEtat = str_pad($numero, 4, '0', STR_PAD_LEFT); // 0001, 0002, ...

// Titre
$formatter = new IntlDateFormatter("fr_FR", IntlDateFormatter::LONG, IntlDateFormatter::NONE, "Europe/Paris", IntlDateFormatter::GREGORIAN);
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
        <td style="width: 50%; font-size: 10pt; text-align: center; border: none;">
            <p><b>REPUBLIQUE DU BENIN</b><br>**********</p>
            <p>MINISTÈRE ...<br>**********</p>
            <p>DIRECTION ...<br>**********</p>
            <p>SERVICE ...<br>**********</p>
        </td>
        <td style="width: 50%; font-size: 10pt; text-align: center; border: none;">
            <p>Cotonou, le ' . $dateFr . '</p>
            <h2>ETAT DE PAIEMENT N°'. $numeroEtat .'</h2>
            <h4>DES INDEMNITES ET FRAIS D\'ENTRETIEN ACCORDES AUX MEMBRES DE LA COMMISSION CHARGEE DE ' . mb_strtoupper($nom_activite, 'UTF-8') . '</h4>
        </td>
    </tr>
</table>';

$html .= '<p><b> REF NS N°0569/MES/DC/SGM/DEC/SAFM/SIS/SEMC/SA DU 04 DECEMBRE 2023 PORTANT CONSTITUTION DES COMMISSIONS CHARGEES DE ' . mb_strtoupper($nom_activite, 'UTF-8') . '</b><br></p>';

// Initialisations
$rowsPerPage = 25;
$pageTotal = 0;
$cumulativeTotal = 0;
$i = 0;

// Début du tableau
function startTable() {
    return '
    <table border="1" cellpadding="4" align="center">
        <thead>
            <tr style="background-color:#f0f0f0; font-size:8px;">
                <th width="7%">N°</th>
                <th width="20%">Nom et Prénoms</th>
                <th width="15%">Qualité</th>
                <th width="6%">Taux/Jour</th>
                <th width="6%">Nbr Jours</th>
                <th width="12%">Montant</th>
                <th width="10%">Banque</th>
                <th width="24%">RIB</th>
            </tr>
        </thead>
        <tbody>';
}

$html .= startTable();

foreach ($data as $index => $row) {
    $i++;
    $pageTotal += $row['montant'];

    $html .= '
    <tr>
        <td width="7%">' . $i . '</td>
        <td width="20%">' . htmlspecialchars($row['nom_participant'] . ' ' . $row['prenoms']) . '</td>
        <td width="15%">' . htmlspecialchars($row['titre_participant']) . '</td>
        <td width="6%">' . number_format($row['taux_journalier'], 2, ',', ' ') . '</td>
        <td width="6%">' . (int)$row['nombre_jours'] . '</td>
        <td width="12%">' . number_format($row['montant'], 2, ',', ' ') . '</td>
        <td width="10%">' . htmlspecialchars($row['banque']) . '</td>
        <td width="24%">' . htmlspecialchars($row['rib']) . '</td>
    </tr>';

    $isLastLine = ($index + 1 === count($data));
    $isPageFull = (($index + 1) % $rowsPerPage === 0);

    if ($isPageFull || $isLastLine) {
        $html .= '
        <tr>
            <td colspan="5" ><strong>Total de cette page</strong></td>
            <td ><strong>' . number_format($pageTotal, 2, ',', ' ') . '</strong></td>
            <td colspan="2"></td>
        </tr>';

        $cumulativeTotal += $pageTotal;
        $pageTotal = 0;

        $html .= '</tbody></table>';

        if (!$isLastLine) {
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->AddPage();
            $html = '<p><strong>Cumul précédent :</strong> ' . number_format($cumulativeTotal, 2, ',', ' ') . ' FCFA</p>';
            $html .= startTable();
        }
    }
}

// Total général
$total = $cumulativeTotal + $pageTotal;
$html .= ' <br> <br>
<table border="1" cellpadding="4" align="center">
    <tr>
        <td colspan="5"  width="54%"><strong>Total général</strong></td>
            <td width="12%"><strong>' . number_format($total, 2, ',', ' ') . '</strong></td>
            <td colspan="2" width="34%"></td>
    </tr>
</table>';

// Total en lettres
$fmt = new NumberFormatter('fr', NumberFormatter::SPELLOUT);
$totalEnLettres = ucfirst($fmt->format($total));

$html .= '<p><strong>Arrêté le présent état de paiement à la somme de :</strong> ' . $totalEnLettres . ' (' . number_format($total, 0, ',', ' ') . ') FCFA</p>';

// Signatures
$pr_nom = htmlspecialchars($data[0]['premier_responsable'] ?? '');
$pr_titre = htmlspecialchars($data[0]['titre_responsable'] ?? '');
$fin_nom = htmlspecialchars($data[0]['financier'] ?? '');
$fin_titre = htmlspecialchars($data[0]['titre_financier'] ?? '');

$html .= '
<table border="0">
    <tr>
        <td style="text-align: center; border: none;">
            <h4>' . $fin_titre . '</h4>
            <h4 style="text-decoration: underline;">' . $fin_nom . '</h4>
        </td>
        <td style="text-align: center; border: none;">
            <h4>' . $pr_titre . '</h4>
            <h4 style="text-decoration: underline;">' . $pr_nom . '</h4>
        </td>
    </tr>
</table>';

$pdf->writeHTML($html, true, false, true, false, '');
//ob_end_clean(); // Nettoyer le tampon de sortie
$pdf->Output(__DIR__ . '/Etat_deliberation.pdf', 'I');
?>
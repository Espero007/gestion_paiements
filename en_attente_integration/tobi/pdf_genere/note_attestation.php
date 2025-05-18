<?php
// Activer le mode debug temporairement (à désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();
// Inclusion de la bibliothèque TCPDF
require_once(__DIR__ . '/../../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../../includes/bdd.php');

// Vérifier que $bdd est un objet PDO
if (!($bdd instanceof PDO)) {
    ob_end_clean();
    die('Erreur : la connexion à la base de données a échoué.');
}

// Requête SQL pour récupérer les informations
$id_type_activite = 1;
$sql = "
    SELECT 
        p.id_participant,
        p.nom,
        p.prenoms,
        t.nom AS titre_participant,
        ib.banque,
        ib.numero_compte,
        a.nom AS nom_activite,
        a.premier_responsable,
        a.titre_responsable,
        a.financier,
        a.titre_financier
    FROM participants p
    LEFT JOIN participations pa ON p.id_participant = pa.id_participant
    LEFT JOIN activites a ON pa.id_activite = a.id
    LEFT JOIN titres t ON pa.id_titre = t.id_titre
    LEFT JOIN informations_bancaires ib ON p.id_participant = ib.id_participant
    WHERE a.type_activite = :type_activite
";
try {
    $stmt = $bdd->prepare($sql);
    $stmt->execute(['type_activite' => $id_type_activite]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    ob_end_clean();
    die('Erreur lors de l’exécution de la requête SQL : ' . $e->getMessage());
}

if (empty($participants)) {
    ob_end_clean();
    die('Aucun participant trouvé pour ce type d’activité.');
}

// Vérifier quel document afficher
$document = isset($_GET['document']) ? $_GET['document'] : '';

$formatter = new IntlDateFormatter("fr_FR", IntlDateFormatter::LONG, IntlDateFormatter::NONE, "Europe/Paris", IntlDateFormatter::GREGORIAN);
$dateFr = $formatter->format(new DateTime());
$nom_activite = isset($participants[0]["nom_activite"]) ? htmlspecialchars($participants[0]["nom_activite"]) : '';

if ($document === 'note') {
    // *** Note de Service PDF ***
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('dejavusans', '', 10);

    $html = '
    <style>
    thead tr { background-color: #eeeeee; }
    h1 { text-align: center; font-size: 16pt; }
    h2 { text-align: center; font-size: 14pt; }
    table { border-collapse: collapse; width: 100%; }
    td, th { border: 1px solid #000; padding: 5px; }
    </style>
    <table style="margin-bottom: 20px;" border="0">
        <tr>
            <td style="width: 50%; font-size: 10pt; text-align:center; border: none;">
                <p><b>REPUBLIQUE DU BENIN<br/>**********</b></p>
                <p><b>MINISTÈRE ...<br/>**********</b></p>
                <p><b>DIRECTION ...<br/>**********</b></p>
                <p><b>SERVICE ...<br/>**********</b></p>
            </td>
            <td style="width: 50%; font-size: 10pt; text-align:center; border: none;">
                <p>Cotonou, le ' . $dateFr . '</p>
                <h2>NOTE DE SERVICE</h2>
                <h4>PORTANT CONSTITUTION DES MEMBRES DE LA COMMISSION CHARGÉE DE ' . mb_strtoupper($nom_activite) . '</h4>
            </td>
        </tr>
    </table>
    <h4><b>N° :</b> /DEG/MAS/SAFM/SDDC/SEL/SEMC/SIS/SD</h4>
    <p><b style="text-decoration:underline;">Réf :</b> NS N° 0012/MAS/DC/SGM/DPAF/DSI/DEC/SAFM/SEMC/SIS/SA DU 29 DECEMBRE 2023</p><br><br>
    <table border="1" cellpadding="4" style="width: 100%;">
        <thead>
            <tr style="background-color: #eeeeee;">
                <th style="width: 7%;">N°</th>
                <th style="width: 15%;">Nom</th>
                <th style="width: 15%;">Prénoms</th>
                <th style="width: 15%;">Titre</th>
                <th style="width: 15%;">Banque</th>
                <th style="width: 33%;">Numéro de Compte</th>
            </tr>
        </thead>
        <tbody>';
    $i = 1;
    foreach ($participants as $p) {
        $html .= '<tr>
                    <td style="width: 7%;">' . $i++ . '</td>
                    <td style="width: 15%;">' . htmlspecialchars($p['nom'] ?? '') . '</td>
                    <td style="width: 15%;">' . htmlspecialchars($p['prenoms'] ?? '') . '</td>
                    <td style="width: 15%;">' . htmlspecialchars($p['titre_participant'] ?? '') . '</td>
                    <td style="width: 15%;">' . htmlspecialchars($p['banque'] ?? '') . '</td>
                    <td style="width: 33%;">' . htmlspecialchars($p['numero_compte'] ?? '') . '</td>
                  </tr>';
    }
    $html .= '</tbody></table>';

    $premier_responsable = isset($participants[0]['premier_responsable']) ? htmlspecialchars($participants[0]['premier_responsable']) : '';
    $titre_responsable = isset($participants[0]['titre_responsable']) ? htmlspecialchars($participants[0]['titre_responsable']) : '';
    $html .= '<br><br>
    <h4 style="text-align:center">' . $titre_responsable . '</h4>
    <h4 style="text-align:center; text-decoration:underline;">' . $premier_responsable . '</h4>';

    $pdf->writeHTML($html, true, false, true, false, '');
    ob_clean();
    ob_end_clean();
    $pdf->Output('Note_de_service.pdf', 'I');
} elseif ($document === 'attestation') {
    // *** Attestation Collective PDF ***
    $pdf1 = new TCPDF();
    $pdf1->AddPage();
    $pdf1->SetFont('dejavusans', '', 10);

    $html = '
    <style>
    h1 { text-align: center; font-size: 16pt; }
    h2 { text-align: center; font-size: 14pt; }
    table { border-collapse: collapse; width: 100%; }
    td, th { border: 1px solid #000; padding: 5px; }
    </style>
    <table style="margin-bottom: 20px;" border="0">
        <tr>
            <td style="width: 50%; font-size: 10pt; text-align:center; border: none;">
                <p><b>REPUBLIQUE DU BENIN<br/>**********</b></p>
                <p><b>MINISTÈRE ...<br/>**********</b></p>
                <p><b>DIRECTION ...<br/>**********</b></p>
                <p><b>SERVICE ...<br/>**********</b></p>
            </td>
            <td style="width: 50%; font-size: 10pt; text-align:center; border: none;">
                <p>Cotonou, le ' . $dateFr . '</p>
                <h2>ATTESTATION COLLECTIVE DE TRAVAIL</h2>
                <h4>DES MEMBRES DE LA COMMISSION CHARGÉE DE ' . mb_strtoupper($nom_activite) . '</h4>
            </td>
        </tr>
    </table><br><br><br><br>
    <table border="1" cellpadding="4" style="width: 100%;">
        <thead>
            <tr style="background-color: #eeeeee;">
                <th style="width: 7%;">N°</th>
                <th style="width: 15%;">Nom</th>
                <th style="width: 15%;">Prénoms</th>
                <th style="width: 15%;">Titre</th>
                <th style="width: 15%;">Banque</th>
                <th style="width: 33%;">Numéro de Compte</th>
            </tr>
        </thead>
        <tbody>';
    $i = 1;
    foreach ($participants as $p) {
        $html .= '<tr>
                    <td style="width: 7%;">' . $i++ . '</td>
                    <td style="width: 15%;">' . htmlspecialchars($p['nom'] ?? '') . '</td>
                    <td style="width: 15%;">' . htmlspecialchars($p['prenoms'] ?? '') . '</td>
                    <td style="width: 15%;">' . htmlspecialchars($p['titre_participant'] ?? '') . '</td>
                    <td style="width: 15%;">' . htmlspecialchars($p['banque'] ?? '') . '</td>
                    <td style="width: 33%;">' . htmlspecialchars($p['numero_compte'] ?? '') . '</td>
                  </tr>';
    }
    $html .= '</tbody></table>';

    $premier_responsable = isset($participants[0]['premier_responsable']) ? htmlspecialchars($participants[0]['premier_responsable']) : '';
    $titre_responsable = isset($participants[0]['titre_responsable']) ? htmlspecialchars($participants[0]['titre_responsable']) : '';
    $financier = isset($participants[0]['financier']) ? htmlspecialchars($participants[0]['financier']) : '';
    $titre_financier = isset($participants[0]['titre_financier']) ? htmlspecialchars($participants[0]['titre_financier']) : '';
    $html .= '<br><br><br><br>
    <table border="0" style="width: 100%;">
        <tr>
            <td style="width: 50%; font-size: 10pt; border: none;">
                <h4 style="margin-bottom:3em">' . $titre_responsable . '</h4>
                <h4 style="text-decoration:underline;">' . $premier_responsable . '</h4>
            </td>
            <td style="width: 50%; font-size: 10pt; border: none;">
                <h4 style="margin-bottom:3em">' . $titre_financier . '</h4>
                <h4 style="text-decoration:underline;">' . $financier . '</h4>
            </td>
        </tr>
    </table>';

    $pdf1->writeHTML($html, true, false, true, false, '');
    ob_clean();
    ob_end_clean();
    $pdf1->Output('Attestation_collective.pdf', 'I');
} else {
    // Afficher une interface pour choisir le document
    ob_end_clean();
    echo '<h2>Choisir un document à afficher :</h2>';
    echo '<p><a href="?document=note">Afficher la Note de service</a></p>';
    echo '<p><a href="?document=attestation">Afficher l’Attestation collective</a></p>';
}
?>
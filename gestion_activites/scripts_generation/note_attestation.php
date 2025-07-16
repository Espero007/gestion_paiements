<?php
// Inclusions
require_once(__DIR__ . '/../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

session_start();
// Activer le mode debug temporairement (à désactiver en production)
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

ob_start();

// Vérifier que $bdd est un objet PDO
// if (!($bdd instanceof PDO)) {
//     ob_end_clean();
//     die('Erreur : la connexion à la base de données a échouée.');
// }

$redirect = true;

if (valider_id('get', 'id', '', 'participations_activites')) {
    // l'id de l'activité est bon
    if (isset($_GET['document'])) {
        if (in_array($_GET['document'], ['attestation', 'note'])) {
            // On a la variable 'document' et elle a une bonne valeur
            $redirect = false;
            $activity_id = $_GET['id'];
            $document = $_GET['document'];
        }
    }
}

if ($redirect) {
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}

if (!valider_id('get', 'id', $bdd, 'participations_activites')) {
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}

// Requête SQL pour récupérer les informations

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
        a.titre_financier,
        a.organisateur,
        a.titre_organisateur,
        a.timbre,
        a.reference
    FROM participations pa
    INNER JOIN participants p ON pa.id_participant = p.id_participant
    INNER JOIN activites a ON pa.id_activite = a.id
    INNER JOIN titres t ON pa.id_titre = t.id_titre
    INNER JOIN informations_bancaires ib ON pa.id_compte_bancaire = ib.id
    WHERE pa.id_activite = :activite_id
";

$stmt = $bdd->prepare($sql);
$stmt->execute(['activite_id' => $activity_id]);
$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
$titre_activite = $participants[0]['nom_activite'];

$stmt = $bdd->query('SELECT * FROM informations_entete WHERE id_activite='.$activity_id);
$informations_entete = $stmt->fetchAll(PDO::FETCH_ASSOC);
$informations_entete = $informations_entete[0];

$formatter = new IntlDateFormatter("fr_FR", IntlDateFormatter::LONG, IntlDateFormatter::NONE, "Europe/Paris", IntlDateFormatter::GREGORIAN);
$dateFr = $formatter->format(new DateTime());
$nom_activite = isset($participants[0]["nom_activite"]) ? htmlspecialchars($participants[0]["nom_activite"]) : '';

$pdf = new TCPDF('P', 'mm', 'A4');
$pdf->AddFont('trebucbd', '', 'trebucbd.php');
$pdf->setPrintHeader(false); // Retrait de la ligne du haut qui s'affiche par défaut sur une page
$pdf->setMargins(15, 25, 15, true);
$pdf->setAutoPageBreak(true, 25); // marge bas = 25 pour footer
$pdf->AddPage();

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


if ($document === 'note') {
    // *** Note de Service PDF ***
    // $pdf = new TCPDF();
    // $pdf->AddPage();
    // $pdf->SetFont('trebuc', '', 10);
    configuration_pdf($pdf, $_SESSION['nom'] . ' ' . $_SESSION['prenoms'], 'Note de service');
    
    $information_supplementaire = ['titre' =>$titre_activite ];
    genererHeader($pdf, 'note_service', $information_supplementaire, $activity_id);
    $pdf->setFont('trebuc', '', 10);

    $html = $style.'
    <br><br><br><br><br>
    <h4><b>N°:</b> '.$participants[0]['timbre'].'</h4>
    <p><b style="text-decoration:underline;">Réf:</b> NS N° '.$participants[0]['reference'].' DU '.$informations_entete['date2'].'</p><br><br>
    <table border="1" cellpadding="5" style="width: 100%; text-align:center">
        <thead>
            <tr>
                <th style="width: 12%;">N°</th>
                <th style="width: 25%;">NOM ET PRENOMS</th>
                <th style="width: 15%;">TITRE</th>
                <th style="width: 15%;">BANQUE</th>
                <th style="width: 33%;">NUMERO DE COMPTE</th>
            </tr>
        </thead>
        <tbody>';
    $i = 1;


    foreach ($participants as $p) {
        $html .= '<tr>
                    <td style="width: 12%;">' . $i++ . '</td>
                    <td style="width: 25%;">' . htmlspecialchars($p['nom'].' '.$p['prenoms'] ?? '') . '</td>
                    <td style="width: 15%;">' . htmlspecialchars($p['titre_participant'] ?? '') . '</td>
                    <td style="width: 15%;">' . htmlspecialchars($p['banque'] ?? '') . '</td>
                    <td style="width: 33%;">' . htmlspecialchars($p['numero_compte'] ?? '') . '</td>
                  </tr>';
    }
    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    ob_clean();
    ob_end_clean();

    // Premier responsable et son titre
    $pdf->setFont('trebucbd', '', 10);
    $pdf->Cell(0, 10, $participants[0]['titre_responsable'], 0, 1, 'C');
    // $pdf->Ln(8);
    $pdf->setFont('trebucbd', 'U', 10);
    $pdf->Cell(0, 10, $participants[0]['premier_responsable'], 0, 1, 'C');

    // $pdf->MultiCell(0, 10, $participants[0]['premier_responsable']."\n".$participants[0]['premier_responsable'], 0, 'C');

    $pdf->Output('Note de service.pdf', 'I');
} elseif ($document === 'attestation') {
    configuration_pdf($pdf, $_SESSION['nom'] . ' ' . $_SESSION['prenoms'], 'Attestation collective');
    $information_supplementaire = ['titre' => $titre_activite ];
    genererHeader($pdf, 'attestation_collective', $information_supplementaire, $activity_id);
    $pdf->setFont('trebuc', '', 10);
    $html = $style. '
    <br><br><br><br><br><br><br><br>
    <table border="1" cellpadding="5" style="width: 100%;text-align:center">
        <thead>
            <tr>
                <th style="width: 12%;">N°</th>
                <th style="width: 25%;">NOM ET PRENOMS</th>
                <th style="width: 15%;">TITRE</th>
                <th style="width: 15%;">BANQUE</th>
                <th style="width: 33%;">NUMERO DE COMPTE</th>
            </tr>
        </thead>
        <tbody>';
    $i = 1;
    foreach ($participants as $p) {
        $html .= '<tr>
                    <td style="width: 12%;">' . $i++ . '</td>
                    <td style="width: 25%;">' . htmlspecialchars($p['nom'].' '.$p['prenoms'] ?? '') . '</td>
                    <td style="width: 15%;">' . htmlspecialchars($p['titre_participant'] ?? '') . '</td>
                    <td style="width: 15%;">' . htmlspecialchars($p['banque'] ?? '') . '</td>
                    <td style="width: 33%;">' . htmlspecialchars($p['numero_compte'] ?? '') . '</td>
                  </tr>';
    }
    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    ob_clean();
    ob_end_clean();

    $premier_responsable = isset($participants[0]['premier_responsable']) ? htmlspecialchars($participants[0]['premier_responsable']) : '';
    $titre_responsable = isset($participants[0]['titre_responsable']) ? htmlspecialchars($participants[0]['titre_responsable']) : '';
    $financier = isset($participants[0]['financier']) ? htmlspecialchars($participants[0]['financier']) : '';
    $titre_financier = isset($participants[0]['titre_financier']) ? htmlspecialchars($participants[0]['titre_financier']) : '';

    // Ajouter les informations du premier responsable et son titre sous le tableau
    $pdf->Ln(10);
    $bloc_gauche = mb_strtoupper($participants[0]['titre_organisateur']);
    $bloc_droite = mb_strtoupper($participants[0]['titre_responsable']);
    afficherTexteDansDeuxBlocs($pdf, $bloc_gauche, $bloc_droite, 'trebucbd', 10, 5, 'C', '', 'C', '');

    $bloc_gauche = mb_strtoupper($participants[0]['organisateur']);
    $bloc_droite = mb_strtoupper($participants[0]['premier_responsable']);
    afficherTexteDansDeuxBlocs($pdf, $bloc_gauche, $bloc_droite, 'trebucbd', 10, 2, 'C', 'U', 'C', 'U');

    $pdf->Output('Attestation collective.pdf', 'I');
}

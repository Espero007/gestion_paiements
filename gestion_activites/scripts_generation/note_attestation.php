<?php
// Inclusions
require_once(__DIR__ . '/../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

session_start();

// Classe personnalisée pour la numérotation des pages
class MYPDF extends TCPDF {
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('trebucbd', '', 8); // Police grasse pour le pied de page
       // $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0);
        $this->Cell(0, 10,  $this->getAliasNumPage() , 0, false, 'C', 0);

    }
}

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
    ORDER BY p.nom ASC, p.prenoms ASC
";

$stmt = $bdd->prepare($sql);
$stmt->execute(['activite_id' => $activity_id]);
$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
$titre_activite = $participants[0]['nom_activite'];

$stmt = $bdd->query('SELECT * FROM informations_entete WHERE id_activite=' . $activity_id);
$informations_entete = $stmt->fetchAll(PDO::FETCH_ASSOC);
$informations_entete = $informations_entete[0];

$formatter = new IntlDateFormatter("fr_FR", IntlDateFormatter::LONG, IntlDateFormatter::NONE, "Europe/Paris", IntlDateFormatter::GREGORIAN);
$dateFr = $formatter->format(new DateTime());
$nom_activite = isset($participants[0]["nom_activite"]) ? htmlspecialchars($participants[0]["nom_activite"]) : '';

$pdf = new MYPDF('P', 'mm', 'A4');
$pdf->AddFont('trebuc', '', 'trebuc.php'); // Police non-grasse
$pdf->AddFont('trebucbd', '', 'trebucbd.php'); // Police grasse
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(true); // Activer le pied de page pour la numérotation
$pdf->setMargins(15, 25, 15, true);
$pdf->setAutoPageBreak(true, 25); // Marge bas = 25 pour footer
$pdf->AddPage();

$style = '
<style>
    th {
        background-color: #f2f2f2;
        text-align: center;
        font-weight: bold;
        font-family: trebucbd;
    }
    td {
        text-align: center;
        line-height: 16px;
        font-weight: normal;
        font-family: trebuc;
    }
</style>';

if ($document === 'note') {
    // *** Note de Service PDF ***
    configuration_pdf($pdf, $_SESSION['nom'] . ' ' . $_SESSION['prenoms'], 'Note de service');
    
    $information_supplementaire = ['titre' => $titre_activite];
    genererHeader($pdf, 'note_service', $information_supplementaire, $activity_id);
    $pdf->setFont('trebucbd', '', 10); // Gras pour les éléments hors tableau

    $html = $style . '
    <br><br><br><br><br>
    <h4><b style="font-family: trebucbd;">N°: ' . htmlspecialchars($participants[0]['timbre']) . '</b></h4>
    <p><b style="font-family: trebucbd; text-decoration:underline;">Réf:</b> NS N° ' . htmlspecialchars($participants[0]['reference']) . ' DU ' . htmlspecialchars($informations_entete['date2']) . '</p><br><br>
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
                    <td style="width: 25%;">' . htmlspecialchars($p['nom'] . ' ' . $p['prenoms'] ?? '') . '</td>
                    <td style="width: 15%;">' . htmlspecialchars($p['titre_participant'] ?? '') . '</td>
                    <td style="width: 15%;">' . htmlspecialchars($p['banque'] ?? '') . '</td>
                    <td style="width: 33%;">' . htmlspecialchars($p['numero_compte'] ?? '') . '</td>
                  </tr>';
    }
    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');
    
    // Premier responsable et son titre
    $pdf->Ln(10);
    $pdf->setFont('trebucbd', '', 10);
    $pdf->Cell(0, 10, htmlspecialchars($participants[0]['titre_responsable'] ?? ''), 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->setFont('trebucbd', 'U', 10);
    $pdf->Cell(0, 10, htmlspecialchars($participants[0]['premier_responsable'] ?? ''), 0, 1, 'C');

    ob_clean();
    ob_end_clean();
    $pdf->Output('Note de service.pdf', 'I');
} elseif ($document === 'attestation') {
    configuration_pdf($pdf, $_SESSION['nom'] . ' ' . $_SESSION['prenoms'], 'Attestation collective');
    $information_supplementaire = ['titre' => $titre_activite];
    genererHeader($pdf, 'attestation_collective', $information_supplementaire, $activity_id);
    $pdf->setFont('trebuc', '', 10);

    $html = $style . '
    <br><br><br><br><br><br><br><br>
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
                    <td style="width: 25%;">' . htmlspecialchars($p['nom'] . ' ' . $p['prenoms'] ?? '') . '</td>
                    <td style="width: 15%;">' . htmlspecialchars($p['titre_participant'] ?? '') . '</td>
                    <td style="width: 15%;">' . htmlspecialchars($p['banque'] ?? '') . '</td>
                    <td style="width: 33%;">' . htmlspecialchars($p['numero_compte'] ?? '') . '</td>
                  </tr>';
    }
    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');

    $premier_responsable = isset($participants[0]['premier_responsable']) ? htmlspecialchars($participants[0]['premier_responsable']) : '';
    $titre_responsable = isset($participants[0]['titre_responsable']) ? htmlspecialchars($participants[0]['titre_responsable']) : '';
    $financier = isset($participants[0]['financier']) ? htmlspecialchars($participants[0]['financier']) : '';
    $titre_financier = isset($participants[0]['titre_financier']) ? htmlspecialchars($participants[0]['titre_financier']) : '';

    // Ajouter les informations du premier responsable et son titre sous le tableau
    $pdf->Ln(10);
    $bloc_gauche = mb_strtoupper($participants[0]['titre_organisateur'] ?? '');
    //$pdf->Ln(10);
    $bloc_droite = mb_strtoupper($participants[0]['titre_responsable'] ?? '');
    //$pdf->Ln(10);
    afficherTexteDansDeuxBlocs($pdf, $bloc_gauche, $bloc_droite, 'trebucbd', 10, 5, 'C', '', 'C', '');

    $pdf->Ln(10);
    $bloc_gauche = mb_strtoupper($participants[0]['organisateur'] ?? '');
    $bloc_droite = mb_strtoupper($participants[0]['premier_responsable'] ?? '');
    afficherTexteDansDeuxBlocs($pdf, $bloc_gauche, $bloc_droite, 'trebucbd', 10, 2, 'C', 'U', 'C', 'U');

    ob_clean();
    ob_end_clean();
    $pdf->Output('Attestation collective.pdf', 'I');
}
?>
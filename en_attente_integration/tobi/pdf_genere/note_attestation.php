<?php
session_start();
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
    die('Erreur : la connexion à la base de données a échouée.');
}


//$activity_id = 13;


$errors = [];
$id_user = $_SESSION['user_id'];

//if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {


// Vérifier si l'ID de l'activité est fourni
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {    
    header('Location:' . $_SESSION["previous_url"]);
    //echo 'Bonjour 1';
    exit;
}
$activity_id = $_GET['id'];


// Vérifier si l'activité existe et appartient à l'utilisateur
try {
    $sql = 'SELECT id_note_generatrice, type_activite FROM activites WHERE id = :id AND id_user = :id_user';
    $stmt = $bdd->prepare($sql);
    $stmt->execute(['id' => $activity_id, 'id_user' => $id_user]);
    $activity = $stmt->fetch(PDO::FETCH_ASSOC);


    if (!$activity) {
        $_SESSION['form_errors'] = ['database' => "Activité non trouvée ou vous n'avez pas les permissions pour la modifier."];
        //echo 'Bonjour 2';
        header('Location:' . $_SESSION["previous_url"]);
        exit;
    }

} catch (PDOException $e) {
    $_SESSION['form_errors'] = ['database' => "Erreur lors de la vérification de l'activité. Veuillez réessayer."];
    header('Location:' . $_SESSION["previous_url"]);
    exit;
    //echo 'Bonjour 3';
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
        a.titre_financier
    FROM participants p
    LEFT JOIN participations pa ON p.id_participant = pa.id_participant
    LEFT JOIN activites a ON pa.id_activite = a.id
    LEFT JOIN titres t ON pa.id_titre = t.id_titre
    LEFT JOIN informations_bancaires ib ON p.id_participant = ib.id_participant
    WHERE a.id = :activite_id
";

$stmt = $bdd->prepare($sql);
$stmt->execute(['activite_id' => $activity_id]); // Passe la valeur du type d'activité ici
$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Création du PDF avec TCPDF
$pdf = new TCPDF();
$pdf->AddPage();
$pdf->SetFont('dejavusans', '', 10); // Utilisation d'une police adaptée

// Construction du tableau HTML
$formatter = new IntlDateFormatter("fr_FR", IntlDateFormatter::LONG, IntlDateFormatter::NONE,"Europe/Paris",IntlDateFormatter::GREGORIAN);
$dateFr = $formatter->format(new DateTime());
$nom_activite = !empty($participants[0]["nom_activite"])? htmlspecialchars($participants[0]["nom_activite"]): '';
$html = '
<style>

thead tr { background-color: #eeeeee; }
h1 { text-align: center; font-size: 16pt; }
h2 { text-align: center; font-size: 14pt; }
table { border-collapse: collapse; width: 100%; }
td, th { border: 1px solid #000; padding: 5px; }
</style>

<table width="100%" style=" margin-bottom: 20px;" border="0">
<tr>
<td style="width: 50%; font-size: 10pt; text-align:center;border: none; ">
    <p><b>REPUBLIQUE DU BENIN </b><br/> ********** </p>
    <p> <b>MINISTÈRE ... </b><br/> **********</p>
    <p><b> DIRECTION ...  </b><br/> </b>**********</p>
    <p> <b> SERVICE ... </b><br/> **********</p>
</td>
<td style="width: 50%; font-size: 10pt; text-align:center; border: none;">
    <p>Cotonou, le'. $dateFr .'. </p>
    <h2>NOTE DE SERVICE</h2>
    <h4>PORTANT CONSTITUTION DES MEMBRES DE LA COMMISSION CHARGÉE DE'. mb_strtoupper($nom_activite) . '</h4>
</td>
</tr>
</table>';

$html .='

    <h4><b>N° :</b> /DEG/MAS/SAFM/SDDC/SEL/SEMC/SIS/SD </h4>
<p>
    <b style="text-decoration:underline;"> Réf :</b> NS N° 0012/MAS/DC/SGM/DPAF/DSI/DEC/SAFM/SEMC/SIS/SA DU 29 DECEMBRE 2023
</p> <br><br> ';


$html .= ' <table border="1" cellpadding="4"  align="center">
            <thead>
                <tr>
                    <th width="7%">N°</th>
                    <th width="15%" >Nom</th>
                    <th width="15%" >Prénoms</th>
                    <th width="15%" >Titre</th>
                    <th width="15%" >Banque</th>
                    <th width="33%" >Numéro de Compte</th>
                </tr>
            </thead>
            <tbody>';
$i = 1;

foreach ($participants as $p) {
    $html .= '<tr>
                <td width="7%" >' . $i++ . '</td>
                <td width="15%" >' . htmlspecialchars($p['nom']) . '</td>
                <td width="15%" >' . htmlspecialchars($p['prenoms']) . '</td>
                <td width="15%" >' . htmlspecialchars($p['titre_participant']) . '</td>
                <td width="15%">' . htmlspecialchars($p['banque']) . '</td>
                <td width="33%" >' . htmlspecialchars($p['numero_compte']) . '</td>
              </tr>';
}

$html .= '</tbody></table> ';

// Ajouter les informations du premier responsable et son titre sous le tableau
$premier_responsable = !empty($participants[0]['premier_responsable']) ? htmlspecialchars($participants[0]['premier_responsable']) : ''; // Si nul, mettre vide
$titre_responsable = !empty($participants[0]['titre_responsable']) ? htmlspecialchars($participants[0]['titre_responsable']) : ''; // Si nul, mettre vide

$html .= '<br><br>';

$html .= '
<h4 style="text-align:center;">' .$titre_responsable . '</h4>
<h4 style="text-align:center ; text-decoration:underline;"> '. $premier_responsable.'</h4> ';

// Écriture du contenu HTML dans le PDF

$pdf->writeHTML($html, true, false, true, false, '');

// Affichage du PDF dans le navigateur
$pdf->Output(__DIR__.'/Note_de_service.pdf', 'F'); // 'F' sauvegarder sur le disque
echo '<a href="Note_de_service.pdf" target="_blank"> Note de service </a>';



// ****ATTESTATION COLLECTIVE****

$pdf1 = new TCPDF();
$pdf1->AddPage();
$pdf1->SetFont('dejavusans', '', 10); // Utilisation d'une police adaptée

// Construction du tableau HTML
$formatter = new IntlDateFormatter("fr_FR", IntlDateFormatter::LONG, IntlDateFormatter::NONE,"Europe/Paris",IntlDateFormatter::GREGORIAN);
$dateFr = $formatter->format(new DateTime());
$html = '

<style>
h1 { text-align: center; font-size: 16pt; }
h2 { text-align: center; font-size: 14pt; }
table { border-collapse: collapse; width: 100%; }
td, th { border: 1px solid #000; padding: 5px; }
</style>
 
<table width="100%" border="0">
<tr>
<td style="width: 50%; font-size: 10pt; text-align:center;border: none; ">
    <p><b>REPUBLIQUE DU BENIN
     <br/>**********  </b></p>
    <p> <b> MINISTÈRE ...
    <br/> **********  </b></p>
    <p>  <b>DIRECTION ... 
    <br/> **********  </b></p>
     <p>  <b> SERVICE ...
     <br/> **********  </b></p>
</td>
<td style="width: 50%; font-size: 10pt; text-align:center; border: none;">
    <p>Cotonou, le'. $dateFr.'. </p>
    <h2>ATTESTATION COLLECTIVE DE TRAVAIL</h2>
    <h4>DES MEMBRES DE LA COMMISSION CHARGÉE DE '. mb_strtoupper($nom_activite) .'</h4>
</td>
</tr>
</table> 
 ';

 $html .= '<br><br><br><br>';

$html .= '

<table border="1" cellpadding="4"  align="center">
            <thead>
                <tr style="background-color:#eeeeee;">
                    <th width="7%">N°</th>
                    <th width="15%">Nom</th>
                    <th width="15%">Prénoms</th>
                    <th width="15%">Titre</th>
                    <th width="15%">Banque</th>
                    <th width="33%">Numéro de Compte</th>
                </tr>
            </thead>
            <tbody>';
$i = 1;
foreach ($participants as $p) {
    $html .= '<tr>
                <td width="7%">' . $i++ . '</td>
                <td width="15%">' . htmlspecialchars($p['nom']) . '</td>
                <td width="15%">' . htmlspecialchars($p['prenoms']) . '</td>
                <td width="15%">' . htmlspecialchars($p['titre_participant']) . '</td>
                <td width="15%">' . htmlspecialchars($p['banque']) . '</td>
                <td width="33%">' . htmlspecialchars($p['numero_compte']) . '</td>
              </tr>';
}


$document = isset($_GET['document']) ? $_GET['document'] : '';

$formatter = new IntlDateFormatter("fr_FR", IntlDateFormatter::LONG, IntlDateFormatter::NONE, "Europe/Paris", IntlDateFormatter::GREGORIAN);
$dateFr = $formatter->format(new DateTime());
$nom_activite = isset($participants[0]["nom_activite"]) ? htmlspecialchars($participants[0]["nom_activite"]) : '';

if ($document === 'note') {
    // *** Note de Service PDF ***
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('trebuc', '', 10);

    $html = '
    <style>
    thead tr { background-color: #eeeeee; }
    h1 { text-align: center; font-size: 16pt; }
    h2 { text-align: center; font-size: 14pt; }
    table { border-collapse: collapse; width: 100%; }
    td, th { border: 1px solid #000; padding: 5px; }
    tr{font-size:8px;}
    P{font-size:11px;}
    </style>
    <table style="margin-bottom: 20px;" border="0">
        <tr>
            <td style="width: 50%; font-size: 10pt; text-align:center; border: none;">
                <p><b>REPUBLIQUE DU BENIN<br/>********</b></p>
                <p><b>MINISTÈRE ...<br/>********</b></p>
                <p><b>DIRECTION ...<br/>********</b></p>
                <p><b>SERVICE ...<br/>********</b></p>
            </td>
            <td style="width: 50%; font-size: 10pt; text-align:center; border: none;">
                <p>Cotonou, le ' . $dateFr . '</p>
                <h2>NOTE DE SERVICE</h2>
                <h4>PORTANT CONSTITUTION DES MEMBRES DE LA COMMISSION CHARGÉE DE ' . mb_strtoupper($nom_activite) . '</h4>
            </td>
        </tr>
    </table>
    <h4><b>N°:</b> /DEG/MAS/SAFM/SDDC/SEL/SEMC/SIS/SD</h4>
    <p><b style="text-decoration:underline;">Réf:</b> NS N° 0012/MAS/DC/SGM/DPAF/DSI/DEC/SAFM/SEMC/SIS/SA DU 29 DECEMBRE 2023</p><br><br>
    <table border="1" cellpadding="4" style="width: 100%; text-align:center">
        <thead>
            <tr style="background-color: #eeeeee;">
                <th style="width: 7%;">N°</th>
                <th style="width: 15%;">NOM</th>
                <th style="width: 15%;">PRENOMS</th>
                <th style="width: 15%;">TITRE</th>
                <th style="width: 15%;">BANQUE</th>
                <th style="width: 33%;">NUMERO DE COMPTE</th>
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
}
 elseif ($document === 'attestation') {
    // *** Attestation Collective PDF ***
    $pdf1 = new TCPDF();
    $pdf1->AddPage();
    $pdf1->SetFont('trebuc', '', 10);

    $html = '
    <style>
    h1 { text-align: center; font-size: 16pt; }
    h2 { text-align: center; font-size: 14pt; }
    table { border-collapse: collapse; width: 100%; }
    td, th { border: 1px solid #000; padding: 5px; }
    tr{font-size:8px;}
    p{font-size:11px;}

    </style>
    <table style="margin-bottom: 20px;" border="0">
        <tr>
            <td style="width: 50%; font-size: 10pt; text-align:center; border: none;">
                <p><b>REPUBLIQUE DU BENIN<br/>********</b></p>
                <p><b>MINISTÈRE ...<br/>********</b></p>
                <p><b>DIRECTION ...<br/>********</b></p>
                <p><b>SERVICE ...<br/>********</b></p>
            </td>
            <td style="width: 50%; font-size: 10pt; text-align:center; border: none;">
                <p>Cotonou, le ' . $dateFr . '</p>
                <h2>ATTESTATION COLLECTIVE DE TRAVAIL</h2>
                <h4>DES MEMBRES DE LA COMMISSION CHARGÉE DE ' . mb_strtoupper($nom_activite) . '</h4>
            </td>
        </tr>
    </table><br><br><br><br>
    <table border="1" cellpadding="4" style="width: 100%;text-align:center">
        <thead>
            <tr style="background-color: #eeeeee;">
                <th style="width: 7%;">N°</th>
                <th style="width: 15%;">NOM</th>
                <th style="width: 15%;">PRENOMS</th>
                <th style="width: 15%;">TITRE</th>
                <th style="width: 15%;">BANQUE</th>
                <th style="width: 33%;">NUMERO DE COMPTE</th>
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
    echo '<p><a href="?document=attestation&id=' . $activity_id . '">Afficher la note de service</a></p>';
    echo '<p><a href="?document=attestation&id=' . $activity_id . '">Afficher l\'Attestation collective</a></p>';}
?>
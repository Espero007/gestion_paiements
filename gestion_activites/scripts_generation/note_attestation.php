<?php
// Inclusions
require_once(__DIR__ . '/../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

session_start();
// Activer le mode debug temporairement (à désactiver en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ob_start();

// Vérifier que $bdd est un objet PDO
if (!($bdd instanceof PDO)) {
    ob_end_clean();
    die('Erreur : la connexion à la base de données a échouée.');
}


//$activity_id = 13;

$errors = [];
$id_user = $_SESSION['user_id'];



// Vérifier si l'ID de l'activité est fourni
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header('Location:' . $_SESSION["previous_url"]);
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
        header('Location:' . $_SESSION["previous_url"]);
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['form_errors'] = ['database' => "Erreur lors de la vérification de l'activité. Veuillez réessayer."];
    header('Location:' . $_SESSION["previous_url"]);
    exit;
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
        a.titre_organisateur
    FROM participations pa
    INNER JOIN participants p ON pa.id_participant = p.id_participant
    INNER JOIN activites a ON pa.id_activite = a.id
    INNER JOIN titres t ON pa.id_titre = t.id_titre
    INNER JOIN informations_bancaires ib ON pa.id_compte_bancaire = ib.id
    WHERE pa.id_activite = :activite_id
";

$stmt = $bdd->prepare($sql);
$stmt->execute(['activite_id' => $activity_id]); // Passe la valeur du type d'activité ici
$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
$titre_activite = $participants[0]['nom_activite'];

// Rédéfinition du header

class Attestation_Collective extends TCPDF
{
    public function Header()
    {
        global $titre_activite;
        // Pour le formattage de la date en français

        $formatter = new IntlDateFormatter(
            'fr_FR',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            'Africa/Lagos',
            IntlDateFormatter::GREGORIAN
        );

        // Charger la police du pdf
        $this->setFont('trebuc', '', 10);
        $this->setY(8); // on descend de 5mm du haut avant de débuter le dessin du header

        // Gestion des deux blocs du header
        $bloc_gauche =  strtoupper("REPUBLIQUE DU BENIN\n*-*-*-*-*\nMINISTERE DE L'ENSEIGNEMENT SUPERIEUR ET SECONDAIRE\n*-*-*-*-*\nDIRECTION DES ............\n*-*-*-*-*\nSERVICE ............");

        $bloc_droite = strtoupper("Cotonou, le " . $formatter->format(new DateTime()) . "\n");

        //Largeur totale disponible entre les marges
        $largeurPage = $this->getPageWidth() - $this->getMargins()['left'] - $this->getMargins()['right'];

        // Largeur d'un bloc
        $largeurBloc = $largeurPage / 2;

        // Sauvegarder la position Y
        $y = $this->GetY();
        // Sauvegarder la position de x à droite
        $x_droite = $this->getMargins()['left'] + $largeurBloc;

        $this->setXY($this->getMargins()['left'], $y);
        $this->MultiCell($largeurBloc, 5, $bloc_gauche, 0, 'C');

        //Bloc de droite (sur la même ligne que le bloc de gauche)
        $this->setXY($this->getMargins()['left'] + $largeurBloc, $y);
        // $this->MultiCell($largeurBloc, 5, $bloc_droite, 0, 'C');

        // Ligne 1
        $ligne1 = strtoupper("Cotonou, le " . $formatter->format(new DateTime()));
        $this->Cell(0, 5, $ligne1, 0, 1, 'C');
        $this->Ln(5);

        // $this->setXY($this->getMargins()['left'], $y);
        // Ligne 2
        $ligne2 = strtoupper("Attestation Collective");
        $this->setFont('trebucbd', '', '11');
        $this->setX($x_droite);
        $this->Cell(0, 5, $ligne2, 0, 1, 'C');
        $this->Ln(5);

        // Ligne 3
        $ligne3 = mb_strtoupper("des membres de la commission chargee de $titre_activite", 'UTF-8');
        $this->setFont('trebuc', '', '10');
        $this->setX($x_droite);
        $this->MultiCell($largeurBloc, 5, $ligne3, 0, 'C');

        // Ligne de séparation
        // $this->Ln(15);
        // $this->Line($this->getMargins()['left'], $this->GetY(), $this->getPageWidth()-$this->getMargins()['right'], $this->GetY());
        // $this->Ln(2);

    }
}

class Note_Service extends TCPDF
{
    public function Header()
    {
        global $titre_activite;
        // Pour le formattage de la date en français

        $formatter = new IntlDateFormatter(
            'fr_FR',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            'Africa/Lagos',
            IntlDateFormatter::GREGORIAN
        );

        // Charger la police du pdf
        $this->setFont('trebuc', '', 10);
        $this->setY(8); // on descend de 5mm du haut avant de débuter le dessin du header

        // Gestion des deux blocs du header
        $bloc_gauche =  strtoupper("REPUBLIQUE DU BENIN\n*-*-*-*-*\nMINISTERE DE L'ENSEIGNEMENT SUPERIEUR ET SECONDAIRE\n*-*-*-*-*\nDIRECTION DES ............\n*-*-*-*-*\nSERVICE ............");

        $bloc_droite = strtoupper("Cotonou, le " . $formatter->format(new DateTime()) . "\n");

        //Largeur totale disponible entre les marges
        $largeurPage = $this->getPageWidth() - $this->getMargins()['left'] - $this->getMargins()['right'];

        // Largeur d'un bloc
        $largeurBloc = $largeurPage / 2;

        // Sauvegarder la position Y
        $y = $this->GetY();
        // Sauvegarder la position de x à droite
        $x_droite = $this->getMargins()['left'] + $largeurBloc;

        $this->setXY($this->getMargins()['left'], $y);
        $this->MultiCell($largeurBloc, 5, $bloc_gauche, 0, 'C');

        //Bloc de droite (sur la même ligne que le bloc de gauche)
        $this->setXY($this->getMargins()['left'] + $largeurBloc, $y);
        // $this->MultiCell($largeurBloc, 5, $bloc_droite, 0, 'C');

        // Ligne 1
        $ligne1 = strtoupper("Cotonou, le " . $formatter->format(new DateTime()));
        $this->Cell(0, 5, $ligne1, 0, 1, 'C');
        $this->Ln(5);

        // $this->setXY($this->getMargins()['left'], $y);
        // Ligne 2
        $ligne2 = strtoupper("note de service");
        $this->setFont('trebucbd', '', '11');
        $this->setX($x_droite);
        $this->Cell(0, 5, $ligne2, 0, 1, 'C');
        $this->Ln(5);

        // Ligne 3
        $ligne3 = mb_strtoupper("portant constitution des membres de la commission chargee de $titre_activite", 'UTF-8');
        $this->setFont('trebuc', '', '10');
        $this->setX($x_droite);
        $this->MultiCell($largeurBloc, 5, $ligne3, 0, 'C');

        // Ligne de séparation
        // $this->Ln(15);
        // $this->Line($this->getMargins()['left'], $this->GetY(), $this->getPageWidth()-$this->getMargins()['right'], $this->GetY());
        // $this->Ln(2);

    }
}


// // Création du PDF avec TCPDF
// $pdf = new TCPDF();
// $pdf->AddPage();
// $pdf->AddFont('trebucbd', 'trebucbd.php');
// $pdf->SetFont('trebuc', '', 10); // Utilisation d'une police adaptée

// // Construction du tableau HTML
// $formatter = new IntlDateFormatter("fr_FR", IntlDateFormatter::LONG, IntlDateFormatter::NONE,"Europe/Paris",IntlDateFormatter::GREGORIAN);
// $dateFr = $formatter->format(new DateTime());
// $nom_activite = !empty($participants[0]["nom_activite"])? htmlspecialchars($participants[0]["nom_activite"]): '';

// $html = '
// <style>

// thead tr { background-color: #eeeeee; }
// h1 { text-align: center; font-size: 16pt; }
// h2 { text-align: center; font-size: 14pt; }
// table { border-collapse: collapse; width: 100%; }
// td, th { border: 1px solid #000; padding: 5px; }
// </style>

// <table width="100%" style=" margin-bottom: 20px;" border="0">
// <tr>
// <td style="width: 50%; font-size: 10pt; text-align:center;border: none; ">
//     <p><b>REPUBLIQUE DU BENIN </b><br/> ********** </p>
//     <p> <b>MINISTÈRE ... </b><br/> **********</p>
//     <p><b> DIRECTION ...  </b><br/> </b>**********</p>
//     <p> <b> SERVICE ... </b><br/> **********</p>
// </td>
// <td style="width: 50%; font-size: 10pt; text-align:center; border: none;">
//     <p>Cotonou, le'. $dateFr .'. </p>
//     <h2>NOTE DE SERVICE</h2>
//     <h4>PORTANT CONSTITUTION DES MEMBRES DE LA COMMISSION CHARGÉE DE'. mb_strtoupper($nom_activite) . '</h4>
// </td>
// </tr>
// </table>';

// $html .='

//     <h4><b>N° :</b> /DEG/MAS/SAFM/SDDC/SEL/SEMC/SIS/SD </h4>
// <p>
//     <b style="text-decoration:underline;"> Réf :</b> NS N° 0012/MAS/DC/SGM/DPAF/DSI/DEC/SAFM/SEMC/SIS/SA DU 29 DECEMBRE 2023
// </p> <br><br> ';


// $html .= ' <table border="1" cellpadding="4"  align="center">
//             <thead>
//                 <tr>
//                     <th width="7%">N°</th>
//                     <th width="15%" >Nom</th>
//                     <th width="15%" >Prénoms</th>
//                     <th width="15%" >Titre</th>
//                     <th width="15%" >Banque</th>
//                     <th width="33%" >Numéro de Compte</th>
//                 </tr>
//             </thead>
//             <tbody>';
// $i = 1;

// foreach ($participants as $p) {
//     $html .= '<tr>
//                 <td width="7%" >' . $i++ . '</td>
//                 <td width="15%" >' . htmlspecialchars($p['nom']) . '</td>
//                 <td width="15%" >' . htmlspecialchars($p['prenoms']) . '</td>
//                 <td width="15%" >' . htmlspecialchars($p['titre_participant']) . '</td>
//                 <td width="15%">' . htmlspecialchars($p['banque']) . '</td>
//                 <td width="33%" >' . htmlspecialchars($p['numero_compte']) . '</td>
//               </tr>';
// }

// $html .= '</tbody></table> ';

// // Ajouter les informations du premier responsable et son titre sous le tableau

// // $premier_responsable = !empty($participants[0]['premier_responsable']) ? htmlspecialchars($participants[0]['premier_responsable']) : ''; // Si nul, mettre vide
// // $titre_responsable = !empty($participants[0]['titre_responsable']) ? htmlspecialchars($participants[0]['titre_responsable']) : ''; // Si nul, mettre vide

// // $html .= '<br><br>';

// // $html .= '
// // <h4 style="text-align:center;">' .$titre_responsable . '</h4>
// // <h4 style="text-align:center ; text-decoration:underline;"> '. $premier_responsable.'</h4> ';

// // // Écriture du contenu HTML dans le PDF

// // $pdf->writeHTML($html, true, false, true, false, '');

// // Affichage du PDF dans le navigateur
// $pdf->Output(__DIR__.'/Note_de_service.pdf', 'F'); // 'F' sauvegarder sur le disque
// echo '<a href="Note_de_service.pdf" target="_blank"> Note de service </a>';



// // ****ATTESTATION COLLECTIVE****

// $pdf1 = new TCPDF();
// $pdf1->AddPage();
// $pdf1->SetFont('trebuc', '', 10); // Utilisation d'une police adaptée

// // Construction du tableau HTML
// $formatter = new IntlDateFormatter("fr_FR", IntlDateFormatter::LONG, IntlDateFormatter::NONE,"Europe/Paris",IntlDateFormatter::GREGORIAN);
// $dateFr = $formatter->format(new DateTime());
// $html = '

// <style>
// h1 { text-align: center; font-size: 16pt; }
// h2 { text-align: center; font-size: 14pt; }
// table { border-collapse: collapse; width: 100%; }
// td, th { border: 1px solid #000; padding: 5px; }
// </style>

// <table width="100%" border="0">
// <tr>
// <td style="width: 50%; font-size: 10pt; text-align:center;border: none; ">
//     <p><b>REPUBLIQUE DU BENIN
//      <br/>**********  </b></p>
//     <p> <b> MINISTÈRE ...
//     <br/> **********  </b></p>
//     <p>  <b>DIRECTION ... 
//     <br/> **********  </b></p>
//      <p>  <b> SERVICE ...
//      <br/> **********  </b></p>
// </td>
// <td style="width: 50%; font-size: 10pt; text-align:center; border: none;">
//     <p>Cotonou, le'. $dateFr.'. </p>
//     <h2>ATTESTATION COLLECTIVE DE TRAVAIL</h2>
//     <h4>DES MEMBRES DE LA COMMISSION CHARGÉE DE '. mb_strtoupper($nom_activite) .'</h4>
// </td>
// </tr>
// </table> 
//  ';

//  $html .= '<br><br><br><br>';

// $html .= '

// <table border="1" cellpadding="4"  align="center">
//             <thead>
//                 <tr style="background-color:#eeeeee;">
//                     <th width="7%">N°</th>
//                     <th width="15%">Nom</th>
//                     <th width="15%">Prénoms</th>
//                     <th width="15%">Titre</th>
//                     <th width="15%">Banque</th>
//                     <th width="33%">Numéro de Compte</th>
//                 </tr>
//             </thead>
//             <tbody>';
// $i = 1;
// foreach ($participants as $p) {
//     $html .= '<tr>
//                 <td width="7%">' . $i++ . '</td>
//                 <td width="15%">' . htmlspecialchars($p['nom']) . '</td>
//                 <td width="15%">' . htmlspecialchars($p['prenoms']) . '</td>
//                 <td width="15%">' . htmlspecialchars($p['titre_participant']) . '</td>
//                 <td width="15%">' . htmlspecialchars($p['banque']) . '</td>
//                 <td width="33%">' . htmlspecialchars($p['numero_compte']) . '</td>
//               </tr>';
// }


$document = isset($_GET['document']) ? $_GET['document'] : '';

$formatter = new IntlDateFormatter("fr_FR", IntlDateFormatter::LONG, IntlDateFormatter::NONE, "Europe/Paris", IntlDateFormatter::GREGORIAN);
$dateFr = $formatter->format(new DateTime());
$nom_activite = isset($participants[0]["nom_activite"]) ? htmlspecialchars($participants[0]["nom_activite"]) : '';

if ($document === 'note') {
    // *** Note de Service PDF ***
    $pdf = new Note_Service();
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
    <br><br><br><br><br><br><br><br>
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

    // $premier_responsable = isset($participants[0]['premier_responsable']) ? htmlspecialchars($participants[0]['premier_responsable']) : '';
    // $titre_responsable = isset($participants[0]['titre_responsable']) ? htmlspecialchars($participants[0]['titre_responsable']) : '';
    // $html .= '<br><br>
    // <h4 style="text-align:center;">' . $titre_responsable . '</h4>
    // <h4 style="text-align:center; text-decoration:underline;">' . $premier_responsable . '</h4>';

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
    // *** Attestation Collective PDF ***
    $pdf1 = new Attestation_Collective('P', 'mm', 'A4');
    $pdf1->AddPage();
    $pdf1->SetFont('trebuc', '', 10);
    // $pdf->Ln(0);

    $html = '
    <style>
    h1 { text-align: center; font-size: 16pt; }
    h2 { text-align: center; font-size: 14pt; }
    table { border-collapse: collapse; width: 100%; }
    td, th { border: 1px solid #000; padding: 5px; }
    tr{font-size:8px;}
    p{font-size:11px;}
    </style>
<br><br><br><br><br><br><br><br><br><br>
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

    $pdf1->writeHTML($html, true, false, true, false, '');
    ob_clean();
    ob_end_clean();

    $premier_responsable = isset($participants[0]['premier_responsable']) ? htmlspecialchars($participants[0]['premier_responsable']) : '';
    $titre_responsable = isset($participants[0]['titre_responsable']) ? htmlspecialchars($participants[0]['titre_responsable']) : '';
    $financier = isset($participants[0]['financier']) ? htmlspecialchars($participants[0]['financier']) : '';
    $titre_financier = isset($participants[0]['titre_financier']) ? htmlspecialchars($participants[0]['titre_financier']) : '';
    // $html .= '<br><br><br><br>
    // <table border="0" style="width: 100%;">
    //     <tr>
    //         <td style="width: 50%; font-size: 10pt; border: none;">
    //             <h4 style="margin-bottom:3em">' . $titre_responsable . '</h4>
    //             <h4 style="text-decoration:underline;">' . $premier_responsable . '</h4>
    //         </td>
    //         <td style="width: 50%; font-size: 10pt; border: none;">
    //             <h4 style="margin-bottom:3em">' . $titre_financier . '</h4>
    //             <h4 style="text-decoration:underline;">' . $financier . '</h4>
    //         </td>
    //     </tr>
    // </table>';

    // Ajouter les informations du premier responsable et son titre sous le tableau

    $pdf1->Ln(10);
    $bloc_gauche = mb_strtoupper($participants[0]['titre_organisateur']);
    $bloc_droite = mb_strtoupper($participants[0]['titre_responsable']);
    afficherTexteDansDeuxBlocs($pdf1, $bloc_gauche, $bloc_droite, 'trebucbd', 10, 5, 'C', '', 'C', '');

    $bloc_gauche = mb_strtoupper($participants[0]['organisateur']);
    $bloc_droite = mb_strtoupper($participants[0]['premier_responsable']);
    afficherTexteDansDeuxBlocs($pdf1, $bloc_gauche, $bloc_droite, 'trebucbd', 10, 2, 'C', 'U', 'C', 'U');


    $pdf1->Output('Attestation collective.pdf', 'I');
} else {
    // Afficher une interface pour choisir le document
    // ob_end_clean();
    // echo '<h2>Choisir un document à afficher :</h2>';
    // echo '<p><a href="?document=attestation&id=' . $activity_id . '">Afficher la note de service</a></p>';
    // echo '<p><a href="?document=attestation&id=' . $activity_id . '">Afficher l\'Attestation collective</a></p>';
}

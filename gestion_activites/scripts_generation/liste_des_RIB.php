<?php
ob_start();
session_start();
require_once(__DIR__ . '/../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

// Validations

if(!valider_id('get', 'id', '', 'participations_activites')){
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}
$id_activite = $_GET['id'];

// 🔖 Récupération du titre
$sqlTitre = "SELECT nom FROM activites WHERE id = :id_activite";
$stmtTitre = $bdd->prepare($sqlTitre);
$stmtTitre->bindParam(':id_activite', $id_activite, PDO::PARAM_INT);
$stmtTitre->execute();
$titre_activite = $stmtTitre->fetchColumn();

// 📊 Requête
$sql = "
SELECT 
    p.nom, 
    p.prenoms,
    ib.numero_compte AS rib
FROM participations pa
INNER JOIN participants p ON pa.id_participant = p.id_participant
INNER JOIN informations_bancaires ib ON pa.id_compte_bancaire = ib.id
WHERE pa.id_activite = :id_activite
ORDER BY p.nom ASC
";
$stmt = $bdd->prepare($sql);
$stmt->bindParam(':id_activite', $id_activite, PDO::PARAM_INT);
$stmt->execute();
$resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 🖨️ Génération PDF
$pdf = new TCPDF('P', 'mm', 'A4');
$pdf->setPrintHeader(false);
$pdf->setMargins(15, 25, 15);
$pdf->setAutoPageBreak(true, 25);
$pdf->AddPage();

$infos_header = ['titre' => $titre_activite];
genererHeader($pdf, 'liste_ribs', $infos_header, $id_activite);

$pdf->Ln(30);

// Titre de la page

$pdf->setFont('trebucbd', '', 16);
$pdf->Cell(0, 10, mb_strtoupper('Liste des RIBs des participants associés à l\'activité', 'UTF-8'), 0, 1, 'C');
$pdf->Ln(8);

$pdf->setFont('trebuc', '', 10);

// 🧾 Génération du HTML
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

$html = $style;

$html .= '<table border="1" cellpadding="5" width="100%">';
$html .= '<tr><th>N°</th><th>NOM ET PRENOMS</th><th>RIB</th></tr>';

foreach ($resultats as $i => $ligne) {
    $nomPrenoms = htmlspecialchars($ligne['nom'] . ' ' . $ligne['prenoms']);
    $rib = htmlspecialchars($ligne['rib']);

    $html .= "<tr>
        <td>" . ($i + 1) . "</td>
        <td>$nomPrenoms</td>
        <td>$rib</td>
    </tr>";
}

$html .= '</table>';

// 🧾 Écriture dans le PDF
$pdf->writeHTML($html, true, false, true, false, '');

// 📤 Sortie
ob_end_clean();
$pdf->Output('', 'I');

// $pdf->Cell()
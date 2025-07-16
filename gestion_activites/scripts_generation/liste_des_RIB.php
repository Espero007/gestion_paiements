<?php
ob_start();
session_start();
require_once(__DIR__ . '/../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

$id_activite = 3;
$banque = 'UBA';

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

// 🎯 Header
$infos_header = ['titre' => $titre_activite, 'banque' => $banque];
genererHeader($pdf, 'Liste_des_RIB', $infos_header);
$pdf->Ln(10);

// 🧾 Génération du HTML
$html = '<style>
    table { border-collapse: collapse; width: 100%; font-size: 10pt; }
    th, td { border: 1px solid #999; padding: 4px; text-align: center; }
    th { background-color: #e6e6e6; font-weight: bold; }
</style>';

$html .= '<table>';
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

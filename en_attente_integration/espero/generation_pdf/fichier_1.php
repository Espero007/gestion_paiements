<?php
require_once(__DIR__ . '/../../../tcpdf/tcpdf.php');

// Création du PDF
$pdf = new TCPDF();

// Configuration du document
$pdf->setCreator(PDF_CREATOR);
$pdf->setAuthor('Tobi');
$pdf->setTitle('Document Administratif');
$pdf->setMargins(15, 20, 15);
$pdf->AddPage();

// Contenu de l'entête
$html_header = <<<EOD
<h2 style="text-align:center;">Ministère de l'éducation</h2>
<p>
    <strong>Nom:</strong> Jean Dupont <br>
    <strong>Date :</strong> 15/05/2025 <br>
    <strong>Objet:</strong> Convocation à la réunion
</p>
<hr>
EOD;

// Contenu du tableau
$html_table = <<<EOD
<table border="1" cellpaddin="4">
    <thead>
        <tr style="background-color:#f0f0f0;">
            <th>Nom</th>
            <th>Fonction</th>
            <th>Présence</th>
        </tr>
    <thead>
    <tbody>
        <tr>
            <td>Jean Dupont</td>
            <td>Directeur</td>
            <td>Oui</td>
        </tr>
        <tr>
            <td>Claire Martin</td>
            <td>Secrétaire</td>
            <td>Non</td>
        </tr>
    </tbody>
</table>
EOD;

// Ecriture dans le PDF
$pdf->writeHTML($html_header, true, false, true, false, '');
$pdf->writeHTML($html_table, true, false, true, false, '');

//Sortie du PDF
$pdf->Output('document.pdf', 'I'); // I = inline dans le navigateur

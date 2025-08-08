<?php
session_start();
require_once(__DIR__ . '/../../includes/bdd.php');
require_once(__DIR__ . '/../../includes/constantes_utilitaires.php');

use setasign\Fpdi\Tcpdf\Fpdi;

/** Générations des fichiers à inclure dans le document fusionné */

if (!valider_id('get', 'id', '', 'participations_activites')) {
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}
$id_activite = dechiffrer($_GET['id']);

// Nombre de répétition
$nbr_ordre_virement = 3;
$nbr_synthese_ordres = 1;
$nbr_liste_ribs = 1;
$nbr_note_service = 1;
$nbr_attestation = 2;
$nbr_etats_paiement = 3;

/** Note de service */

for ($i = 0; $i < $nbr_note_service; $i++) {
    // $fichiers[] = genererNoteAttestation($id_activite, 'note', false);
    $fichiers[] = genererNoteService($id_activite, false);
}

/** Attestation collective */
for ($i = 0; $i < $nbr_attestation; $i++) {
    $fichiers[] = genererAttestation($id_activite, false);
}

/**Etat de paiement */

for ($i = 0; $i < $nbr_etats_paiement; $i++) {
    $fichiers[] = genererEtatPaiement2($id_activite, false);
}

/** Ordres de virement */

$banques = listeBanques($id_activite);

foreach ($banques as $banque) {
    for ($i = 0; $i < $nbr_ordre_virement; $i++) {
        $chemin_fichier = genererOrdreVirement($id_activite, $banque, false);
        $fichiers[] = $chemin_fichier;
    }
}

// $chemins[] = $chemin_fichier;

// for ($i = 0; $i < $nbr_ordre_virement; $i++) {
//     for ($j = 0; $j < count($chemins); $j++) {
//         $fichiers[] = $chemins[$j];
//     }
// }

/** Synthèse des ordres de virement */

for ($i = 0; $i < $nbr_synthese_ordres; $i++) {
    $fichiers[] = genererSyntheseOrdres($id_activite, false);
}

/** Liste des RIBS */

for ($i = 0; $i < $nbr_liste_ribs; $i++) {
    $fichiers[] = genererListeRIBS($id_activite, false);
}

// Classe personnalisée avec Footer()
// class PDFFusion extends Fpdi
// {
//     // public function Footer()
//     // {
//     //     // $this->SetY(-15);
//     //     $this->SetFont('trebucbd', '', 10);
//     //     // $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'R');
//     //     $this->Cell(0, 10, $this->getAliasNumPage(), 0, 0, 'R');
//     // }
// }

$pdf = new Fpdi();
$pdf->setMargins(15, 25, 15);
$pdf->setAutoPageBreak(true, 25); // marge bas = 25 pour footer
$pdf->SetFooterMargin(25);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
configuration_pdf($pdf, $_SESSION['nom'] . ' ' . $_SESSION['prenoms'], 'Fusion des documents');

foreach ($fichiers as $fichier) {
    $pageCount = $pdf->setSourceFile($fichier);
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $tpl = $pdf->importPage($pageNo);
        $size = $pdf->getTemplateSize($tpl);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tpl);
    }
}

// Affichage du PDF final
$pdf->Output('Fusion des documents.pdf', 'I');

// Suppression des éléments

foreach ($fichiers as $fichier) {
    if (file_exists($fichier)) {
        unlink($fichier);
    }
}

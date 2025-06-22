<?php
require_once(__DIR__.'/../../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../../includes/constantes_utilitaires.php');

class PDF extends TCPDF
{
    public function Header()
    {
        // Charger la police du prof
        $this->setFont('trebuc', '', 11);
        $this->setY(5); // descend à 5mm du haut

        // Définir la police
        // $this->SetFont('helvetica', '', 10);

        // Bloc de gauche
        $left_bloc = "Republique du benin\n*-*-*-*-*\nMinistere de l'enseignement supérieur et secondaire\n*-*-*-*-*\nDirection des............\n*-*-*-*-*\nService............";
        //Bloc de droite
        $right_bloc = "Cotonou, le...\n\nETAT DE PAIEMENT N°\nINDEMNITES ET FRAIS D'ENTRETIEN ACCORDES AUX MEMBRES DE LA COMMISSION CHARGEE DE LA CORRECTION DES EXAMENS DE..., SESSION 2020";

        // Largeur totale disponible entre les marges
        $pageLargeur = $this->getPageWidth() - $this->getMargins()['left'] - $this->getMargins()['right'];

        // Largeur du bloc
        $blocWidth = $pageLargeur/2;

        // Sauvegarder la position Y
        $y = $this->GetY();

        // Bloc gauche
        $this->setXY($this->getMargins()['left'], $y);
        $this->MultiCell($blocWidth, 5, $left_bloc, 0, 'C',);

        // Bloc droit (sur la même ligne que le bloc gauche)
        $this->setXY($this->getMargins()['left']+$blocWidth, $y);
        $this->MultiCell($blocWidth, 5, $right_bloc, 0, 'R');


        // $this->Cell(0, 10, 'this is a texte header', 0, 1, 'C');

        // Ligne de séparation
        $this->Ln(20);
        $this->Line($this->getMargins()['left'], $this->GetY(), $this->getPageWidth() - $this->getMargins()['right'], $this->GetY());
        $this->Ln(2);
    }
}

$pdf = new PDF('P', 'mm', 'A4');

// Configuration du document
$pdf->setCreator(PDF_CREATOR);
$pdf->setAuthor('Tobi');
$pdf->setTitle('Document Administratif - 2');
$pdf->setMargins(15, 20, 15);
$pdf->setAutoPageBreak(true, 25); // marge bas = 25 pour footer
$pdf->AddPage();

// Sortie du PDF
$pdf->Output('document.pdf', 'I');

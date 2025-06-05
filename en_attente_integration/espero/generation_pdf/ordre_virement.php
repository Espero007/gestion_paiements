<?php

require_once(__DIR__ . '/../../../tcpdf/tcpdf.php');
require_once(__DIR__ . '/../../../includes/constantes_utilitaires.php');

class Ordre_Virement extends TCPDF {
    public function Header()
    {
        // Charger la police du pdf
        $this->setFont('trebuc', '', 12);
        $this->setY(5); // on descend de 5mm du haut avant de débuter le dessin du header

        // Gestion des deux blocs du header
        $bloc_gauche =  strtoupper("REPUBLIQUE DU BENIN\n*-*-*-*-*\nMINISTERE DE L'ENSEIGNEMENT SUPERIEUR ET SECONDAIRE\n*-*-*-*-*\nDIRECTION DES............\n*-*-*-*-*\nSERVICE............");

        $bloc_droite = strtoupper('Cotonou, le'.date('l j m Y').'\n');

        //Largeur totale disponible entre les marges
        $largeurPage = $this->getPageWidth() - $this->getMargins()['left'] - $this->getMargins()['right'];

        // Largeur d'un bloc
        $largeurBloc = $largeurPage/2;

        // Sauvegarder la position Y
        $y = $this->GetY();

        $this->setXY($this->getMargins()['left'], $y);
        $this->MultiCell($largeurBloc, 5, $bloc_gauche, 0, 'C');

        //Bloc de droite (sur la même ligne que le bloc de gauche)
        $this->setXY($this->getMargins()['left']+$largeurBloc, $y);
        $this->MultiCell($largeurBloc, 5, $bloc_droite, 0, 'R');

        // Ligne de séparation
        $this->Ln(45);
        $this->Line($this->getMargins()['left'], $this->GetY(), $this->getPageWidth()-$this->getMargins()['right'], $this->GetY());
        $this->Ln(2);
        
    }
}

$pdf = new Ordre_Virement('P', 'mm', 'A4');

// Configuration du document
configuration_pdf($pdf, 'Tobi', 'Ordre de virement (tests)');
$pdf->setMargins(15, 20, 15);
$pdf->setAutoPageBreak(true, 25); // marge bas = 25 pour footer
$pdf->AddPage();

//Sortie du pdf
$pdf->Output('ordre de virement.pdf', 'I');

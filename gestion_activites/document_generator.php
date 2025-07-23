<?php

require_once(__DIR__ . '/../tcpdf/tcpdf.php'); // Chemin vers TCPDF

/*
 * Fonction générique pour obtenir le contenu binaire d'un PDF à partir de son script de génération.
 *
 * @param string $pdfScriptPath Le chemin absolu ou relatif à DOCUMENT_ROOT du script PHP qui génère le PDF.
 * @param array $params Les paramètres GET à passer temporairement au script de génération.
 * @return string|false Le contenu binaire du PDF ou false en cas d'échec.
 */

function getPdfBinaryContent(string $pdfScriptPath, array $params = []): string|false
{
    // Sauvegarder l'état actuel de $_GET et définir temporairement pour le script inclus
    $original_get = $_GET;
    $_GET = $params;

    ob_start();
    // Utiliser DOCUMENT_ROOT pour inclure le script de manière robuste
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $pdfScriptPath;
    if (!file_exists($fullPath)) {
        error_log("Fichier de script PDF introuvable : " . $fullPath);
        $_GET = $original_get; // Restaurer $_GET même en cas d'erreur de chemin
        return false;
    }
    require $fullPath;
    $pdfBinaryContent = ob_get_clean();

    // Restaurer l'état original de $_GET
    $_GET = $original_get;

    return $pdfBinaryContent;
}


/*
 * Génère et retourne le contenu binaire d'un PDF fusionné à partir de plusieurs scripts.
 *
 * @param array $documentInfos Un tableau de tableaux, chaque sous-tableau contenant:
 * ['script_path' => '/chemin/vers/script.php', 'params' => ['param1' => 'value1']]
 * @param string $outputFilename Le nom de fichier suggéré pour le PDF fusionné.
 * @return string Le contenu binaire du PDF fusionné.
 * @throws Exception Si aucun contenu PDF n'est fourni ou si la fusion échoue.
 

function generateMergedPdf(array $documentInfos, string $outputFilename = 'document_fusionne.pdf'): string
{
    if (empty($documentInfos)) {
        throw new Exception("Aucun document fourni pour la fusion.");
    }

    $pdfsToMergeContent = [];
    $pdfGenerationErrors = [];

    foreach ($documentInfos as $info) {
        $pdfBinaryContent = getPdfBinaryContent($info['script_path'], $info['params']);

        if ($pdfBinaryContent !== false && !empty($pdfBinaryContent)) {
            $pdfsToMergeContent[] = $pdfBinaryContent;
        } else {
            $pdfGenerationErrors[] = "Erreur de génération pour le script " . htmlspecialchars($info['script_path']);
        }
    }

    if (empty($pdfsToMergeContent)) {
        throw new Exception("Aucun PDF n'a pu être généré pour la fusion. Détails: " . implode("; ", $pdfGenerationErrors));
    }

    $pdf = new Fpdi(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    foreach ($pdfsToMergeContent as $pdfContent) {
        $tempPdfFile = tempnam(sys_get_temp_dir(), 'merged_pdf_') . '.pdf';
        file_put_contents($tempPdfFile, $pdfContent);

        try {
            $pageCount = $pdf->setSourceFile($tempPdfFile);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $templateId = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($templateId);

                if ($size['width'] > $size['height']) {
                    $pdf->AddPage('L', [$size['width'], $size['height']]);
                } else {
                    $pdf->AddPage('P', [$size['width'], $size['height']]);
                }
                $pdf->useTemplate($templateId);
            }
        } finally {
            if (file_exists($tempPdfFile)) {
                unlink($tempPdfFile);
            }
        }
    }

    return $pdf->Output($outputFilename, 'S');
}
*/

/*
 * Génère un fichier ZIP contenant plusieurs PDF.
 *
 * @param array $documentInfos Un tableau de tableaux, chaque sous-tableau contenant:
 * ['id' => 'doc_id', 'label' => 'Doc Label', 'script_path' => '/chemin/vers/script.php', 'params' => ['param1' => 'value1']]
 * @param string $zipFilename Le nom de fichier suggéré pour le fichier ZIP.
 * @return string Le contenu binaire du fichier ZIP.
 * @throws Exception Si aucun contenu PDF n'est généré ou si la création du ZIP échoue.
 */

function generatePdfZip(array $documentInfos, string $zipFilename = 'documents.zip'): string
{
    if (empty($documentInfos)) {
        throw new Exception("Aucun document fourni pour le ZIP.");
    }

    $contenusPdfs = [];
    $pdfGenerationErrors = [];

    foreach ($documentInfos as $info) {
        $pdfBinaryContent = getPdfBinaryContent($info['script_path'], $info['params']);

        if ($pdfBinaryContent !== false && !empty($pdfBinaryContent)) {
            $filenameInZip = str_replace(' ', '_', $info['label']) . '.pdf';
            $contenusPdfs[$filenameInZip] = $pdfBinaryContent;
        } else {
            $pdfGenerationErrors[] = "Erreur de génération pour '" . htmlspecialchars($info['label']) . "'";
        }
    }

    if (empty($contenusPdfs)) {
        throw new Exception("Aucun PDF n'a pu être généré pour le ZIP. Détails: " . implode("; ", $pdfGenerationErrors));
    }

    $cheminFichierZip = sys_get_temp_dir() . '/' . uniqid('zip_temp_') . '.zip';
    $zip = new ZipArchive();

    if ($zip->open($cheminFichierZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        foreach ($contenusPdfs as $filename => $content) {
            $zip->addFromString($filename, $content);
        }
        $zip->close();

        $zipContent = file_get_contents($cheminFichierZip);
        unlink($cheminFichierZip); // Supprimer le fichier temporaire

        return $zipContent;
    } else {
        throw new Exception("Erreur : Impossible de créer le fichier ZIP.");
    }
}
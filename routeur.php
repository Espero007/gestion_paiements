<?php
require_once(__DIR__ . '/includes/constantes_utilitaires.php');
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Liste noire : fichiers sensibles à bloquer
$protected_files = ['cle.php', 'crypto.php', 'routes.php', 'redirect.php'];

foreach ($protected_files as $file) {
    if (basename($uri) === $file) {
        // http_response_code(403);
        // echo 'Accès interdit.';
        redirigerVersPageErreur(403);
        // exit;
    }
}

if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

require_once(__DIR__ . '/redirect.php');

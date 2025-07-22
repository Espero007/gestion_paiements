<?php
// Assure-toi que cle.php retourne bien la clé.
// Si ce fichier est inclus via public/index.php, cle.php sera déjà disponible
// en tant que retour de require.
// Define SECRET_KEY seulement si elle n'est pas déjà définie.
if (!defined('SECRET_KEY')) {
    define('SECRET_KEY', require __DIR__ . '/../autres/cle.php');
}

const METHOD = 'AES-128-CTR';

function chiffrer($id)
{
    if (empty(SECRET_KEY)) {
        trigger_error('SECRET_KEY non définie ou vide dans Crypto.php', E_USER_ERROR);
    }
    $iv = random_bytes(openssl_cipher_iv_length(METHOD));
    $chiffre = openssl_encrypt($id, METHOD, SECRET_KEY, 0, $iv);
    if ($chiffre === false) {
        trigger_error('Erreur de chiffrement: ' . openssl_error_string(), E_USER_WARNING);
        return false;
    }
    return bin2hex($iv) . ':' . $chiffre;
}

function dechiffrer($valeur)
{
    if (empty(SECRET_KEY)) {
        trigger_error('SECRET_KEY non définie ou vide dans Crypto.php', E_USER_ERROR);
    }
    // Gérer le cas où $valeur ne contient pas ':'
    if (strpos($valeur, ':') === false) {
        trigger_error('Format de valeur chiffrée invalide: le séparateur ":" est manquant.', E_USER_WARNING);
        return false;
    }

    [$iv_hex, $chiffre] = explode(':', $valeur, 2); // Limite à 2 pour éviter des problèmes si le chiffré contient des ':'
    $iv = hex2bin($iv_hex);

    // Vérifier la longueur de l'IV
    if (strlen($iv) !== openssl_cipher_iv_length(METHOD)) {
        trigger_error('Longueur d\'IV invalide.', E_USER_WARNING);
        return false;
    }

    $dechiffre = openssl_decrypt($chiffre, METHOD, SECRET_KEY, 0, $iv);
    if ($dechiffre === false) {
        trigger_error('Erreur de déchiffrement: ' . openssl_error_string(), E_USER_WARNING);
        return false;
    }
    return $dechiffre;
}

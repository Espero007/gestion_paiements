<?php
// Définit le chemin racine du projet
define('ROOT_PATH', dirname(__DIR__));

// 1. Inclure les fichiers essentiels
require_once ROOT_PATH . '/gestion_paiements/autres/cle.php'; // La clé est définie ici
require_once ROOT_PATH . '/gestion_paiements/crypto.php'; // Les fonctions de chiffrement
require_once ROOT_PATH . '/gestion_paiements/includes/constantes_utilitaires.php'; // Les fonctions d'aide, dont generateUrl()

// 2. Charger les définitions de routes
$routes = require ROOT_PATH . '/gestion_paiements/routes.php';

// 3. Récupérer l'URI de la requête
// Supprime les slashes au début et à la fin pour faciliter la correspondance
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

$trouve = false;

// 4. Parcourir les routes pour trouver une correspondance
foreach ($routes as $pattern => $callback) {
    // Utilise preg_match pour vérifier si l'URI correspond au pattern de la route
    // et extraire les paramètres (les groupes de capture dans la regex)
    if (preg_match('#^' . $pattern . '$#', $uri, $matches)) {
        array_shift($matches); // Supprime le match complet (premier élément)
        call_user_func_array($callback, $matches); // Appelle la fonction de callback avec les paramètres extraits
        $trouve = true;
        break; // Une fois qu'une route est trouvée, on arrête de chercher
    }
}

// 5. Si aucune route n'a été trouvée, afficher une page 404
if (!$trouve) {
    http_response_code(404);
    echo '<h1>404 - Page non trouvée</h1>';
}
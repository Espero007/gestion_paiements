<?php
require_once(__DIR__.'/crypto.php');
// Les chemins ici ne sont pas des fichiers de vue, mais des méthodes de contrôleurs
// que tu devras implémenter. Pour l'instant, je les laisse comme des fichiers
// pour que ce soit compatible avec ton setup actuel.
// Idéalement, une route appellerait une méthode spécifique d'un contrôleur.

return [
    // Page d'accueil
    '' => function () {
        require __DIR__ . '/index.php'; 
    },

    // Pages simples
    'participants' => function () {
        require __DIR__ . '/gestion_participants/voir_participants.php';
    },
    'mon_compte' => function () {
        require __DIR__ . '/parametres/gestion_compte/voir_profil.php';
    },
    'connexion' => function () {
        require __DIR__ . '/auth/connexion.php';
    },
    'inscription' => function(){
        require __DIR__.'/auth/inscription.php';
    },
    'activites' => function(){
        require __DIR__.'/gestion_activites/voir_activites.php';
    },

    // Page pour gérer un participant via token chiffré
    // IMPORTANT : Utilisez '([^/]+)' pour capturer n'importe quelle chaîne non vide sans slash
    'participants/gerer/([^/]+)' => function ($token) {
        // Ces "echo" sont pour le débogage, tu peux les retirer une fois que ça marche
        echo "<pre>Route matchée. Token reçu : $token</pre>";
        $decoded = urldecode($token);
        echo "<pre>Token décodé : $decoded</pre>";

        // Assure-toi que la fonction dechiffrer est accessible (incluse via public/index.php)
        $id = dechiffrer($decoded);

        if ($id === false || $id === null || !is_numeric($id)) { // Vérifie aussi si $id est un nombre valide
            http_response_code(400); // Bad Request
            echo "<h1>400 - ID invalide ou token corrompu.</h1>";
            return;
        }

        $_GET['id'] = $id; // Passe l'ID déchiffré à la vue via $_GET
        require __DIR__ . '/gestion_participants/gerer_participant.php';
    },

    // Route par défaut (pour les autres fichiers PHP qui pourraient être dans des sous-dossiers)
    // C'est une route de secours. L'idéal est de définir toutes les routes explicitement.
    '(.*)' => function ($path) {
        // Attention : Cette route est très générique et peut cacher des erreurs
        // ou des chemins non intentionnels si tes routes spécifiques ne sont pas bien définies.
        // Utilise-la avec prudence et préfère des routes explicites.

        // Tentative de charger une vue si elle existe directement
        $viewPath = __DIR__ . '/' . $path . '.php';
        if (file_exists($viewPath)) {
            require $viewPath;
            return;
        }

        // Sinon, 404
        http_response_code(404);
        echo "<h1>404 - Page introuvable</h1>";
    }
];
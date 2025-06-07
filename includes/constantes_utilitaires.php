<?php
// Constantes

define('BASE_PATH', realpath(__DIR__ . '/../'));
const NBR_ACTIVITES_A_AFFICHER = 6;
const NOMBRE_MAXIMAL_COMPTES = 3;
define('TIMEOUT', 360 * 60); // 1h d'inactivité soit 20*60 secondes.
const UPLOADS_BASE_DIR = BASE_PATH . '/fichiers';
const PERMISSIONS = 0777;


// Gestion du timezone pour qu'il s'adapte au Bénin

date_default_timezone_set('Africa/Lagos');

// Fonctions utilitaires

function redirigerVersPageErreur($code_erreur, $url)
{
    $_SESSION['previous_url'] = $url;
    $_SESSION['code_erreur'] = $code_erreur;
    header('location:/page_erreur.php');
    exit;
}

function creer_dossiers_upload()
{
    // Création des dossier s'ils n'existent pas

    $upload_annee = UPLOADS_BASE_DIR . "/" . date("Y");
    $upload_mois = $upload_annee . "/" . date("m");
    $upload_dirs = array(UPLOADS_BASE_DIR, $upload_annee, $upload_mois);

    foreach ($upload_dirs as $dir) {
        if (!is_dir($dir)) {
            // Le dossier n'existe pas
            if (!mkdir($dir, PERMISSIONS)) {
                // $erreurs['creation_dossiers'] = "Une erreur s'est produite lors de la création des dossiers de sauvegarde des fichiers. Vérifiez les permissions.";
                echo "<div class=\"alert alert-danger mt-2\">Une erreur s'est produite lors de la création des dossiers de sauvegarde des fichiers. Vérifiez les permissions.</div>";
                die(-1);
            }
        }
    }
    return $upload_mois . '/';
}

function inserer_fichier_dans_bdd($bdd, $chemin_absolu, $infos_fichier, $current_url)
{
    // Enregistrement des métadonnées
    $stmt = $bdd->prepare("INSERT INTO fichiers(chemin_acces, nom_original, date_upload, type_fichier) VALUES (:val1, :val2, :val3, :val4)");

    $stmt->bindParam(':val1', $chemin_absolu);
    $stmt->bindParam(':val2', $infos_fichier['name']); // nom original
    $date_upload = date("Y-m-d"); //  peut être : 2001-03-10
    $stmt->bindParam(':val3', $date_upload);
    $extension = strtolower(pathinfo($infos_fichier['name'], PATHINFO_EXTENSION));
    $stmt->bindParam(':val4', $extension); // extension

    if (!$stmt->execute()) {
        redirigerVersPageErreur(500, $current_url);
    }
}

function inserer_metadonnees_dans_bdd($bdd, $id_participant, $banque, $numero_compte, $id_fichier, $current_url)
{
    $stmt = $bdd->prepare("INSERT INTO informations_bancaires(id_participant, banque, numero_compte, id_rib) VALUES (:val1, :val2, :val3, :val4)");

    $stmt->bindParam(':val1', $id_participant);
    $stmt->bindParam(':val2', $banque);
    $stmt->bindParam(':val3', $numero_compte);
    $stmt->bindParam(':val4', $id_fichier, PDO::PARAM_INT);

    if (!$stmt->execute()) {
        redirigerVersPageErreur(500, $current_url);
    }
}

function modifier_nom($fichier, $matricule_ifu)
{
    $nom_fichier = $fichier; // Ici je récupère "pdf_rib_$i";
    $chiffre_fin = substr($nom_fichier, -1); // Je prends le chiffre de fin
    $nom_fichier = substr($nom_fichier, 0, -1); // Ici je garde "pdf_rib_";

    return $nom_fichier . $matricule_ifu . "_" . $chiffre_fin . ".pdf"; // Je constitue le nom final et je le retourne
}

function valider_valeur_numerique($cle, $conteneur)
{
    // $val est le nom de la valeur dans $conteneur donc cette fonction se base sur le principe que le conteneur est un tableau associatif avec des couples clés/valeurs. Dans les faits elle est construite pour vérifier les différentes valeurs qui seront passées par GET mais gardons cet aspect général avec $conteneur

    // 1- On s'assure que la valeur recherchée est bien dans le conteneur

    if (!isset($conteneur[$cle])) {
        return false;
    }

    // 2 - On s'assure que la valeur si elle est là est un nombre (ici, prenons pour hypothèse que ce nombre quelqu'il soit doit être supérieur à 0)

    $val = intval($conteneur[$cle]);
    if ($val == 0) {
        echo "Je suis ici";
        return false; // La valeur que nous avons reçue est une chaîne de caractère
    }

    // Tout va bien
    return true;
}

function valider_id($methode, $cle, $bdd, $table = 'participants')
{
    // S'assurer que la méthode, et la table sont valides

    $allowed_methods = ['get', 'post'];
    $allowed_tables = ['participants', 'autre_table'];
    // $allowed_columns = ['id_participant', 'id_autre'];

    if (!in_array($table, $allowed_tables) || !in_array($methode, $allowed_methods)) {
        throw new Exception("Table ou méthode non autorisée.");
    }

    // Definition des valeurs globales

    if ($methode == 'get') {
        $const_superglobale = INPUT_GET;
        $superglobale = $_GET;
    } elseif ($methode == 'post') {
        $const_superglobale = INPUT_POST;
        $superglobale = $_POST;
    }

    // type d'id

    switch ($table) {
        case 'participants':
            $type_id = 'id_participant';
            break;
    }

    if (!filter_input($const_superglobale, $cle, FILTER_VALIDATE_INT)) {
        // C'est une chaîne de caractères ou tout simplement la valeur 0 que j'ai reçue
        return false;
    } else {
        $valeur = $superglobale[$cle];

        $stmt = $bdd->prepare("SELECT $type_id FROM $table WHERE $type_id=:valeur_id AND id_user=".$_SESSION['user_id']);
        $stmt->bindParam(':valeur_id', $valeur, PDO::PARAM_INT);
        
        if (!$stmt->execute()) {
            // Une erreur s'est produite lors de la récupération
            redirigerVersPageErreur(500, obtenirURLcourant());
        } else {
            $bool = count($stmt->fetchAll()) == 0 ? false : true;
            return $bool;
        }
    }
}

function obtenirURLcourant()
{
    // Récupération du protocole (http ou https)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    // Récupération du nom de domaine + port si nécessaire
    $host = $_SERVER['HTTP_HOST'];
    // Récupération du chemin URI
    $request_uri = $_SERVER['REQUEST_URI'];
    // URL complète
    $current_url = $protocol . $host . $request_uri;

    return $current_url;
}

// Fonctions liées à la génération de pdfs

function configuration_pdf($pdf, $auteur, $titre)
{
    $pdf->setCreator(PDF_CREATOR);
    $pdf->setAuthor($auteur);
    $pdf->setTitle($titre);
}

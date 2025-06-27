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
    $allowed_tables = ['participants', 'activites', 'autre_table'];
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
        case 'activites':
            $type_id = 'id';
            break;
    }

    if (!filter_input($const_superglobale, $cle, FILTER_VALIDATE_INT)) {
        // C'est une chaîne de caractères ou tout simplement la valeur 0 que j'ai reçue
        return false;
    } else {
        $valeur = $superglobale[$cle];

        $stmt = $bdd->prepare("SELECT $type_id FROM $table WHERE $type_id=:valeur_id AND id_user=" . $_SESSION['user_id']);
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

function obtenirURLcourant($debut_url = false)
{
    // $debut_url : pour savoir si je veux juste le début de l'url sans l'uri ou pas

    // Récupération du protocole (http ou https)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    // Récupération du nom de domaine + port si nécessaire
    $host = $_SERVER['HTTP_HOST'];
    // Récupération du chemin URI
    $request_uri = $_SERVER['REQUEST_URI'];

    // URL complète

    if ($debut_url) {
        $current_url = $protocol . $host;
    } else {
        $current_url = $protocol . $host . $request_uri;
    }

    return $current_url;
}

// Fonctions liées à la génération de pdfs

function configuration_pdf($pdf, $auteur, $titre)
{
    $pdf->setCreator(PDF_CREATOR);
    $pdf->setAuthor($auteur);
    $pdf->setTitle($titre);
}

function determinerPeriode($date_debut, $date_fin)
{
    $fmt = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'Africa/Lagos', IntlDateFormatter::GREGORIAN);
    return "Du " . $fmt->format(new DateTime($date_debut)) . " au " . $fmt->format(new DateTime($date_fin));
}

function couperTexte($texte, $nbr_mots, $nbr_caractères)
{
    $modifie = false;
    $texte = explode(' ', $texte);
    if (count($texte) > $nbr_mots) {
        $texte = array_splice($texte, 0, $nbr_mots);
        $modifie = true;
    } else if (strlen($texte[0]) > $nbr_caractères) {
        // Le texte ne contient pas d'espace mais juste une chaîne de caractères hyper longue
        $texte = substr($texte[0], 0, $nbr_caractères);
        $modifie = true;
    }

    if ($modifie) {
        return implode(' ', $texte) . '...'; // Si le texte a été modifié, on rajoute les trois points de suspension à la fin après avior recollé le tableau
    } else {
        return implode(' ', $texte); // Autrement on ne fait rien
    }
}

// En cours de développement

function afficherSousFormeTableau($elements, $style)
{
    // $elements : les éléments à afficher sous la forme d'un tableau. Je considère que dans $elements est constitué de deux tableaux, un pour l'entête du tableau et un second pour le body

    // $style correspond au style additionnel qu'on pourrait ajouter au tableau
    $head = $elements[0];
    $body = $elements[1];

?>
    <div class="table-responsive text-no-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Choix</th>
                    <?php foreach ($head as $valeur) : ?>
                        <th><?= htmlspecialchars($valeur) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody class="table-border-bottom-1">
                <?php foreach ($body as $ligne) : ?>
                    <tr>
                        <td><input type="checkbox" name="id_activite" <?= 'bonjour' ?> value="<?= 'bonjour' //$activite['id'] 
                                                                                                ?>"></td>
                        <?php foreach ($ligne as $cellule) : ?>
                            td
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>
<?php
}

require_once(__DIR__ . '/../PHPMailer/autoload.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function envoyerLienValidationEmail($lien_verif, $email)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  // Serveur SMTP de Gmail
        $mail->SMTPAuth   = true;
        $mail->Username   = 'gpaiements229@gmail.com';  // adresse Gmail
        $mail->Password   = 'rxop lqyz scjl hiqd';  // Mot de passe d'application
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // Sécuriser la connexion
        $mail->Port       = 587;
        $mail->CharSet = 'utf-8';
        $mail->setFrom('gpaiements229@gmail.com', 'GPaiements');
        $mail->addAddress($email, 'GPaiements'); // L'email de l'utilisateur
        $mail->isHTML(true);

        $mail->Subject = 'Confirmez votre adresse email';
        $mail->Body    = 'Cliquez sur ce lien pour confirmer votre adresse email : <a href="' . $lien_verif . '">Confirmez votre email</a>';

        $mail->SMTPDebug = 0; // Pour désactiver le débug
        $mail->send();
        return true;
    } catch (Exception $e) {
        // die("Erreur : " . $e->getMessage());
        return false;
    }
}

function afficherAlerte($message, $type)
{
    //  $type fait allusion au fait que le message soit un message de succès ou d'erreur
    // $message est tout simplement le message
?>
    <div class="alert alert-<?= $type ?> alert-dismissible text-center">
        <?= $message ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
<?php
}

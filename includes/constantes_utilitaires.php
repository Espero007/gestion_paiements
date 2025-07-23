<?php
// Constantes

define('BASE_PATH', realpath(__DIR__ . '/../'));
const NBR_ACTIVITES_A_AFFICHER = 6;
const NOMBRE_MAXIMAL_COMPTES = 3;
define('TIMEOUT', 1 * 86400); // 1 journée d'inactivité soit 86400
// define('TIMEOUT', 30); // 30 secondes d'inactivité
$nom_dossier_upload = 'fichiers';
define('UPLOADS_BASE_DIR', BASE_PATH . '/' . $nom_dossier_upload);
const PERMISSIONS = 0777;
$dossier_exports_temp =  BASE_PATH . '/gestion_activites/scripts_generation/exports';
if (!is_dir($dossier_exports_temp)) {
    mkdir($dossier_exports_temp, 0777, true); // crée récursivement avec droits suffisants
}


// Gestion du timezone pour qu'il s'adapte au Bénin

date_default_timezone_set('Africa/Lagos');

// Quelques inclusions

require_once(__DIR__ . '/../tcpdf/tcpdf.php');
require_once __DIR__ . '/../vendor/autoload.php';

use setasign\Fpdi\Tcpdf\Fpdi;

// Fonctions utilitaires

function redirigerVersPageErreur($code_erreur = 404, $url = null)
{
    if ($url) {
        $_SESSION['previous_url'] = $url;
    } else {
        // On a pas inqué l'url donc par défaut c'est l'url précédent qui est utilisé
    }
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

function valider_id($methode, $cle, $bdd, $table = 'participants', $valeur_id = false, $chiffre = true)
{
    global $bdd;
    // $valeur_id nous permet de valider un id qu'on passe directement à la fonction sans passer par les superglobales

    $allowed_tables = ['participants', 'activites', 'participations_participant', 'participations_activites', 'participations', 'autre_table'];
    // $allowed_columns = ['id_participant', 'id_autre'];

    if (!in_array($table, $allowed_tables)) {
        throw new Exception("Table non autorisée.");
    }

    // type d'id

    switch ($table) {
        case 'participants':
            $type_id = 'id_participant';
            break;
        case 'activites':
            $type_id = 'id';
            break;
        case 'participations_participant':
            $type_id = 'id_participant';
            $type_id2 = $type_id;
            break;
        case 'participations_activites':
            $type_id = 'id_activite';
            $type_id2 = 'id';
            break;
        case 'participations':
            $type_id = 'id';
        default:
    }

    if (!$valeur_id) {
        // La fonction ne travaille pas directement sur la valeur de l'id mais sur les superglobables
        // S'assurer que la méthode, et la table sont valides
        $allowed_methods = ['get', 'post'];
        if (!in_array($methode, $allowed_methods)) {
            throw new Exception("Méthode non autorisée.");
        }

        // Definition des valeurs globales
        if ($methode == 'get') {
            $const_superglobale = INPUT_GET;
            $superglobale = $_GET;
        } elseif ($methode == 'post') {
            $const_superglobale = INPUT_POST;
            $superglobale = $_POST;
        }

        if (!$chiffre) {
            // L'id n'est pas chiffré
            if (!filter_input($const_superglobale, $cle, FILTER_VALIDATE_INT)) {
                // C'est une chaîne de caractères ou tout simplement la valeur 0 que j'ai reçue
                return false;
            } else {
                $valeur = $superglobale[$cle];
            }
        } else {
            // L'id est chiffré
            $id = dechiffrer($superglobale[$cle]);
            if (!filter_var($id, FILTER_VALIDATE_INT)) {
                return false;
            } else {
                $valeur = $id;
            }
        }
    } else {
        if ($chiffre) {
            $valeur = dechiffrer($valeur_id);
        } else {
            $valeur = $valeur_id;
        }
    }

    if (in_array($table, ['participants', 'activites'])) {
        $stmt = $bdd->prepare("SELECT $type_id FROM $table WHERE $type_id=:valeur_id AND id_user=" . $_SESSION['user_id']);
    } elseif (str_contains($table, 'participations_')) {
        $table_base = 'participations';
        $table_additionnelle = str_replace('participations_', '', $table);
        $stmt = $bdd->prepare("SELECT $type_id FROM participations pa INNER JOIN $table_additionnelle t ON pa.$type_id=t.$type_id2 WHERE t.$type_id2=:valeur_id AND id_user=" . $_SESSION['user_id']);
    } elseif ($table == 'participations') {
        $stmt = $bdd->prepare("
        SELECT p.id_participant
        FROM participants p
        INNER JOIN participations p1 ON
        p.id_participant = p1.id_participant
        WHERE p.id_user=" . $_SESSION['user_id'] . " AND 
        p.id_participant IN
        (SELECT id_participant
        FROM participants WHERE $type_id=:valeur_id)
        ");
    }

    $stmt->bindParam(':valeur_id', $valeur, PDO::PARAM_INT);

    if (!$stmt->execute()) {
        // Une erreur s'est produite lors de la récupération
        redirigerVersPageErreur(500, obtenirURLcourant());
    } else {
        $bool = count($stmt->fetchAll()) == 0 ? false : true;
        return $bool;
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

/** Fonction mise en place par Ifè et Tobi (on respecte les droits d'auteurs ici !) */
function formaterPeriode($dateDebut, $dateFin)
{
    $debut = new DateTime($dateDebut);
    $fin   = new DateTime($dateFin);

    $jourDebut = $debut->format('j');
    $jourFin   = $fin->format('j');

    $formatterMois = new IntlDateFormatter("fr_FR", IntlDateFormatter::NONE, IntlDateFormatter::NONE, null, null, 'MMMM');

    $moisDebut = $formatterMois->format($debut);
    $moisFin   = $formatterMois->format($fin);

    $anneeDebut = $debut->format('Y');
    $anneeFin   = $fin->format('Y');

    // Période dans le même mois et année
    if ($moisDebut === $moisFin && $anneeDebut === $anneeFin) {
        return "$jourDebut au $jourFin $moisFin $anneeFin";
    }
    // Même année mais mois différents
    elseif ($anneeDebut === $anneeFin) {
        return "$jourDebut $moisDebut au $jourFin $moisFin $anneeFin";
    }
    // Mois et années différents
    else {
        return "$jourDebut $moisDebut $anneeDebut au $jourFin $moisFin $anneeFin";
    }
}
/** Fonction mise en place par Ifè et Tobi */

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

function afficherSousFormeTableau($elements, $style1, $style2, $choix = true, $actions = true, $cbxs = null)
{
    // $elements : les éléments à afficher sous la forme d'un tableau. Je considère que dans $elements est constitué de deux tableaux, un pour l'entête du tableau et un second pour le body
    // $style correspond au style additionnel qu'on pourrait ajouter au tableau
    // dans actions je dois avoir l'intitulé de l'action et le lien qui permet de la réaliser dans cet ordre donc action devrait ressembler un peu à
    // [0][0]['intitule'=>'Gérer', 'lien'=>'...']
    //    [1]['intitule'=>'Gérer', 'lien'=>'...']
    // Pour la dernière action de la liste ajouter dans le tableau associatif un booléen avec comme clé 'dernier'
    // On peut ajouter du style aussi si on le souhaite dans une valeur dont la clé sera 'style'

    // Okay, si j'ai bien compris la logique que je suivais, si $cbxs a une valeur, à l'index 0 on aura le nom que les checkbox doivent prendre dans la post et dans l'index 2 on a les ids pour chaque chechbox

    $head = $elements[0];
    $body = $elements[1];
    $actions = $actions ? $elements[2] : $actions;
    $index = 0; // variable d'incrémentation

?>
    <div class="<?= $style1 ?>">
        <table class="table <?= $style2 ?>" id="dataTable" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <?php if ($choix) : ?>
                        <th>Choix</th>
                    <?php endif; ?>
                    <?php foreach ($head as $valeur) : ?>
                        <th><?= htmlspecialchars($valeur) ?></th>
                    <?php endforeach; ?>

                    <?php if ($actions) : ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <?php if ($choix) : ?>
                        <th>Choix</th>
                    <?php endif; ?>
                    <?php foreach ($head as $valeur) : ?>
                        <th><?= htmlspecialchars($valeur) ?></th>
                    <?php endforeach; ?>
                    <?php if ($actions) : ?>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </tfoot>
            <tbody class="table-border-bottom-1">
                <?php foreach ($body as $ligne) : ?>
                    <tr>
                        <?php if ($choix) : ?>
                            <td><input type="checkbox" name="<?= $cbxs ? $cbxs[0] : 'bref' ?>[]" value="<?= $cbxs ? $cbxs[1][$index] : 'bref' ?>"></td>
                        <?php endif; ?>
                        <?php foreach ($ligne as $cellule) : ?>
                            <td><?= $cellule != null ? htmlspecialchars($cellule) : '-' ?></td>
                        <?php endforeach; ?>
                        <?php if ($actions) : ?>
                            <td>
                                <div class="btn-group">
                                    <?php $action = $actions[$index][0] ?>
                                    <a href="<?= $action['lien'] ?>" class="btn btn-primary"><?= $action['intitule'] ?></a>
                                    <button type="button" class="btn btn-primary btn btn-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"></button>
                                    <ul class="dropdown-menu">
                                        <?php for ($i = 1; $i < count($actions[$index]); $i++) : ?>
                                            <?php $action = $actions[$index][$i] ?>
                                            <?php if (isset($action['dernier']) && count($actions[$index]) > 2) : ?>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                            <?php endif; ?>
                                            <li>
                                                <a href="<?= $action['lien'] ?>" class="dropdown-item custom-dropdown-item<?= isset($action['style']) ? ' ' . $action['style'] : '' ?>"><?= $action['intitule'] ?></a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </div>
                            </td>
                        <?php endif; ?>
                        <?php $index++ ?>
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

function envoyerLienValidationEmail($lien_verif, $email, $nom, $prenom, $type_mail)
{
    // si $type_mail est à 0, le mail est pour l'inscription (on veut confirmer le mail lors de l'inscription)
    // si c'est à 1, le mail est pour confirmer son email (on veut confirmer le mail pendant que l'utilisateur essaye de changer son email)
    // si c'est à 2, le mail est pour réinitialiser le mot de passe
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

        if ($type_mail == 0) {
            $mail->Subject = 'Activation de votre compte GPaiements';
            $mail->Body    = '
            <p>Cher(e) ' . $nom . ' ' . $prenom . ',</p>
            <p>Merci pour votre inscription sur GPaiements, la plateforme de gestion de vos activités. Nous sommes heureux de vous savoir à bord</p>
            <p>A présent, veuillez cliquez sur le lien ci-dessous pour activer votre compte et entamer l\'aventure !</p>
            <p style="text-align:center;"><a href="' . $lien_verif . '" style="text-decoration : none; color : #4e73df; font-size : 1.2rem;">Activer mon compte GPaiements</a></p>
            <p>Très chaleureusement,<br>L\'équipe de GPaiements</p>';
        } elseif ($type_mail == 1) {
            $mail->Subject = 'Confirmation de votre adresse email';
            $mail->Body = '
            <p>Plus q\'un clic pour actualiser votre adresse mail</p>
            <p><a href="' . $lien_verif . '" style="text-decoration : none; color : #4e73df;">Confirmer mon adresse</a></p>
            <p>Très chaleureusement,<br>L\'équipe de GPaiements</p>';
        } elseif ($type_mail == 2) {
            $mail->Subject = 'Réinitialisation du mot de passe de votre compte GPaiements';
            $mail->Body    = '
            <p>Cher(e) ' . $nom . ' ' . $prenom . ',</p>
            <p>Une fois de plus merci d\'avoir entamé l\'aventure à nos côtés.</p>
            <p>Cliquez sur le lien ci-dessous pour réinitialiser votre mot de passe.</p>
            <p style="text-align:center;"><a href="' . $lien_verif . '" style="text-decoration : none; color : #4e73df; font-size : 1.2rem;">Réinitialiser mon mot de passe</a></p>
            <p>Très chaleureusement,<br>L\'équipe de GPaiements</p>';
        }


        $mail->SMTPDebug = 0; // Pour désactiver le débug
        $mail->send();
        return true;
    } catch (Exception $e) {
        // die("Erreur : " . $e->getMessage());
        return false;
    }
}

function afficherAlerte($message, $type, $session = false, $dismissible = true)
{
    //  $type fait allusion au fait que le message soit un message de succès ou d'erreur
    // $message est tout simplement le message
    // $session est pour savoir si la variable contenant le message est dans la session ou pas

?>
    <div class="alert alert-<?= $type ?><?= $dismissible ? ' alert-dismissible' : '' ?> text-center">
        <?php if (!$session) : ?>
            <?= $message ?>
        <?php else: ?>
            <?= $_SESSION[$message] ?>
            <?php unset($_SESSION[$message]) ?>
        <?php endif; ?>
        <?php if ($dismissible) : ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        <?php endif; ?>
    </div>
<?php
}

function traiterCheminAcces($chemin, $basename = false)
{
    // Cette fonction est là pour m'aider à retrouver le lien en relatif à partir du lien en absolu. Elle va donc tout simplement couper le chemin d'accès à partir de 'fichiers' et me donner le reste du chemin d'accès en relatif

    if (!$basename) {
        $motCle = $GLOBALS['nom_dossier_upload'];
        return strstr($chemin, $motCle);
    } else {
        return basename($chemin);
    }
}

function afficherTexteDansDeuxBlocs($pdf, $bloc_gauche, $bloc_droite, $font, $font_size, $sauts_ligne, $bloc_gauche_align = 'C', $bloc_gauche_style = '', $bloc_droite_align = 'C', $bloc_droite_style = '')
{
    // $bloc_gauche_style pour savoir si je veux le bloc souligné, en gras, ce genre de choses
    $pdf->setFont($font, $bloc_gauche_style, $font_size);

    $largeurPage = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
    // Largeur d'un bloc
    $largeurBloc = $largeurPage / 2;
    // Sauvegarder la position Y
    $y = $pdf->GetY();
    // Sauvegarder la position de x à droite
    $x_droite = $pdf->getMargins()['left'] + $largeurBloc;
    $pdf->setXY($pdf->getMargins()['left'], $y);
    $pdf->MultiCell($largeurBloc, 5, $bloc_gauche, 0, $bloc_gauche_align);

    $pdf->setFont($font, $bloc_droite_style, $font_size);

    //Bloc de droite (sur la même ligne que le bloc de gauche)
    $pdf->setXY($pdf->getMargins()['left'] + $largeurBloc, $y);
    $pdf->MultiCell($largeurBloc, 5, $bloc_droite, 0, $bloc_droite_align);

    $pdf->Ln($sauts_ligne);
}

function supprimerAccents($chaine)
{
    if (class_exists('Transliterator')) {
        $transliterator = Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC');
        return $transliterator->transliterate($chaine);
    } else {
        return $chaine;
    }
}

function genererHeader($pdf, $type_document, $informations, $id_activite)
{
    global $bdd;

    /** Commentaires explicatifs */

    // $type_document est la variable qui doit nous dire si le header est pour un ordre de virement, l'attestation collective, etc...
    // Les différents types possibles sont 'ordre_virement', 'note_service', 'attestation_collective' (si un autre type que ceux listés là est utilisé à l'usage de la fonction, le comportement de celle ci est indéterminé)

    /** Gestion des informations à adapter selon le type du document */

    // $informations est un tableau associatif qui doit contenir comme données générales le titre de l'activité(['titre'=>'valeur_titre'])

    // Note de service : RAS, appeler la fonction en lui donnant le titre de l'activité suffira

    // Attestation collective : pareil que pour la note de service

    // Etat paiement : il faut dans le tableau indiquer le type de l'activité pour que le header puisse s'adapter comme celà se doit. (bref, en cours de développement)


    // Ordre de virement : il faut ici la banque en plus, toujours selon le format ['banque'=>]

    /** Fin Commentaires explicatifs */

    /** Actions préliminaires */

    // On récupère les informations associées utiles de l'activité
    $stmt = $bdd->query('SELECT centre, date_debut, date_fin FROM activites WHERE id=' . $id_activite);
    $resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $informations_activite = $resultat[0];

    $entete_editee = false;
    $stmt = $bdd->query('SELECT * FROM informations_entete WHERE id_activite=' . $id_activite);
    if ($stmt->rowCount() != 0) {
        $informations_entete = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $informations_entete = $informations_entete[0];
        $entete_editee = true;
    }

    // Les sous titres du header
    $sous_titres = [
        'ordre_virement' => 'des indemnités et frais d\' entretien accordés aux membres de la commission chargée de',
        'note_service' => 'portant constitution des membres de la commission chargée de',
        'etat_paiement_1' => 'des indemnités et frais d\'entretien accordés aux membres de la commission chargée de',
        'etat_paiement_2' => 'des indemnités et frais d\'entretien accordés aux membres d\'encadrement dans le cadre',
        'etat_paiement_3' => 'indemnités et frais d\'entretien accordés aux membres de la commission chargee de la correction des examens de',
        'liste_ribs' => 'Dans le cadre de la'
    ];

    // Formattage de la date en français

    $formatter = new IntlDateFormatter(
        'fr_FR',
        IntlDateFormatter::FULL,
        IntlDateFormatter::NONE,
        'Africa/Lagos',
        IntlDateFormatter::GREGORIAN
    );

    // Chargement de la police du pdf
    $pdf->setFont('trebuc', '', 10);
    $pdf->setY(8); // on descend de 8mm du haut avant de débuter le dessin du header

    // Calcul de largeur totale disponible entre les marges
    $largeurPage = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
    $largeurBloc = $largeurPage / 2; // Largeur d'un bloc
    $y = $pdf->GetY(); // sauvegarde de la position Y
    $x = $pdf->getMargins()['left'] + $largeurBloc; // sauvegarde de la position de x à droite

    // Gestion du bloc de gauche du header
    $bloc_gauche = !$entete_editee ? strtoupper("REPUBLIQUE DU BENIN\n*-*-*-*-*\nMINISTERE DE L'ENSEIGNEMENT SUPERIEUR ET SECONDAIRE\n*-*-*-*-*\nDIRECTION DES ............\n*-*-*-*-*\nSERVICE ............") : mb_strtoupper($informations_entete['ligne1'] . "\n*-*-*-*-*\n" . $informations_entete['ligne2'] . "\n*-*-*-*-*\n" . $informations_entete['ligne3'] . "\n*-*-*-*-*\n" . $informations_entete['ligne4'], 'UTF-8');
    $pdf->setXY($pdf->getMargins()['left'], $y);
    $pdf->MultiCell($largeurBloc, 5, $bloc_gauche, 0, 'C');

    // Gestion du bloc de droite (sur la même ligne que le bloc de gauche)
    $pdf->setXY($x, $y); // Déplacement du curseur à la bonne position

    // Ligne 1 : date
    $ligne1 = !$entete_editee ? strtoupper("Cotonou, le " . $formatter->format(new DateTime())) : mb_strtoupper($informations_entete['ville'] . ', le ' . $informations_entete['date1'], 'UTF-8');
    $pdf->Cell(0, 5, $ligne1, 0, 1, 'C');
    $pdf->Ln(5);

    // Ligne 2 : Titre du document
    if ($type_document == 'ordre_virement') {
        $ligne2 = mb_strtoupper('ordre de virement ' . $informations['banque'], 'UTF-8');
    } elseif ($type_document == 'note_service') {
        $ligne2 = 'NOTE DE SERVICE';
    } elseif ($type_document == 'attestation_collective') {
        $ligne2 = 'ATTESTATION COLLECTIVE DE TRAVAIL';
    } elseif (str_contains($type_document, 'etat_paiement')) {
        $ligne2 = 'ETAT DE PAIEMENT N°';
    } elseif ($type_document == 'liste_ribs') {
        $ligne2 = 'LISTE DES RIBS';
    }

    $pdf->setFont('trebucbd', '', '11');
    $pdf->setX($x);
    $pdf->Cell(0, 5, $ligne2, 0, 1, 'C');
    $pdf->Ln(5);

    // Ligne 3 : Sous-titre du document
    $ligne3 = mb_strtoupper($sous_titres[$type_document] . ' ' . $informations['titre'] . ($type_document != 'etat_paiement_3' ? '' : ', ' . $informations_entete['ligne5']), 'UTF-8');
    $pdf->setFont('trebuc', '', '10');
    $pdf->setX($x);
    $pdf->MultiCell($largeurBloc, 5, $ligne3, 0, 'C');

    if ($type_document == 'etat_paiement_2' || $type_document == 'etat_paiement_3') {
        $pdf->Ln(5);
        // Ligne 4
        $debut_ligne_4 = '';
        $ligne4 = '';
        if ($type_document == 'etat_paiement_2') {
            $debut_ligne_4 = 'Journée';
            $ligne4 = !$entete_editee ? $formatter->format(new DateTime()) : $informations_entete['date1'];
        } elseif ($type_document == 'etat_paiement_3') {
            $debut_ligne_4 = 'Période';
            $ligne4 = mb_strtoupper('du ' . formaterPeriode($informations_activite['date_debut'], $informations_activite['date_fin']), 'UTF-8');
        }

        $pdf->setFont('trebucbd', 'U', '10');
        $pdf->setX($x);
        $pdf->Write(0, $debut_ligne_4);
        $pdf->setFont('trebucbd', '', '10');
        $pdf->Write(0, ' : ' . mb_strtoupper($ligne4, 'UTF-8'));
        $pdf->Ln(8);
        $pdf->setFont('trebucbd', 'U', '10');
        $pdf->setX($x);
        $pdf->Write(0, 'CENTRE');
        $centre = $informations_activite['centre'];
        $pdf->setFont('trebucbd', '', '10');
        $pdf->Write(0, ' : ' . mb_strtoupper($centre, 'UTF-8'));
    }
}

function listeBanques($id_activite)
{
    // Cette fonction doit me permettre d'avoir la liste des banques des participants associées à une activité
    global $bdd;

    $stmt = $bdd->query(
        'SELECT DISTINCT banque
        FROM participations pa
        INNER JOIN participants p ON pa.id_participant = p.id_participant
        INNER JOIN informations_bancaires ib ON pa.id_compte_bancaire = ib.id
        WHERE p.id_user=' . $_SESSION['user_id'] . ' AND pa.id_activite=' . $id_activite
    );
    $banques = $stmt->fetchAll(PDO::FETCH_NUM);
    foreach ($banques as $banque) {
        $liste_banques[] = $banque[0];
    }
    return $liste_banques;
}

function montantParticipant($id_participant, $id_activite)
{
    // ELle permettra de calculer le montant associé à un participant (dans le contexte d'une activité à laquelle des participants ont été associés bien sûr) mais son réel intérêt est de centraliser le code de calcul à un seul endroit pour qu'on puisse le modifier plus facilement

    global $bdd;

    $stmt = $bdd->query("
    SELECT 
    a.type_activite,
    t.indemnite_forfaitaire,
    a.taux_journalier,
    a.taux_taches,
    a.frais_deplacement_journalier as fdj,
    pa.nombre_jours,
    pa.nombre_taches
    FROM participations pa
    INNER JOIN titres t ON pa.id_titre = t.id_titre
    INNER JOIN activites a ON pa.id_activite=a.id
    WHERE pa.id_activite=$id_activite AND pa.id_participant =$id_participant 
    ");

    $infos = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    $montant = 0;
    if ($infos['type_activite'] == 3) {
        $montant = $infos['taux_taches'] * $infos['nombre_taches'] + $infos['fdj'] * $infos['nombre_jours'] + $infos['indemnite_forfaitaire'];
    } else {
        $montant = $infos['taux_journalier'] * $infos['nombre_jours'] + $infos['indemnite_forfaitaire'];
    }
    return $montant;
}

function listeParticipantsBanque($id_activite, $banque)
{
    // Nous renvoie la liste des id des participants ayant la banque indiquée (dans le contexte d'une activité à laquelle on a associé des participants bien-sûr)
    global $bdd;
    $stmt = $bdd->prepare("
    SELECT 
    pa.id_participant as id
    FROM participations pa    
    INNER JOIN informations_bancaires ib ON pa.id_participant=ib.id_participant
    WHERE pa.id_activite=$id_activite AND ib.banque =:banque 
    ");
    $stmt->bindParam('banque', $banque);
    $stmt->execute();
    $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultats as $participant) {
        $liste_participants[] = $participant['id'];
    }

    return $liste_participants;
}

function totalBanque($id_activite, $banque)
{
    // Sert à calculer le total du montant associé à une banque dans le contexte d'une activité à laquelle des participants ont été associés
    global $bdd;

    // Voici le process :
    // 1- Je récupère la liste des participants qui sont dans cette banque
    // 2- Je calcule le montant de chacun de ces participants
    // 3- Je fais le cumul

    $liste_participants = listeParticipantsBanque($id_activite, $banque);
    $total = 0;
    foreach ($liste_participants as $id_participant) {
        $total += montantParticipant($id_participant, $id_activite);
    }

    return $total;
}

function extrairePrefixe($nomfichier)
{
    // $nom = basename($chemin_fichier);
    if (preg_match('/^(.*_)\d+\.pdf$/', $nomfichier, $matches)) {
        return $matches[1];
    }
    return false;
}

function extraireSuffixe($nomfichier)
{
    if (preg_match('/_(\d+)\.pdf$/', $nomfichier, $matches)) {
        return $matches[1];
    }
    return false;
}

function arrangerRibs($id_participant)
{
    global $bdd;
    // Je récupère les chemins d'accès vers les ribs qu'il lui reste
    $stmt = $bdd->query('
    SELECT id_fichier, chemin_acces
    FROM fichiers f
    INNER JOIN informations_bancaires ib ON ib.id_rib = f.id_fichier
    INNER JOIN participants p ON ib.id_participant = p.id_participant
    WHERE p.id_participant=' . $id_participant);

    $donnees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($donnees as $index => $donnee) {
        $donnees[$index]['suffixe'] = extraireSuffixe(basename($donnee['chemin_acces']));
        $donnees[$index]['prefixe'] = extrairePrefixe(basename($donnee['chemin_acces']));
        $chiffres[] = $donnees[$index]['suffixe'];
    }

    $nbr_valeurs = count($chiffres);

    for ($i = 1; $i <= $nbr_valeurs; $i++) {
        $chiffre_min = min($chiffres);
        foreach ($donnees as $donnee) {
            $suffixe = $donnee['suffixe'];
            if ($chiffre_min == $suffixe) {
                // On modifie le nom du fichier et on actualise la bdd
                $nouveauNom = dirname($donnee['chemin_acces']) . '/' . $donnee['prefixe'] . $i . '.pdf';
                if (rename($donnee['chemin_acces'], $nouveauNom)) {

                    $stmt = $bdd->prepare('UPDATE fichiers SET chemin_acces=:chemin WHERE id_fichier=' . $donnee['id_fichier']);
                    $stmt->execute(['chemin' => $nouveauNom]);

                    // Recherche l'index du minimum trouvé et le retire
                    $index = array_search($chiffre_min, $chiffres);
                    if ($index !== false) {
                        unset($chiffres[$index]);
                    }
                    // Réindexer le tableau
                    $chiffres = array_values($chiffres);
                }
            }
        }
    }
}

// fonction pour génerer les urls

function generateUrl(string $path, array $params = []): string
{
    $url = '/' . trim($path, '/'); // Ajoute un slash initial et nettoie le chemin

    if (!empty($params)) {
        if ($path === 'participants/gerer' && isset($params['id'])) {
            // Cas spécifique où l'ID doit être chiffré et encodé
            if (function_exists('chiffrer')) {
                $token = chiffrer($params['id']);
                if ($token !== false) { // Vérifie que le chiffrement a réussi
                    $url .= '/' . urlencode($token);
                } else {
                    // Fallback ou gestion d'erreur si le chiffrement échoue
                    trigger_error('Échec du chiffrement de l\'ID pour l\'URL.', E_USER_WARNING);
                    $url .= '/' . $params['id']; // Fallback non sécurisé pour débogage
                }
            } else {
                trigger_error('La fonction chiffrer n\'est pas disponible. Vérifiez l\'inclusion de Crypto.php.', E_USER_ERROR);
                $url .= '/' . $params['id']; // Fallback non sécurisé
            }
        } else {
            // Pour d'autres types de paramètres (query strings, ex: ?page=2&tri=nom)
            $queryString = http_build_query($params);
            if (!empty($queryString)) {
                $url .= '?' . $queryString;
            }
        }
    }

    return $url;
}
// Fonctions pour la génération des participants de façon aléatoire

function genererCSVDemo($chemin_csv, $nbr_acteurs)
{

    function genererIFU()
    {
        // Format IFU : 13 chiffres (ex: 3202101234567)
        $prefix = rand(100, 999);
        $annee = rand(2000, 2023);
        $suffix = rand(100000, 999999);
        return $prefix . $annee . $suffix;
    }

    function genererDateNaissance()
    {
        $timestamp = strtotime('-' . rand(18, 60) . ' years');
        return date('Y/m/d', $timestamp);
    }

    // Données de base
    $noms = [
        "Ahouansou",
        "Kouassi",
        "Soglo",
        "Zinsou",
        "Agbangla",
        "Chabi",
        "Gnonlonfoun",
        "Azon",
        "Dossou",
        "Houngbédji",
        "Agbo",
        "Kakaï",
        "Yabi",
        "Toko",
        "Lawani",
        "Boko",
        "Allagbé",
        "Assogba",
        "Bio",
        "Codjia"
    ];

    $prenoms_masculins = [
        "Sébastien",
        "Yves",
        "Romuald",
        "Blaise",
        "Barnabé",
        "Marcel",
        "Ignace",
        "Ulrich",
        "Dona",
        "Pascal",
        "Andréas",
        "Komi",
        "Sylvestre",
        "Ismaël",
        "Ghislain"
    ];

    $prenoms_feminins = [
        "Afi",
        "Adjovi",
        "Sophie",
        "Clarisse",
        "Edith",
        "Brigitte",
        "Reine",
        "Arlette",
        "Chantal",
        "Séraphine",
        "Tatiana",
        "Nadine",
        "Solange",
        "Prisca",
        "Eliane"
    ];

    $lieux = [
        "Cotonou",
        "Porto-Novo",
        "Parakou",
        "Abomey",
        "Bohicon",
        "Natitingou",
        "Djougou",
        "Ouidah",
        "Lokossa",
        "Kandi",
        "Malanville",
        "Savalou",
        "Covè",
        "Comè",
        "Sakété"
    ];

    $diplomes = [
        "CEP",
        "BEPC",
        "BAC",
        "Licence en Informatique",
        "Licence en Droit",
        "Licence en Économie",
        "Master en Finance",
        "Master en Agronomie",
        "Doctorat en Médecine",
        "DUT en Génie Civil",
        "BTS en Gestion Commerciale",
        "Certificat en Programmation",
        "Diplôme en Marketing Digital",
        "Licence en Mathématiques",
        "Master en Relations Internationales"
    ];

    // Générer identités
    $csvFile = fopen($chemin_csv, "w");
    fputcsv($csvFile, ["Nom", "Prénoms", "Date de Naissance", "Lieu de Naissance", "IFU", "Diplôme"]);

    for ($i = 0; $i < $nbr_acteurs; $i++) {
        $sexe = rand(0, 1) ? 'M' : 'F';
        $nom = $noms[array_rand($noms)];
        $sourcePrenoms = $sexe === 'M' ? $prenoms_masculins : $prenoms_feminins;

        // Choisir 1 ou 2 prénoms aléatoirement sans doublons
        shuffle($sourcePrenoms);
        $prenoms = implode(" ", array_slice($sourcePrenoms, 0, rand(1, 2)));

        $dateNaissance = genererDateNaissance();
        $lieuNaissance = $lieux[array_rand($lieux)];
        $ifu = genererIFU();
        $diplome = $diplomes[array_rand($diplomes)];

        fputcsv($csvFile, [$nom, $prenoms, $dateNaissance, $lieuNaissance, $ifu, $diplome]);
    }
    fclose($csvFile);
}

function creerActivitesDemo()
{
    global $bdd;
    $liste_activites = '';
    /** Création en premier lieu de 3 activités de types distincts chacun */

    // Requête
    $sql = "INSERT INTO activites(type_activite, id_user, nom, description, date_debut, date_fin, centre, premier_responsable, titre_responsable, organisateur, titre_organisateur, financier, titre_financier, timbre, taux_journalier, taux_taches, frais_deplacement_journalier, reference) VALUES (:type_activite,{$_SESSION['user_id']},:nom, 'C\'est une activité de démonstration pour tester les diverses fonctionnalités de la plateforme', '2025-01-01', '2025-12-31', :centre, 'AKANDO Espéro Eléazar Ogoluwa', 'Ingénrieur Télécoms', 'COMLAN Ifè', 'Ingénieur Télécoms', 'MONSI Olowun-Tobi', 'Ingénieur Réseaux', :timbre_activite, :taux_journalier, :taux_taches, :frais_deplacement_journalier, :reference)";
    $stmt = $bdd->prepare($sql);

    // Activité de type 1
    $stmt->execute([
        'type_activite' => 1,
        'nom' => 'Activité de démo 1',
        'centre' => 'Parakou',
        'timbre_activite' => '/DEG/MAS/SAFM/SDDC/SEL/SEMC/SIS/SD',
        'taux_journalier' => 1075,
        'taux_taches' => null,
        'frais_deplacement_journalier' => null,
        'reference' => 'NS N° 0012/MAS/DC/SGM/DPAF/DSI/DEC/SAFM/SEMC/SIS/SA DU 29 DECEMBRE 2023'
    ]);
    $ids_activites[] = $bdd->lastInsertId();
    $titres = ['Sécrétaire', 'Directeur', 'Gardien', 'Recteur'];
    $sql = 'INSERT INTO titres(id_activite, nom, indemnite_forfaitaire) VALUES (:id_activite, :nom, :indemnite_forfaitaire)';
    $stmt2 = $bdd->prepare($sql);
    foreach ($titres as $titre) {
        $stmt2->execute([
            'id_activite' => $ids_activites[0],
            'nom' => $titre,
            'indemnite_forfaitaire' => null
        ]);
        $titres_activites[0][] = $bdd->lastInsertId();
    }

    $liste_activites .= $ids_activites[count($ids_activites) - 1];


    // Activité de type 2
    $stmt->execute([
        'type_activite' => 2,
        'nom' => 'Activité de démo 2',
        'centre' => 'Cotonou',
        'timbre_activite' => '/DEG/MAS/UAC/GIT-EPAC/SEL/SEMC/SIS/SD',
        'taux_journalier' => 1075,
        'taux_taches' => null,
        'frais_deplacement_journalier' => null,
        'reference' => 'AS N° 0012/MAS/DC/SGM/DPAF/DSI/DEC/SAFM/SEMC/SIS/SA DU 31 DECEMBRE 2025'
    ]);
    $ids_activites[] = $bdd->lastInsertId();
    $titres = ['Journaliste', 'Commentateur', 'Joueur', 'Virgile'];
    $forfaits = [500, 500, 1000, 0];
    foreach (array_combine($titres, $forfaits) as $titre => $forfait) {
        $stmt2->execute([
            'id_activite' => $ids_activites[1],
            'nom' => $titre,
            'indemnite_forfaitaire' => $forfait
        ]);
        $titres_activites[1][] = $bdd->lastInsertId();
    }

    $liste_activites .= ',' . $ids_activites[count($ids_activites) - 1];

    // Activité de type 3
    $stmt->execute([
        'type_activite' => 3,
        'nom' => 'Activité de démo 3',
        'centre' => 'Malanville',
        'timbre_activite' => '/DEG/MAS/SAFM/SDDC/SEL/SEMC/SIS/SD/ACTIVITE3',
        'taux_journalier' => null,
        'taux_taches' => 1000,
        'frais_deplacement_journalier' => 500,
        'reference' => 'NS N° 0012/MAS/DC/SGM/DPAF/DSI/DEC/SAFM/SEMC/SIS/SA DU 01 JANVIER 2024'
    ]);
    $ids_activites[] = $bdd->lastInsertId();
    $titres = ['Chanteur', 'Producteur', 'Guitariste'];
    $forfaits = [5000, 2500, 2000];
    foreach (array_combine($titres, $forfaits) as $titre => $forfait) {
        $stmt2->execute([
            'id_activite' => $ids_activites[2],
            'nom' => $titre,
            'indemnite_forfaitaire' => $forfait
        ]);
        $titres_activites[2][] = $bdd->lastInsertId();
    }

    $liste_activites .= ',' . $ids_activites[count($ids_activites) - 1];

    return [
        'ids_activites' => $ids_activites,
        'titres_activites' => $titres_activites,
        'liste_activites' => $liste_activites
    ];
}

function ConfigurerInformationsDemo()
{
    global $bdd;

    // 1 ère étape : Génération des données associées aux 150 participants dans un fichier csv
    $chemin_csv = __DIR__ . '/../parametres/donnees.csv';
    $nbr_acteurs = 150;
    genererCSVDemo($chemin_csv, $nbr_acteurs);

    // 2ème étape : je crée trois (03) activités l'un de type 1, le second de type 2 et le dernier de type 3
    $infos_activites = creerActivitesDemo();

    // 3ème étape : je crée les participants avec des nombres de comptes bancaires aléatoires (insertions en bdd comprises)
    $banques = ['BOA', 'UBA', 'BIIC', 'ECOBANK', 'BSIC', 'ORABANK', 'NSIA', 'Coris Bank', 'Atlantique', 'CCP', 'SGB'];

    if (($handle = fopen($chemin_csv, 'r')) !== false) {
        // Lire les entêtes
        $entetes = fgetcsv($handle);
    }

    // Lire chaque ligne (les informations de chaque acteur généré de façon aléatoire)
    $ids_acteurs = [];
    $liste_acteurs = '';
    $compteur = 0;

    while (($ligne = fgetcsv($handle)) !== false) {
        // Associer les colonnes aux en-têtes
        $acteur = array_combine($entetes, $ligne);

        // Primo j'insère dans la table participants : j'ai tout ce qu'il faut comme information
        $stmt = "INSERT INTO participants(id_user, nom, prenoms, matricule_ifu, date_naissance, lieu_naissance, diplome_le_plus_eleve) VALUES ({$_SESSION['user_id']}, :nom, :prenoms, :matricule_ifu, :date_naissance, :lieu_naissance, :diplome_le_plus_eleve)";
        $stmt = $bdd->prepare($stmt);

        $stmt->execute([
            'nom' => mb_strtoupper($acteur['Nom'], 'UTF-8'),
            'prenoms' => $acteur['Prénoms'],
            'matricule_ifu' => $acteur['IFU'],
            'date_naissance' => $acteur['Date de Naissance'],
            'lieu_naissance' => $acteur['Lieu de Naissance'],
            'diplome_le_plus_eleve' => $acteur['Diplôme']
        ]);

        $id_acteur = $bdd->lastInsertId();
        $ids_acteurs[] = $id_acteur;

        if ($compteur == 0) {
            $liste_acteurs .= $id_acteur;
        } else {
            $liste_acteurs .= ',' . $id_acteur;
        }

        $nbr_comptes = rand(1, NOMBRE_MAXIMAL_COMPTES); // En principe entre 1 et 3 puisqu'à l'heure où je conçois cette fonction le nombre maximal de comptes vaut 3

        for ($i = 0; $i < $nbr_comptes; $i++) {
            $banque = $banques[rand(0, count($banques) - 1)]; // On prend une banque de façon aléatoire

            // Ici je crée le fichier pdf nécessaire
            $pdf = new TCPDF('P', 'mm', 'A4');
            $pdf->AddFont('trebucbd', '', 'trebucbd.php');
            $pdf->setPrintHeader(false); // Retrait de la ligne du haut qui s'affiche par défaut sur une page
            configuration_pdf($pdf, $_SESSION['nom'] . ' ' . $_SESSION['prenoms'], 'Copie PDF RIB');
            $pdf->setMargins(15, 25, 15, true);
            $pdf->setAutoPageBreak(true, 25); // marge bas = 25 pour footer
            $pdf->AddPage();

            // Titre de la page

            $pdf->setFont('trebucbd', '', 16);
            $pdf->Cell(0, 10, mb_strtoupper('Copie PDF du RIB N°' . ($i + 1) . ' de ' . $acteur['Nom'] . ' ' . $acteur['Prénoms'], 'UTF-8'), 0, 1, 'C');
            $pdf->Ln(2);
            $pdf->setFont('trebucbd', '', 12);
            // $pdf->Cell(0, 10, mb_strtoupper($acteur['Nom'] . ' ' . $acteur['Prénoms'], 'UTF-8'), 0, 1, 'C');
            $numero_compte = mb_strtoupper($banque . rand(10000000, 19999999), 'UTF-8');
            $pdf->Cell(0, 10, mb_strtoupper($banque . ' (' . $numero_compte . ')'), 0, 1, 'C');
            $upload_path = creer_dossiers_upload() . 'demo/';
            if (!is_dir($upload_path)) {
                mkdir($upload_path, PERMISSIONS);
            }
            $nom_fichier = $upload_path . 'pdf_' . $acteur['IFU'] . '_' . ($i + 1) . '.pdf';
            $pdf->Output($nom_fichier, 'F');

            // J'enregistre les informations en bdd

            // On commence par la table fichiers
            $stmt = $bdd->prepare('INSERT INTO fichiers(chemin_acces, nom_original, date_upload) VALUES (:chemin_acces, :nom_original, :date_upload)');
            $stmt->execute([
                'chemin_acces' => $nom_fichier,
                'nom_original' => 'copie_pdf_demo.pdf',
                'date_upload' => date("Y-m-d H:i:s")
            ]);
            $id_fichier = $bdd->lastInsertId();

            // Puis viens la table informations_bancaires

            $stmt = $bdd->prepare('INSERT INTO informations_bancaires(id_participant, banque, numero_compte, id_rib) VALUES (:id_participant, :banque, :numero_compte, :id_rib)');
            $stmt->execute([
                'id_participant' => $id_acteur,
                'banque' => $banque,
                'numero_compte' => $numero_compte,
                'id_rib' => $id_fichier
            ]);

            $id_compte = $bdd->lastInsertId();
            $ids_comptes[count($ids_acteurs) - 1][] = $id_compte;
        }
        $compteur++;
        // Et c'est tout pour la création pseudo-aléatoire des acteurs
    }
    fclose($handle);

    // 4ème étape : réaliser 50 liaisons par activités

    $compteur = 0;
    for ($i = 0; $i < count($infos_activites['ids_activites']); $i++) {
        $id_activite = $infos_activites['ids_activites'][$i];

        for ($j = 0; $j < $nbr_acteurs / 3; $j++) {
            // Pour réaliser une liaison, il me faut l'id du participant, l'id de l'activité, l'id du titre, l'id du compte bancaire, le nombre de jours et le nombre de tâches

            $id_acteur = $ids_acteurs[$compteur];
            $titres = $infos_activites['titres_activites'][$i];
            $id_titre = $titres[rand(0, count($titres) - 1)];
            $comptes = $ids_comptes[$compteur];
            $id_compte = $comptes[rand(0, count($comptes) - 1)];
            $nbr_jours = rand(1, 100);

            if ($i == 2) {
                // Activité de type 3
                $nbr_taches = rand(1, 100);
            } else {
                $nbr_taches = null;
            }

            $stmt = $bdd->prepare('INSERT INTO participations(id_participant, id_activite, id_titre, id_compte_bancaire, nombre_jours, nombre_taches) VALUES (:id_participant, :id_activite, :id_titre, :id_compte_bancaire, :nbr_jours, :nbr_taches)');
            $stmt->execute([
                'id_participant' => $id_acteur,
                'id_activite' => $id_activite,
                'id_titre' => $id_titre,
                'id_compte_bancaire' => $id_compte,
                'nbr_jours' => $nbr_jours,
                'nbr_taches' => $nbr_taches
            ]);
            $compteur++;
        }
    }

    // 5ème et dernière étape : On insère dans la bdd les informations liées aux informations de demo

    $stmt = $bdd->prepare('INSERT INTO informations_demo(id_user, ids_activites, ids_participants) VALUES (' . $_SESSION['user_id'] . ', :ids_activites, :ids_participants)');
    $stmt->execute([
        'ids_activites' => $infos_activites['liste_activites'],
        'ids_participants' => $liste_acteurs
    ]);

    // C'est tout

    return true;
}

function verifierDemoActive()
{
    // Une fonction pour savoir si des données de démo ont été générées ou pas
    global $bdd;
    $stmt = $bdd->query('SELECT * FROM connexion WHERE user_id=' . $_SESSION['user_id'] . ' AND demo=1');
    return $stmt->rowCount() == 1 ? true : false;
}

// Fonctions pour la génération des documents dans un seul fichier

function genererOrdreVirement($id_activite, $banque, $navigateur = true, $zip = false)
{
    global $dossier_exports_temp, $bdd;

    // Récupération des informations nécessaires
    $stmt = "
    SELECT
    pa.id_participant,
    a.type_activite,
    p.nom, 
    p.prenoms,
    t.nom as qualite,
    t.indemnite_forfaitaire,
    ib.banque,
    ib.numero_compte as rib,
    a.taux_journalier,
    a.taux_taches,
    a.frais_deplacement_journalier as fdj,
    pa.nombre_jours,
    pa.nombre_taches,
    a.nom as titre_activite,
    a.titre_financier,
    a.financier,
    a.premier_responsable,
    a.titre_responsable
    FROM participations pa
    INNER JOIN participants p ON pa.id_participant=p.id_participant
    INNER JOIN titres t ON pa.id_titre = t.id_titre
    INNER JOIN informations_bancaires ib ON p.id_participant=ib.id_participant
    INNER JOIN activites a ON pa.id_activite=a.id
    WHERE pa.id_activite=$id_activite AND ib.banque =:banque 
";
    $stmt = $bdd->prepare($stmt);
    $stmt->bindParam('banque', $banque);
    $stmt->execute();
    $informations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Configuration du document
    $pdf = new TCPDF('P', 'mm', 'A4');
    $pdf->AddFont('trebucbd', '', 'trebucbd.php');
    $pdf->setPrintHeader(false); // Retrait de la ligne du haut qui s'affiche par défaut sur une page
    $pdf->setMargins(15, 25, 15, true);
    configuration_pdf($pdf, $_SESSION['nom'] . ' ' . $_SESSION['prenoms'], 'Ordre de virement ' . $banque);
    $pdf->setAutoPageBreak(true, 25); // marge bas = 25 pour footer
    $pdf->AddPage();

    // Header
    $informations_necessaires = ['titre' => $informations[0]['titre_activite'], 'banque' => $banque];
    // $informations_necessaires = ['titre' => $resultats[0]['titre_activite']];
    genererHeader($pdf, 'ordre_virement', $informations_necessaires, $id_activite);
    // genererHeader($pdf, 'etat_paiement_1', $informations_necessaires, $id_activite);

    $pdf->Ln(20);

    $largeurPage = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
    $tailles_colonnes = [0.05, 0.22, 0.15, 0.15, 0.15, 0.28]; // à multiplier par 100 pour avoir les pourcentages

    foreach ($tailles_colonnes as $index => $taille) {
        $tailles_colonnes[$index] = $taille * $largeurPage;
    }

    // Tableau
    // Entête
    $pdf->setFont('trebucbd', '', 10);
    $pdf->setFillColor(242, 242, 242); // #f2f2f2
    $hauteur = 10;
    // $pdf->SetLineWidth(0.2); // 0.2 mm ~ HTML border=1
    $pdf->Cell($tailles_colonnes[0], $hauteur, 'N°', 1, 0, 'C', true); // 5%
    $pdf->Cell($tailles_colonnes[1], $hauteur, strtoupper('Nom et prenoms'), 1, 0, 'C', true);
    $pdf->Cell($tailles_colonnes[2], $hauteur, strtoupper('Qualite'), 1, 0, 'C', true);
    $pdf->Cell($tailles_colonnes[3], $hauteur, strtoupper('Montant'), 1, 0, 'C', true);
    $pdf->Cell($tailles_colonnes[4], $hauteur, strtoupper('Banque'), 1, 0, 'C', true);
    $pdf->Cell($tailles_colonnes[5], $hauteur, strtoupper('Rib'), 1, 0, 'C', true);
    $pdf->Ln();

    foreach ($tailles_colonnes as $index => $taille) {
        $tailles_colonnes[$index] = $taille / $largeurPage * 100;
    }

    $total = 0;
    $style = '
<style>
td{
text-align : center;
line-height : 16px;
}
</style>
';

    $html = $style . '
<table border="1" cellpadding="5" width="100%">
<tbody>';

    $pdf->setFont('trebuc', '', 10);

    for ($i = 0; $i < count($informations); $i++) {
        $ligne = $informations[$i];
        $montant = montantParticipant($ligne['id_participant'], $id_activite);

        // Une ligne
        $html .= '<tr>';

        // $pdf->setFillColor(255, 255, 255); // #fff
        // N°
        $html .= '<td width="' . $tailles_colonnes[0] . '%">' . ($i + 1) . '</td>';
        // $pdf->Cell($tailles_colonnes[0], $hauteur, $i + 1, 1, 0, 'C');
        // Nom et prénoms
        $html .= '<td width="' . $tailles_colonnes[1] . '%">' . $ligne['nom'] . ' ' . $ligne['prenoms'] . '</td>';
        // $pdf->Cell($tailles_colonnes[1], $hauteur, $ligne['nom'] . ' ' . $ligne['prenoms'], 1);
        // Qualité
        $html .= '<td width="' . $tailles_colonnes[2] . '%">' . $ligne['qualite'] . '</td>';
        // $pdf->Cell($tailles_colonnes[2], $hauteur, $ligne['qualite'], 1, 0, 'C');
        // Montant
        $html .= '<td width="' . $tailles_colonnes[3] . '%">' . number_format($montant, 0, ',', '.') . '</td>';
        // $pdf->Cell($tailles_colonnes[3], $hauteur, number_format($montant, 0, ',', '.'), 1, 0, 'C');
        // Banque
        $html .= '<td width="' . $tailles_colonnes[4] . '%">' . $banque . '</td>';
        // $pdf->Cell($tailles_colonnes[4], $hauteur, $banque, 1, 0, 'C');
        // Rib
        $html .= '<td width="' . $tailles_colonnes[5] . '%">' . $ligne['rib'] . '</td>';
        // $pdf->Cell($tailles_colonnes[5], $hauteur, $ligne['rib'], 1, 0, 'C');
        // $pdf->Ln();
        $html .= '</tr>';
        $total += $montant;
    }

    $html .= '
</tbody>
</table>';

    $pdf->writeHTML($html, false, false, true, false, '');

    // Dernière ligne du tableau pour le total

    foreach ($tailles_colonnes as $index => $taille) {
        $tailles_colonnes[$index] = $taille / 100 * $largeurPage;
    }

    $pdf->setFont('trebucbd', '', 10);
    $pdf->setFillColor(242, 242, 242); // #f2f2f2
    // Total ( )
    $pdf->Cell($tailles_colonnes[0] + $tailles_colonnes[1] + $tailles_colonnes[2], $hauteur, strtoupper('Total ( )'), 1, 0, 'C', true);
    // Montant
    $pdf->Cell($tailles_colonnes[3], $hauteur, number_format($total, 0, ',', '.'), 1, 0, 'C', true);
    // Banque
    $pdf->Cell($tailles_colonnes[4], $hauteur, '', 1, 0, 'C', true);
    // Rib
    $pdf->Cell($tailles_colonnes[5], $hauteur, '', 1, 0, 'C', true);
    $pdf->Ln(15);

    // On s'attaque à la phrase en dessous du tableau
    $formatter = new NumberFormatter('fr_FR', NumberFormatter::SPELLOUT);
    $pdf->MultiCell(0, 10, "Arrêté le présent ordre de virement à la somme de " . mb_strtoupper($formatter->format($total), 'UTF-8') . "(" . number_format($total, 0, ',', '.') . ") Francs CFA", 0, 'C');
    $pdf->Ln(8);

    // Bloc du bas avec le financier et le premier responsable

    $bloc_gauche = mb_strtoupper($informations[0]['financier']);
    $bloc_droite = mb_strtoupper($informations[0]['premier_responsable']);
    afficherTexteDansDeuxBlocs($pdf, $bloc_gauche, $bloc_droite, 'trebucbd', 10, 5, 'C', '', 'C', '');

    $pdf->Ln(15);

    $bloc_gauche = mb_strtoupper($informations[0]['titre_financier']);
    $bloc_droite = mb_strtoupper($informations[0]['titre_responsable']);
    afficherTexteDansDeuxBlocs($pdf, $bloc_gauche, $bloc_droite, 'trebucbd', 10, 2, 'C', 'U', 'C', 'U');

    // //Sortie du pdf
    if ($navigateur) {
        $pdf->Output('Ordre de virement ' . $banque . '.pdf', 'I');
    } else {
        // On ne veut pas le document pour une sortie en navigateur
        $chemin_fichier = $dossier_exports_temp . '/Ordre de virement ' . $banque . '.pdf';
        $pdf->Output($chemin_fichier, 'F');
        return $chemin_fichier;
    }
}

function genererSyntheseOrdres($id_activite, $navigateur = true)
{
    global $bdd, $dossier_exports_temp;
    $liste_banques = listeBanques($id_activite);

    foreach ($liste_banques as $banque) {
        $totaux_banques[] = totalBanque($id_activite, $banque);
    }

    $stmt = $bdd->query('SELECT nom FROM activites WHERE id=' . $id_activite);
    $titre_activite = $stmt->fetch(PDO::FETCH_NUM);
    $titre_activite = $titre_activite[0];
    $stmt->closeCursor();

    // Configuration du document
    $pdf = new TCPDF('P', 'mm', 'A4');
    $pdf->AddFont('trebucbd', '', 'trebucbd.php');
    $pdf->setPrintHeader(false); // Retrait de la ligne du haut qui s'affiche par défaut sur une page
    configuration_pdf($pdf, $_SESSION['nom'] . ' ' . $_SESSION['prenoms'], 'Tableau récapitulatif');
    $pdf->setMargins(15, 25, 15, true);
    $pdf->setAutoPageBreak(true, 25); // marge bas = 25 pour footer
    $pdf->AddPage();

    // Titre de la page

    $pdf->setFont('trebucbd', '', 16);
    $pdf->Cell(0, 10, mb_strtoupper('Tableau récapitulatif', 'UTF-8'), 0, 1, 'C');
    $pdf->Ln(8);

    // Tableau

    // Ecriture de l'entête

    $pdf->setFont('trebucbd', '', 10);

    // $liste_banques = ['BOA', 'CorisBenin', 'Atlantique Bénin', 'CCP', 'NSIA', 'ORABANK', 'SGB', 'UBA', 'CorisBenin1', 'Atlantique Bénin1', 'CCP1', 'NSIA1', 'ORABANK1', 'SGB1', 'UBA1'];
    // $liste_banques = ['BOA', 'CorisBenin', 'Atlantique Bénin'];
    // $totaux_banques = [0.1, 0.2, 0.3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 15];

    // $liste_banques = ['BOA', 'CorisBenin', 'Atlantique Bénin', 'CCP', 'NSIA', 'ORABANK', 'SGB', 'UBA', 'CorisBenin1', 'Atlantique Bénin 1', 'CCP1', 'NSIA1', 'ORABANK1', 'SGB1', 'UBA1'];
    // $totaux_banques = [20000, 40000, 30000, 2000, 1000, 1000, 1000, 10000, 5000, 30000, 40000, 20000, 30000, 10000, 5000];

    // $largeurPage = $pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right'];
    $tailles_colonnes = [40, 20];

    $nbr_banques = count($liste_banques);
    $var_inter = $nbr_banques;

    // Détermination du nombre de lignes du tableau

    if ($var_inter <= 3) {
        // Il y a moins de 3 banques donc une seule ligne suffit
        $nbr_lignes = 1;
    } elseif (3 < $var_inter && $var_inter <= 8) {
        // On est entre 3 et 8 banques, autres en dehors des trois banques affichées sur la première ligne, il y a encore entre 1 et 5 banques à afficher. Une seconde ligne suffira pour cela
        $nbr_lignes = 2;
    } elseif ($var_inter > 8) {
        // On a plus de 8 banques donc on est déjà sur une troisième ligne. De là on retranche d'abord le nombre de banques qui seront affichées sur les lignes 1 et 2 à savoir 8, on détermine le reste et on boucle sur la valeur obtenue. Donc tant que ce reste sera supérieur à 5, on ajoutera une ligne au tableau.
        $nbr_lignes = 3;
        $var_inter -= 8;
        // Supposons qu'en dehors des 8 banques il reste encore 12 banques à afficher
        // A la première itération, le nombre de lignes passera à 4, nous permettant d'afficher 5 banques parmi les 12. Il en restera 7
        // A la seconde itération (puisque le nombre de banques est toujours supérieur à 5), le nombre de lignes passera à 5 permettant d'afficher aussi 5 banques. Il en restera 2 à afficher. On sortira de la boucle while avec un 5 lignes en tout pour le tableau, un nombre suffisant dans le cas d'espèce.

        // Supposons par contre qu'il y ait 17 banques plutôt
        // Première itération : $nbr_lignes -> 4, $var_inter -> 12
        // Seconde itération : $nbr_lignes -> 5, $var_inter -> 7
        // Troisième itération : $nbr_lignes -> 6, $var_inter -> 2
        // Je présume que c'est bon. En tout cas jusque là le nombre de lignes obtenu est valide

        while ($var_inter > 5) {
            $nbr_lignes++;
            $var_inter -= 5;
        }
    }

    $style = '
<style>
th{
background-color : #f2f2f2;
text-align : center;
}
td{
text-align : center;
line-height : 16px;
}
</style>
';

    $compteur = 0;
    $compteur_2 = 0;

    for ($i = 0; $i < $nbr_lignes; $i++) {
        // Chaque ligne
        $pourcentage = $i == 0 ? 100 - $tailles_colonnes[0] : 100;
        $nouvelle_ligne = false; // pour le montant total

        $html = $style . '
<table border="1" cellpadding="5" width="100%">
<thead>
<tr>';
        // Header
        if ($i == 0) {
            $html .= '
<th width="' . $tailles_colonnes[0] . '%">ELEMENT</th>';
            $max_j = $nbr_banques < 3 ? $nbr_banques : 3;
        } else {
            $max_j = $nbr_banques > 5 ? 5 : $nbr_banques;
        }

        for ($j = 0; $j < $max_j; $j++) {
            $html  .= '
<th width="' . $tailles_colonnes[1] . '%">' . mb_strtoupper($liste_banques[$compteur], 'UTF-8') . '</th>';
            $compteur++;
            $nbr_banques--;
            $pourcentage -= $tailles_colonnes[1];
        }

        if ($i == $nbr_lignes - 1 && $pourcentage != 0) {
            // Dernière ligne et il reste encore de la place
            $html .= '
    <th width="' . $pourcentage . '%">MONTANT TOTAL</th>
            ';
        } elseif ($i == $nbr_lignes - 1 && $pourcentage == 0) {
            $nouvelle_ligne = true;
        }

        $html .= '
</tr>
</thead>
<tbody>
<tr>';
        // Body
        if ($i == 0) {
            $html .= '
<td width="' . $tailles_colonnes[0] . '%">' . mb_strtoupper($titre_activite, 'UTF-8') . '</td>';
        }

        for ($j = 0; $j < $max_j; $j++) {
            $html  .= '
<td width="' . $tailles_colonnes[1] . '%">' . number_format($totaux_banques[$compteur_2], 0, ',', '.') . '</td>';
            $compteur_2++;
        }

        if ($i == $nbr_lignes - 1 && $pourcentage != 0) {
            // Dernière ligne et il reste encore de la place
            $html .= '
    <td width="' . $pourcentage . '%">' . number_format(array_sum($totaux_banques), 0, ',', '.') . ' FCFA</td>
            ';
        }
        $html .= '
</tr>
</tbody>
</table>
    ';
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Ln();

        if ($nouvelle_ligne) {
            $html =
                $style .
                '
    <table border="1" cellpadding="5" width="100%">
    <thead>
    <tr>
    <th>MONTANT TOTAL</th>
    </tr>
    </thead>
    <tbody>
    <tr>
    <td>' . number_format(array_sum($totaux_banques), 0, ',', '.') . ' FCFA</td>
    </tr>
    </tbody>
    </table>
    ';
            $pdf->writeHTML($html, true, false, true, false, '');
            $pdf->Ln();
        }
    }

    //Sortie du pdf
    if ($navigateur) {
        $pdf->Output('Tableau récapitulatif.pdf', 'I');
    } else {
        $chemin_fichier = $dossier_exports_temp . '/Synthèse des ordres de virement.pdf';
        $pdf->Output($chemin_fichier, 'F');
        return $chemin_fichier;
    }
}

function genererNoteAttestation($activity_id, $document, $navigateur = true)
{
    global $bdd, $dossier_exports_temp;

    // Classe personnalisée pour la numérotation des pages
    if (!class_exists('MYPDF') & $navigateur) {
        class MYPDF extends TCPDF
        {
            public function Footer()
            {
                // $this->SetY(-15);
                $this->SetFont('trebucbd', '', 8); // Police grasse pour le pied de page
                // $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0);
                $this->Cell(0, 10,  $this->getAliasNumPage(), 0, false, 'C', 0);
            }
        }
    } elseif (!class_exists('MYPDF') && !$navigateur) {
        class MYPDF extends TCPDF
        {
            public function Footer()
            {
                // $this->SetY(-15);
                // $this->SetFont('trebucbd', '', 8); // Police grasse pour le pied de page
                // // $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0);
                // $this->Cell(0, 10,  $this->getAliasNumPage(), 0, false, 'C', 0);
            }
        }
    }

    ob_start();

    // Requête SQL pour récupérer les informations
    $sql = "
    SELECT 
        p.id_participant,
        p.nom,
        p.prenoms,
        t.nom AS titre_participant,
        ib.banque,
        ib.numero_compte,
        a.nom AS nom_activite,
        a.premier_responsable,
        a.titre_responsable,
        a.financier,
        a.titre_financier,
        a.organisateur,
        a.titre_organisateur,
        a.timbre,
        a.reference
    FROM participations pa
    INNER JOIN participants p ON pa.id_participant = p.id_participant
    INNER JOIN activites a ON pa.id_activite = a.id
    INNER JOIN titres t ON pa.id_titre = t.id_titre
    INNER JOIN informations_bancaires ib ON pa.id_compte_bancaire = ib.id
    WHERE pa.id_activite = :activite_id
    ORDER BY p.nom ASC, p.prenoms ASC
";

    $stmt = $bdd->prepare($sql);
    $stmt->execute(['activite_id' => $activity_id]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $titre_activite = $participants[0]['nom_activite'];

    $stmt = $bdd->query('SELECT * FROM informations_entete WHERE id_activite=' . $activity_id);
    $informations_entete = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $informations_entete = $informations_entete[0];

    $formatter = new IntlDateFormatter("fr_FR", IntlDateFormatter::LONG, IntlDateFormatter::NONE, "Europe/Paris", IntlDateFormatter::GREGORIAN);
    $dateFr = $formatter->format(new DateTime());
    $nom_activite = isset($participants[0]["nom_activite"]) ? htmlspecialchars($participants[0]["nom_activite"]) : '';

    $pdf = new MYPDF('P', 'mm', 'A4');
    $pdf->AddFont('trebuc', '', 'trebuc.php'); // Police non-grasse
    $pdf->AddFont('trebucbd', '', 'trebucbd.php'); // Police grasse
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true); // Activer le pied de page pour la numérotation
    $pdf->setMargins(15, 25, 15, true);
    $pdf->setAutoPageBreak(true, 25); // Marge bas = 25 pour footer
    $pdf->AddPage();

    $style = '
<style>
    th {
        background-color: #f2f2f2;
        text-align: center;
        font-weight: bold;
        font-family: trebucbd;
    }
    td {
        text-align: center;
        line-height: 16px;
        font-weight: normal;
        font-family: trebuc;
    }
</style>';

    if ($document === 'note') {
        // *** Note de Service PDF ***
        configuration_pdf($pdf, $_SESSION['nom'] . ' ' . $_SESSION['prenoms'], 'Note de service');

        $information_supplementaire = ['titre' => $titre_activite];
        genererHeader($pdf, 'note_service', $information_supplementaire, $activity_id);
        $pdf->setFont('trebucbd', '', 10); // Gras pour les éléments hors tableau

        $html = $style . '
    <br><br><br><br><br>
    <h4><b style="font-family: trebucbd;">N°: ' . htmlspecialchars($participants[0]['timbre']) . '</b></h4>
    <p><b style="font-family: trebucbd; text-decoration:underline;">Réf:</b> NS N° ' . htmlspecialchars($participants[0]['reference']) . ' DU ' . htmlspecialchars($informations_entete['date2']) . '</p><br><br>
    <table border="1" cellpadding="5" style="width: 100%; text-align:center">
        <thead>
            <tr>
                <th style="width: 12%;">N°</th>
                <th style="width: 25%;">NOM ET PRENOMS</th>
                <th style="width: 15%;">TITRE</th>
                <th style="width: 15%;">BANQUE</th>
                <th style="width: 33%;">NUMERO DE COMPTE</th>
            </tr>
        </thead>
        <tbody>';
        $i = 1;

        foreach ($participants as $p) {
            $html .= '<tr>
                    <td style="width: 12%;">' . $i++ . '</td>
                    <td style="width: 25%;">' . htmlspecialchars($p['nom'] . ' ' . $p['prenoms'] ?? '') . '</td>
                    <td style="width: 15%;">' . htmlspecialchars($p['titre_participant'] ?? '') . '</td>
                    <td style="width: 15%;">' . htmlspecialchars($p['banque'] ?? '') . '</td>
                    <td style="width: 33%;">' . htmlspecialchars($p['numero_compte'] ?? '') . '</td>
                  </tr>';
        }
        $html .= '</tbody></table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        // Premier responsable et son titre
        $pdf->Ln(10);
        $pdf->setFont('trebucbd', '', 10);
        $pdf->Cell(0, 10, htmlspecialchars($participants[0]['titre_responsable'] ?? ''), 0, 1, 'C');
        $pdf->Ln(10);
        $pdf->setFont('trebucbd', 'U', 10);
        $pdf->Cell(0, 10, htmlspecialchars($participants[0]['premier_responsable'] ?? ''), 0, 1, 'C');

        ob_clean();
        ob_end_clean();
        if ($navigateur) {
            $pdf->Output('Note de service.pdf', 'I');
        } else {
            $chemin_fichier = $dossier_exports_temp . '/Note de service.pdf';
            $pdf->Output($chemin_fichier, 'F');
            return $chemin_fichier;
        }
    } elseif ($document === 'attestation') {
        configuration_pdf($pdf, $_SESSION['nom'] . ' ' . $_SESSION['prenoms'], 'Attestation collective');
        $information_supplementaire = ['titre' => $titre_activite];
        genererHeader($pdf, 'attestation_collective', $information_supplementaire, $activity_id);
        $pdf->setFont('trebuc', '', 10);

        $html = $style . '
    <br><br><br><br><br><br><br><br>
    <table border="1" cellpadding="5" style="width: 100%; text-align:center">
        <thead>
            <tr>
                <th style="width: 12%;">N°</th>
                <th style="width: 25%;">NOM ET PRENOMS</th>
                <th style="width: 15%;">TITRE</th>
                <th style="width: 15%;">BANQUE</th>
                <th style="width: 33%;">NUMERO DE COMPTE</th>
            </tr>
        </thead>
        <tbody>';
        $i = 1;
        foreach ($participants as $p) {
            $html .= '<tr>
                    <td style="width: 12%;">' . $i++ . '</td>
                    <td style="width: 25%;">' . htmlspecialchars($p['nom'] . ' ' . $p['prenoms'] ?? '') . '</td>
                    <td style="width: 15%;">' . htmlspecialchars($p['titre_participant'] ?? '') . '</td>
                    <td style="width: 15%;">' . htmlspecialchars($p['banque'] ?? '') . '</td>
                    <td style="width: 33%;">' . htmlspecialchars($p['numero_compte'] ?? '') . '</td>
                  </tr>';
        }
        $html .= '</tbody></table>';

        $pdf->writeHTML($html, true, false, true, false, '');

        $premier_responsable = isset($participants[0]['premier_responsable']) ? htmlspecialchars($participants[0]['premier_responsable']) : '';
        $titre_responsable = isset($participants[0]['titre_responsable']) ? htmlspecialchars($participants[0]['titre_responsable']) : '';
        $financier = isset($participants[0]['financier']) ? htmlspecialchars($participants[0]['financier']) : '';
        $titre_financier = isset($participants[0]['titre_financier']) ? htmlspecialchars($participants[0]['titre_financier']) : '';

        // Ajouter les informations du premier responsable et son titre sous le tableau
        $pdf->Ln(10);
        $bloc_gauche = mb_strtoupper($participants[0]['titre_organisateur'] ?? '');
        //$pdf->Ln(10);
        $bloc_droite = mb_strtoupper($participants[0]['titre_responsable'] ?? '');
        //$pdf->Ln(10);
        afficherTexteDansDeuxBlocs($pdf, $bloc_gauche, $bloc_droite, 'trebucbd', 10, 5, 'C', '', 'C', '');

        $pdf->Ln(10);
        $bloc_gauche = mb_strtoupper($participants[0]['organisateur'] ?? '');
        $bloc_droite = mb_strtoupper($participants[0]['premier_responsable'] ?? '');
        afficherTexteDansDeuxBlocs($pdf, $bloc_gauche, $bloc_droite, 'trebucbd', 10, 2, 'C', 'U', 'C', 'U');

        ob_clean();
        ob_end_clean();
        if ($navigateur) {
            $pdf->Output('Attestation collective.pdf', 'I');
        } else {
            $chemin_fichier = $dossier_exports_temp . '/Attestation collective.pdf';
            $pdf->Output($chemin_fichier, 'F');
            return $chemin_fichier;
        }
    }
}

function genererEtatPaiement($id_activite, $navigateur = true)
{
    global $bdd, $dossier_exports_temp;

    if (!function_exists('convertir_en_lettres')) {
        function convertir_en_lettres($nombre)
        {
            $fmt = new NumberFormatter("fr", NumberFormatter::SPELLOUT);
            return ucfirst($fmt->format($nombre));
        }
    }



    $stmt = $bdd->prepare('SELECT type_activite, nom, reference FROM activites WHERE id = :id');
    $stmt->execute(['id' => $id_activite]);
    $resultat = $stmt->fetch(PDO::FETCH_ASSOC);
    $id_type_activite = $resultat['type_activite'];
    $nom_activite = htmlspecialchars($resultat['nom']);
    $stmt->closeCursor();

    // Fonction pour générer le tableau d'en-tête
    if (!function_exists('startTable')) {
        function startTable($type_activite)
        {
            switch ($type_activite) {
                case 1:
                    return '
                <style>
                    th { font-weight: bold; font-family: trebucbd; }
                    td { font-weight: normal; font-family: trebuc; }
                </style>
                <table border="1" cellpadding="4" align="center">
                    <thead>
                        <tr style="background-color: #f2f2f2; font-size:8px;">
                            <th width="6%">N°</th>
                            <th width="20%">NOM ET PRENOMS</th>
                            <th width="15%">QUALITE</th>
                            <th width="8%">TAUX/JOUR</th>
                            <th width="6%">NBRE JOUR</th>
                            <th width="12%">MONTANT</th>
                            <th width="10%">BANQUE</th>
                            <th width="23%">RIB</th>
                        </tr>
                    </thead>
                    <tbody>';
                case 2:
                    return '
                <style>
                    th { font-weight: bold; font-family: trebucbd; }
                    td { font-weight: normal; font-family: trebuc; }
                </style>
                <table border="1" cellpadding="4" align="center">
                    <thead>
                        <tr style="background-color:#f2f2f2; font-size:8px;">
                            <th width="5%">N°</th>
                            <th width="18%">NOM ET PRENOMS</th>
                            <th width="11%">QUALITE</th>
                            <th width="7%">TAUX/JOUR</th>
                            <th width="8%">NOMBRE DE JOURS</th>
                            <th width="12%">INDEMNITE FORFAITAIRE</th>
                            <th width="11%">MONTANT</th>
                            <th width="10%">BANQUE</th>
                            <th width="18%">RIB</th>
                        </tr>
                    </thead>
                    <tbody>';
                case 3:
                    return '
                <style>
                    th { font-weight: bold; font-family: trebucbd; }
                    td { font-weight: normal; font-family: trebuc; }
                </style>
                <table border="1" cellpadding="5" align="center">
                    <thead>
                        <tr style="background-color:#f2f2f2; font-size:8px;">
                            <th width="5%">N°</th>
                            <th width="13%">NOM ET PRENOM</th>
                            <th width="9%">TITRE</th>
                            <th width="6%">TAUX/TÂCHE</th>
                            <th width="7%">NOMBRE DE TÂCHE</th>
                            <th width="9%">FRAIS ENTRETIENS PAR JOURS</th>
                            <th width="7%">NOMBRE DE JOURS</th>
                            <th width="13%">INDEMNITE FORFAITAIRE</th>
                            <th width="8%">MONTANT</th>
                            <th width="8%">BANQUE</th>
                            <th width="15%">RIB</th>
                        </tr>
                    </thead>
                    <tbody>';
                default:
                    return '';
            }
        }
    }


    // Fonction pour générer une ligne de données
    if (!function_exists('generateRow')) {
        function generateRow($row, $type_activite, $i)
        {
            switch ($type_activite) {
                case 1:
                    return '
            <tr>
                <td width="6%">' . $i . '</td>
                <td width="20%">' . htmlspecialchars($row['nom_participant'] . ' ' . $row['prenoms']) . '</td>
                <td width="15%">' . htmlspecialchars($row['titre_participant']) . '</td>
                <td width="8%">' . number_format($row['taux_journalier'], 0, ',', '.') . '</td>
                <td width="6%">' . (int)$row['nombre_jours'] . '</td>
                <td width="12%">' . number_format($row['montant'], 0, ',', '.') . '</td>
                <td width="10%">' . htmlspecialchars($row['banque']) . '</td>
                <td width="23%">' . htmlspecialchars($row['rib']) . '</td>
            </tr>';
                case 2:
                    $indemnite = isset($row['indemnite_forfaitaire']) ? $row['indemnite_forfaitaire'] : 0;
                    return '
            <tr>
                <td width="5%">' . $i . '</td>
                <td width="18%">' . htmlspecialchars($row['nom_participant'] . ' ' . $row['prenoms']) . '</td>
                <td width="11%">' . htmlspecialchars($row['titre_participant']) . '</td>
                <td width="7%">' . number_format($row['taux_journalier'], 0, ',', '.') . '</td>
                <td width="8%">' . (int)$row['nombre_jours'] . '</td>
                <td width="12%">' . number_format($indemnite, 0, ',', '.') . '</td>
                <td width="11%">' . number_format($row['montant'], 0, ',', '.') . '</td>
                <td width="10%">' . htmlspecialchars($row['banque']) . '</td>
                <td width="18%">' . htmlspecialchars($row['rib']) . '</td>
            </tr>';
                case 3:
                    $indemnite = isset($row['indemnite_forfaitaire']) ? $row['indemnite_forfaitaire'] : 0;
                    return '
            <tr>
                <td width="5%">' . $i . '</td>
                <td width="13%">' . htmlspecialchars($row['nom_participant'] . ' ' . $row['prenoms']) . '</td>
                <td width="9%">' . htmlspecialchars($row['titre_participant']) . '</td>
                <td width="6%">' . number_format($row['taux_taches'], 0, ',', '.') . '</td>
                <td width="7%">' . (int)$row['nombre_taches'] . '</td>
                <td width="9%">' . number_format($row['frais_deplacement_journalier'], 0, ',', '.') . '</td>
                <td width="7%">' . (int)$row['nombre_jours'] . '</td>
                <td width="13%">' . number_format($indemnite, 0, ',', '.') . '</td>
                <td width="8%">' . number_format($row['montant'], 0, ',', '.') . '</td>
                <td width="8%">' . htmlspecialchars($row['banque']) . '</td>
                <td width="15%">' . htmlspecialchars($row['rib']) . '</td>
            </tr>';
                default:
                    return '';
            }
        }
    }

    // Fonction pour générer le PDF
    if (!function_exists('generatePDF')) {
        function generatePDF($pdf, $data, $type_activite, $nom_activite, $id_activite, $reference, $navigateur)
        {
            if (!($pdf instanceof MYPDF2)) {
                die("Erreur : \$pdf n'est pas une instance de MYPDF");
            }

            // Ajouter l'en-tête personnalisé uniquement sur la première page
            $pdf->SetFont('trebucbd', '', 10); // Police grasse pour l'en-tête
            $information_supplementaire = ($type_activite == 1) ? ['type' => $nom_activite] : ['titre' => $nom_activite];
            genererHeader($pdf, 'etat_paiement_' . $type_activite, $information_supplementaire, $id_activite);

            $pdf->Ln(20);
            $reference = ($type_activite === 3)
                ? $reference
                : 'REF ' . $reference;
            $html = '<p align="center"><b style="font-family: trebucbd;">' . $reference . ' PORTANT CONSTITUTION DES COMMISSIONS CHARGEES DE ' . mb_strtoupper($nom_activite, 'UTF-8') . '</b></p><br>';

            // Gestion des informations d'en-tête supplémentaires pour type 2
            if ($type_activite == 2) {
                $stmt = $GLOBALS['bdd']->query('SELECT * FROM informations_entete WHERE id_activite=' . $id_activite);
                if ($stmt->rowCount() != 0) {
                    $informations_entete = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $informations_entete = $informations_entete[0];
                    // $reference = 'NS N°' . htmlspecialchars($informations_entete['reference']) . ' DU ' . htmlspecialchars($informations_entete['date2']);
                    // $reference = $resultat['reference'];
                    $pdf->Ln(20);
                } else {
                    $reference =  htmlspecialchars($data[0]['reference'] ?? 'N/A');
                    $pdf->Ln(20);
                }
                $html = '<p align="center"><b style="font-family: trebucbd;">' . $reference . ' PORTANT CONSTITUTION DES COMMISSIONS CHARGEES DE SUPERVISER LE DÉROULEMENT DES ÉPREUVES ÉCRITES DE ' . mb_strtoupper($nom_activite, 'UTF-8') . '</b></p><br>';
                $stmt->closeCursor();
            }

            $pageTotal = 0;
            $cumulativeTotal = 0;
            $i = 0;
            $linesOnPage = 0;
            $maxLinesPerPage = ($type_activite == 3) ? 6 : 20; // 6 lignes pour type 3, 10 pour types 1 et 2

            $pdf->SetFont('trebuc', '', 8); // Police non-grasse pour le tableau
            $html .= startTable($type_activite);


            if (empty($data)) {
                $html .= '<tr><td colspan="' . ($type_activite == 3 ? '11' : ($type_activite == 2 ? '9' : '8')) . '" style="text-align:center;">Aucune donnée disponible</td></tr>';
                $html .= '</tbody></table>';
                $pdf->writeHTML($html, true, false, true, false, '');
            } else {
                foreach ($data as $index => $row) {
                    $i++;
                    $linesOnPage++;
                    $pageTotal += $row['montant'];

                    // Ajouter la ligne de données
                    //$pdf->Ln(20);
                    $rowHtml = generateRow($row, $type_activite, $i);
                    $html .= $rowHtml;

                    // Vérifier si un saut de page est nécessaire (basé sur le nombre de lignes)
                    if ($linesOnPage >= $maxLinesPerPage && $index < count($data) - 1) {
                        // Ajouter "A reporter" en bas de la page (sauf pour la dernière page)
                        $html .= '
                <tr style="background-color:#f2f2f2;">
                    <td colspan="' . ($type_activite == 3 ? '8' : '5') . '" width="' . ($type_activite == 3 ? '69%' : '55%')  . '"><strong style="font-family: trebucbd;">A REPORTER :</strong></td>
                    <td width="' . ($type_activite == 3 ? '8%' : '12%') . '"><strong style="font-family: trebucbd;">' . number_format($cumulativeTotal + $pageTotal, 0, ',', '.') . ' FCFA</strong></td>
                    <td colspan="' . ($type_activite == 3 ? '2' : '2') . '" width="' . ($type_activite == 3 ? '23%' : '33%') . '"></td>
                </tr>';
                        $html .= '</tbody></table>';
                        $pdf->writeHTML($html, true, false, true, false, '');
                        $pdf->AddPage();
                        $pdf->Ln(10);
                        $cumulativeTotal += $pageTotal;
                        $pageTotal = 0;
                        $linesOnPage = 0;
                        $html = startTable($type_activite);

                        // Ajouter "Report" dans le tableau de la nouvelle page (sauf pour la première)
                        if ($pdf->getPage() > 1) {
                            $html .= '
                    <tr style="background-color:#f2f2f2;">
                        <td colspan="' . ($type_activite == 3 ? '8' : '5') . '" width="' . ($type_activite == 3 ? '69%' : '55%') . '"><strong style="font-family: trebucbd;">REPORT :</strong></td>
                        <td width="' . ($type_activite == 3 ? '8%' : '12%') . '"><strong style="font-family: trebucbd;">' . number_format($cumulativeTotal, 0, ',', '.') . ' FCFA</strong></td>
                        <td colspan="' . ($type_activite == 3 ? '2' : '2') . '" width="' . ($type_activite == 3 ? '23%' : '33%') . '"></td>
                    </tr>';
                            $linesOnPage++;
                        }
                    }

                    // Ajouter "Total de cette page" à la fin de la dernière page
                    /*
            if ($index + 1 === count($data)) {
                $html .= '
                <tr style="background-color:#f2f2f2;">
                    <td colspan="' . ($type_activite == 3 ? '8' : '5') . '" width="' . ($type_activite == 3 ? '69%' : '55%') . '"><strong style="font-family: trebucbd;">Total de cette page</strong></td>
                    <td width="' . ($type_activite == 3 ? '8%' : '12%') . '"><strong style="font-family: trebucbd;">' . number_format($pageTotal, 0, ',', '.') . ' FCFA</strong></td>
                    <td colspan="' . ($type_activite == 3 ? '2' : '2') . '" width="' . ($type_activite == 3 ? '23%' : '33%') . '"></td>
                </tr>';
            } */

                    $cumulativeTotal += $pageTotal;
                }
                $html .= '</tbody></table>';
            }

            $total = $cumulativeTotal;
            $html .= '<br><br>
        <table border="1" cellpadding="4" align="center">
            <tr style="background-color:#f2f2f2;">
                <td colspan="' . ($type_activite == 3 ? '8' : '5') . '" width="' . ($type_activite == 3 ? '69%' : '55%') . '"><strong style="font-family: trebucbd;">Total général ( )</strong></td>
                <td width="' . ($type_activite == 3 ? '8%' : '12%') . '"><strong style="font-family: trebucbd;">' . number_format($total, 0, ',', '.') . ' FCFA</strong></td>
                <td colspan="' . ($type_activite == 3 ? '2' : '2') . '" width="' . ($type_activite == 3 ? '23%' : '33%') . '"></td>
            </tr>
        </table>';

            $totalEnLettres = convertir_en_lettres($total);

            $pdf->SetFont('trebucbd', '', 10);
            $html .= '<br><p align="center"><b style="font-family: trebucbd;">Arrêté le présent état de paiement à la somme de : ' . mb_strtoupper($totalEnLettres, 'UTF-8') . ' (' . number_format($total, 0, ',', '.') . ') Francs CFA</b></p>';

            $pr_nom = htmlspecialchars($data[0]['premier_responsable'] ?? '');
            $pr_titre = htmlspecialchars($data[0]['titre_responsable'] ?? '');
            $fin_nom = htmlspecialchars($data[0]['financier'] ?? '');
            $fin_titre = htmlspecialchars($data[0]['titre_financier'] ?? '');

            $html .= '
        <br><br><br>
        <table border="0" align="center">
            <tr>
                <td style="border:none; text-align:center;">
                    <h4 style="margin-bottom:1em; font-family: trebucbd;">' . htmlspecialchars($fin_titre) . '</h4>
                    <br>
                    <h4 style="text-decoration:underline; font-family: trebucbd;">' . htmlspecialchars($fin_nom) . '</h4>
                </td>
                <td style="border:none; text-align:center;">
                    <h4 style="margin-bottom:1em; font-family: trebucbd;">' . htmlspecialchars($pr_titre) . '</h4>
                    <br>
                    <h4 style="text-decoration:underline; font-family: trebucbd;">' . htmlspecialchars($pr_nom) . '</h4>
                </td>
            </tr>
        </table>';

            $pdf->writeHTML($html, true, false, true, false, '');
            if ($navigateur) {
                ob_end_clean();
            }
        }
    }


    // Exécution pour les trois types d'activité
    if (in_array($id_type_activite, [1, 2, 3])) {
        $sql = '';
        if ($id_type_activite == 1) {
            $sql = "
            SELECT 
                p.nom AS nom_participant,
                p.prenoms,
                t.nom AS titre_participant,
                a.reference,
                a.taux_journalier,
                pa.nombre_jours,
                (a.taux_journalier * pa.nombre_jours) AS montant,
                ib.banque,
                ib.numero_compte AS rib,
                a.premier_responsable,
                a.titre_responsable,
                a.financier,
                a.titre_financier
            FROM participants p
            JOIN participations pa ON p.id_participant = pa.id_participant
            JOIN activites a ON pa.id_activite = a.id
            LEFT JOIN titres t ON pa.id_titre = t.id_titre
            LEFT JOIN informations_bancaires ib ON p.id_participant = ib.id_participant
            WHERE a.type_activite = :type_activite AND a.id = :id_activite
            ORDER BY p.nom ASC, p.prenoms ASC
            ";
        } elseif ($id_type_activite == 2) {
            $sql = "
            SELECT 
                p.nom AS nom_participant,
                p.prenoms,
                t.nom AS titre_participant,
                t.indemnite_forfaitaire,
                a.reference,
                a.taux_journalier,
                pa.nombre_jours,
                (a.taux_journalier * pa.nombre_jours + IFNULL(t.indemnite_forfaitaire, 0)) AS montant,
                ib.banque,
                ib.numero_compte AS rib,
                a.premier_responsable,
                a.titre_responsable,
                a.financier,
                a.titre_financier,
                a.reference
            FROM participants p
            JOIN participations pa ON p.id_participant = pa.id_participant
            JOIN activites a ON pa.id_activite = a.id
            LEFT JOIN titres t ON pa.id_titre = t.id_titre
            LEFT JOIN informations_bancaires ib ON p.id_participant = ib.id_participant
            WHERE a.type_activite = :type_activite AND a.id = :id_activite
            ORDER BY p.nom ASC, p.prenoms ASC
            ";
        } elseif ($id_type_activite == 3) {
            $sql = "
            SELECT 
                p.nom AS nom_participant,
                p.prenoms,
                t.nom AS titre_participant,
                t.indemnite_forfaitaire,
                a.taux_taches,
                a.reference,
                pa.nombre_taches,
                a.frais_deplacement_journalier,
                pa.nombre_jours,
                (a.taux_taches * pa.nombre_taches + IFNULL(t.indemnite_forfaitaire, 0) + a.frais_deplacement_journalier * pa.nombre_jours) AS montant,
                ib.banque,
                ib.numero_compte AS rib,
                a.premier_responsable,
                a.titre_responsable,
                a.financier,
                a.titre_financier
            FROM participants p
            JOIN participations pa ON p.id_participant = pa.id_participant
            JOIN activites a ON pa.id_activite = a.id
            LEFT JOIN titres t ON pa.id_titre = t.id_titre
            LEFT JOIN informations_bancaires ib ON p.id_participant = ib.id_participant
            WHERE a.type_activite = :type_activite AND a.id = :id_activite
            ORDER BY p.nom ASC, p.prenoms ASC
            ";
        }

        $stmt = $bdd->prepare($sql);
        $stmt->execute([
            'type_activite' => $id_type_activite,
            'id_activite' => $id_activite
        ]);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Classe personnalisée pour la numérotation des pages
        // echo 

        // if (!class_exists('MYPDF') && $navigateur) {
        //     class MYPDF extends TCPDF
        //     {
        //         public function Footer()
        //         {
        //             // $this->SetY(-15);
        //             $this->SetFont('trebucbd', '', 9); // Police grasse pour le pied de page
        //             // $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0);
        //             $this->Cell(0, 10,  $this->getAliasNumPage(), 0, false, 'R', 0);
        //         }
        //     }
        // } elseif (!class_exists('MYPDF') && !$navigateur) {
        //     class MYPDF extends TCPDF
        //     {
        //         public function Footer()
        //         {
        //             // $this->SetY(-15);
        //             // $this->SetFont('trebucbd', '', 8); // Police grasse pour le pied de page
        //             // // $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0);
        //             // $this->Cell(0, 10,  $this->getAliasNumPage(), 0, false, 'C', 0);
        //         }
        //     }
        // }

        // if ($navigateur) {
        //     echo 'bonjour 2';
        // }

        if (!class_exists('MYPDF2')) {
            class MYPDF2 extends TCPDF
            {
                public function Footer()
                {
                    // $this->SetY(-15);
                    // $this->SetFont('trebucbd', '', 9); // Police grasse pour le pied de page
                    // $this->Cell(0, 10,  $this->getAliasNumPage(), 0, false, 'R', 0);
                }
            }
        }

        // Création du PDF avec numérotation
        $pdf = new MYPDF2($id_type_activite == 3 ? 'L' : 'P', 'mm', 'A4');
        $pdf->AddFont('trebuc', '', 'trebuc.php'); // Police non-grasse
        $pdf->AddFont('trebucbd', '', 'trebucbd.php'); // Police grasse
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setMargins(15, 25, 15, true);
        configuration_pdf($pdf, $_SESSION['nom'] . ' ' . $_SESSION['prenoms'], 'Etat de paiement');
        $pdf->setAutoPageBreak(true, 25);
        $pdf->AddPage();
        $pdf->SetFont('trebucbd', '', 10);

        // Appeler la fonction generatePDF
        generatePDF($pdf, $data, $id_type_activite, $nom_activite, $id_activite, $data[0]['reference'], $navigateur);

        // global $navigateur, $dossier_exports_temp;
        if ($navigateur) {
            $pdf->Output('Etat de paiement.pdf', 'I');
        } else {
            $chemin_fichier = $dossier_exports_temp . '/Etat de paiement.pdf';;
            $pdf->Output($chemin_fichier, 'F');
            return $chemin_fichier;
        }
    } else {
        redirigerVersPageErreur(404, $_SESSION['previous_url']);
    }
}

function genererFusionPDFS($fichiers, $titre_document, $navigateur = true, $supprimerFichiers = true)
{
    global $dossier_exports_temp;
    // Classe personnalisée avec Footer()
    if ($navigateur) {
        class PDFPerso extends Fpdi
        {
            public function Footer()
            {
                // $this->SetY(-15);
                $this->SetFont('trebucbd', '', 10);
                // $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'R');
                $this->Cell(0, 10, $this->getAliasNumPage(), 0, 0, 'R');
            }
        }
    } else {
        class PDFPerso extends Fpdi
        {
            public function Footer()
            {
                // $this->SetY(-15);
                // $this->SetFont('trebucbd', '', 10);
                // $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages(), 0, 0, 'R');
                // $this->Cell(0, 10, $this->getAliasNumPage(), 0, 0, 'R');
            }
        }
    }

    $pdf = new PDFPerso();
    $pdf->setMargins(15, 25, 15);
    $pdf->setAutoPageBreak(true, 25); // marge bas = 25 pour footer
    $pdf->SetFooterMargin(25);
    $pdf->setPrintHeader(false);
    configuration_pdf($pdf, $_SESSION['nom'] . ' ' . $_SESSION['prenoms'], $titre_document);

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
    if ($navigateur) {
        $pdf->Output($titre_document . '.pdf', 'I');
    } else {
        $chemin_fichier = $dossier_exports_temp . '/' . $titre_document . '.pdf';
        $pdf->Output($chemin_fichier, 'F');
        return $chemin_fichier;
    }

    if ($supprimerFichiers) {
        // Suppression des éléments
        foreach ($fichiers as $fichier) {
            unlink($fichier);
        }
    }
}

function genererListeRIBS($id_activite, $navigateur = true)
{
    global $bdd;
    // Process : on récupère les chemins vers les fichiers rib des acteurs associés à l'activité et on fusionne ces pdfs

    $stmt = $bdd->query('
    SELECT chemin_acces
    FROM participations p1
    INNER JOIN informations_bancaires ib ON p1.id_compte_bancaire = ib.id
    INNER JOIN fichiers f ON f.id_fichier = ib.id_rib
    INNER JOIN participants p2 ON p1.id_participant = p2.id_participant
    WHERE p1.id_activite = ' . $id_activite . '
    ORDER BY p2.nom ASC
');

    $chemins = $stmt->fetchAll(PDO::FETCH_NUM);
    for ($i = 0; $i < count($chemins); $i++) {
        $chemins[$i] = $chemins[$i][0];
    }
    if ($navigateur) {
        genererFusionPDFS($chemins, 'Liste des RIBS', true, false);
    } else {
        $chemin_fichier = genererFusionPDFS($chemins, 'Liste des RIBS', false, false);
        return $chemin_fichier;
    }
}

// Fonctions de chiffrement
// Define SECRET_KEY seulement si elle n'est pas déjà définie.
// if (!defined('SECRET_KEY')) {
//     define('SECRET_KEY', require __DIR__ . '/../autres/cle.php');
// }

const METHOD = 'AES-128-CTR';

function base64url_encode($data)
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode($data)
{
    $replaced = strtr($data, '-_', '+/');
    $padding = strlen($data) % 4;
    if ($padding > 0) {
        $replaced .= str_repeat('=', 4 - $padding);
    }
    return base64_decode($replaced);
}


function chiffrer($id)
{
    // if (empty(SECRET_KEY)) {
    //     trigger_error('SECRET_KEY non définie ou vide dans Crypto.php', E_USER_ERROR);
    // }
    $iv = random_bytes(openssl_cipher_iv_length(METHOD));
    // $chiffre = openssl_encrypt($id, METHOD, SECRET_KEY, 0, $iv);
    $chiffre = openssl_encrypt($id, METHOD, $_SESSION['cle_chiffrement'], OPENSSL_RAW_DATA, $iv);
    if ($chiffre === false) {
        trigger_error('Erreur de chiffrement: ' . openssl_error_string(), E_USER_WARNING);
        return false;
    }
    return base64url_encode($iv . $chiffre);
}

function dechiffrer($valeur)
{
    // if (empty(SECRET_KEY)) {
    //     // trigger_error('SECRET_KEY non définie ou vide dans Crypto.php', E_USER_ERROR);
    // }
    $donnees = base64url_decode($valeur);
    $iv_length = openssl_cipher_iv_length(METHOD);
    $iv = substr($donnees, 0, $iv_length);
    $chiffre = substr($donnees, $iv_length);
    // Gérer le cas où $valeur ne contient pas ':'
    // if (strpos($valeur, ':') === false) {
    //     trigger_error('Format de valeur chiffrée invalide: le séparateur ":" est manquant.', E_USER_WARNING);
    //     return false;
    // }

    // [$iv_hex, $chiffre] = explode(':', $valeur, 2); // Limite à 2 pour éviter des problèmes si le chiffré contient des ':'
    // $iv = hex2bin($iv_hex);

    // Vérifier la longueur de l'IV
    // if (strlen($iv) !== openssl_cipher_iv_length(METHOD)) {
    //     trigger_error('Longueur d\'IV invalide.', E_USER_WARNING);
    //     return false;
    // }

    if (strlen($iv) !== $iv_length) {
        trigger_error('Longueur d\'IV invalide.', E_USER_WARNING);
        return false;
    }

    $dechiffre = openssl_decrypt($chiffre, METHOD, $_SESSION['cle_chiffrement'], OPENSSL_RAW_DATA, $iv);
    if ($dechiffre === false) {
        trigger_error('Erreur de déchiffrement: ' . openssl_error_string(), E_USER_WARNING);
        return false;
    }
    return $dechiffre;
}

// Autres fonctions
function quotaComptesAtteint($id)
{
    global $bdd;
    $stmt = $bdd->query("SELECT id FROM informations_bancaires WHERE id_participant =" . $id);
    return ((NOMBRE_MAXIMAL_COMPTES - $stmt->rowCount()) == 0 ? true : false);
}

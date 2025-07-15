<?php
// Constantes

define('BASE_PATH', realpath(__DIR__ . '/../'));
const NBR_ACTIVITES_A_AFFICHER = 6;
const NOMBRE_MAXIMAL_COMPTES = 3;
define('TIMEOUT', 360 * 60); // 1h d'inactivité soit 20*60 secondes.
$nom_dossier_upload = 'fichiers';
define('UPLOADS_BASE_DIR', BASE_PATH . '/' . $nom_dossier_upload);
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

function valider_id($methode, $cle, $bdd, $table = 'participants', $valeur_id = false)
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

        if (!filter_input($const_superglobale, $cle, FILTER_VALIDATE_INT)) {
            // C'est une chaîne de caractères ou tout simplement la valeur 0 que j'ai reçue
            return false;
        } else {
            $valeur = $superglobale[$cle];
        }
    } else {
        $valeur = $valeur_id;
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

function afficherSousFormeTableau($elements, $style1, $style2, $choix = true, $actions = true)
{
    // $elements : les éléments à afficher sous la forme d'un tableau. Je considère que dans $elements est constitué de deux tableaux, un pour l'entête du tableau et un second pour le body
    // $style correspond au style additionnel qu'on pourrait ajouter au tableau
    // dans actions je dois avoir l'intitulé de l'action et le lien qui permet de la réaliser dans cet ordre donc action devrait ressembler un peu à
    // [0][0]['intitule'=>'Gérer', 'lien'=>'...']
    //    [1]['intitule'=>'Gérer', 'lien'=>'...']
    // Pour la dernière action de la liste ajouter dans le tableau associatif un booléen avec comme clé 'dernier'
    // On peut ajouter du style aussi si on le souhaite dans une valeur dont la clé sera 'style'
    $head = $elements[0];
    $body = $elements[1];
    $actions = $elements[2];
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
                            <td><input type="checkbox" name="bref" id="bref"></td>
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
                            <?php $index++ ?>
                        <?php endif; ?>
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
    // si $type_mail est à 0, le mail est pour l'inscription
    // si c'est à 1, le mail est pour confirmer son email
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

        if (!$type_mail) {
            $mail->Subject = 'Activation de votre compte GPaiements';
            $mail->Body    = '
            <p>Cher(e) ' . $nom . ' ' . $prenom . ',</p>
            <p>Merci pour votre inscription sur GPaiements, la plateforme de gestion de vos activités. Nous sommes heureux de vous savoir à bord</p>
            <p>A présent, veuillez cliquez sur le lien ci-dessous pour activer votre compte et entamer l\'aventure !</p>
            <p style="text-align:center;"><a href="' . $lien_verif . '" style="text-decoration : none; color : #4e73df; font-size : 1.2rem;">Activer mon compte GPaiements</a></p>
            <p>Très chaleureusement,<br>L\'équipe de GPaiements</p>';
        } else {
            $mail->Subject = 'Confirmation de votre adresse email';
            $mail->Body = '
            <p>Plus q\'un clic pour actualiser votre adresse mail</p>
            <p><a href="' . $lien_verif . '" style="text-decoration : none; color : #4e73df;">Confirmer mon adresse</a></p>
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
        <?php if($dismissible) : ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        <?php endif; ?>
    </div>
<?php
}

function traiterCheminAcces($chemin)
{
    // Cette fonction est là pour m'aider à retrouver le lien en relatif à partir du lien en absolu. Elle va donc tout simplement couper le chemin d'accès à partir de 'fichiers' et le reste me donnera le chemin d'accès en relatif
    $motCle = $GLOBALS['nom_dossier_upload'];
    return strstr($chemin, $motCle);
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

function genererHeader($pdf, $type_document, $informations)
{

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

    $titres = [
        'ordre_virement' => 'des indemnités et frais d\' entretien accordés aux membres de la commission chargée de',
        'note_service' => 'portant constitution des membres de la commission chargée de',
        'etat_paiement_1' => 'des indemnités et frais d\'entretien accordés aux membres de la commission chargée de',
        'etat_paiement_2' => 'indemnités et frais d\'entretien accordés aux membres de la commission chargée de la correction des examens de',
        'etat_paiement_3' => 'des indemnités et frais d\'entretien accordés aux membres d\'encadrement dans le cadre'
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
    $bloc_gauche =  strtoupper("REPUBLIQUE DU BENIN\n*-*-*-*-*\nMINISTERE DE L'ENSEIGNEMENT SUPERIEUR ET SECONDAIRE\n*-*-*-*-*\nDIRECTION DES ............\n*-*-*-*-*\nSERVICE ............");
    $pdf->setXY($pdf->getMargins()['left'], $y);
    $pdf->MultiCell($largeurBloc, 5, $bloc_gauche, 0, 'C');

    // Gestion du bloc de droite (sur la même ligne que le bloc de gauche)
    $pdf->setXY($x, $y); // Déplacement du curseur à la bonne position

    // Ligne 1 : date
    $ligne1 = strtoupper("Cotonou, le " . $formatter->format(new DateTime()));
    $pdf->Cell(0, 5, $ligne1, 0, 1, 'C');
    $pdf->Ln(5);

    // Ligne 2 : Titre du document
    if ($type_document == 'ordre_virement') {
        $ligne2 = mb_strtoupper('ordre de virement ' . $informations['banque'], 'UTF-8');
    } elseif ($type_document == 'note_service') {
        $ligne2 = 'NOTE DE SERVICE';
    } elseif ($type_document == 'attestation_collective') {
        $ligne2 = 'ATTESTATION COLLECTIVE DE TRAVAIL';
    } elseif ($type_document == 'etat_paiement') {
    }

    $pdf->setFont('trebucbd', '', '11');
    $pdf->setX($x);
    $pdf->Cell(0, 5, $ligne2, 0, 1, 'C');
    $pdf->Ln(5);

    // Ligne 3
    $ligne3 = mb_strtoupper($titres[$type_document] . ' ' . $informations['titre'], 'UTF-8');
    $pdf->setFont('trebuc', '', '10');
    $pdf->setX($x);
    $pdf->MultiCell($largeurBloc, 5, $ligne3, 0, 'C');
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

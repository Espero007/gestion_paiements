<?php
const NBR_ACTIVITES_A_AFFICHER = 6;
const NOMBRE_MAXIMAL_COMPTES = 3;
// const RACINE_PROJET = __DIR__.'/..';

// echo RACINE_PROJET;

// require_once(RACINE_PROJET.'/auth/connexion.php');

// Gestion du timezone pour qu'il s'adapte au Bénin

date_default_timezone_set('Africa/Lagos');

function redirigerVersPageErreur($code_erreur, $url)
{
    $_SESSION['previous_url'] = $url;
    $_SESSION['code_erreur'] = $code_erreur;
    header('location:/page_erreur.php');
    exit;
}

function creer_dossiers_upload($repertoire_racine, $permissions)
{
    // Création des dossier s'ils n'existent pas

    $upload_annee = $repertoire_racine . "/" . date("Y");
    $upload_mois = $upload_annee . "/" . date("m");
    $upload_dirs = array($repertoire_racine, $upload_annee, $upload_mois);

    foreach ($upload_dirs as $dir) {
        if (!is_dir($dir)) {
            // Le dossier n'existe pas
            if (!mkdir($dir, $permissions)) {
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

function valider_id_participant($valeur, $bdd, $current_url){
    $valeur = intval($valeur);

    if($valeur == 0){
        // C'est une chaîne de caractères ou tout simplement la valeur 0 que j'ai reçue
        return false;
    }else{
        $stmt = "SELECT id_participant FROM participants WHERE id_participant=".$valeur." AND id_user=".$_SESSION['user_id'];
        $resultat = $bdd->query($stmt);
        if(!$resultat){
            // Une erreur s'est produite lors de la récupération
            redirigerVersPageErreur(500, $current_url);
        }else{
            if($resultat->rowCount() == 0){
                return false;
            }
            $resultat->closeCursor();
            return true;
        }

    }
}
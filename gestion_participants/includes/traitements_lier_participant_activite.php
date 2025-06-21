<?php

// Je vais partir du principe que j'ai choisi le participant et que la première chose à faire sur cette page sera de choisir l'activité à laquelle je veux l'associer. Pour l'instant on ne fera la liaison qu'avec une activté à la fois mais quel affichage mettre en place pour les activités ?

// Validations
$redirect = true;

// Je suis supposé recevoir l'id du participant sélectionné donc on va partir dessus et la valider
if (valider_id('get', 'id_participant', $bdd, 'participants')) {
    // La valeur indiquée est bien retrouvée dans la table 'participants' donc pas besoin de redirection je présume.
    $id_participant = $_GET['id_participant'];

    // A présent il faut que je prenne toutes les activités créées par l'utilisateur pour les afficher
    $stmt = $bdd->prepare('SELECT nom, date_debut, date_fin, description FROM activites WHERE id_user ='.$_SESSION['user_id']);
    $stmt->execute();
    $activites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(isset($_GET['id_activite'])){
        if(valider_id('get', 'id_activite', $bdd, 'activites')){
            // L'activite est présente et valide donc on poursuit le game
            $id_activite = $_GET['id_activite'];
            // $stmt = $bdd->prepare('SELECT')
            $redirect = false;

            // On récupère les titres associés à l'activité actuelle
            $stmt = $bdd->prepare('SELECT id_titre, nom FROM titres WHERE id_activite='.$id_activite);
            $stmt->execute();
            $titres = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // On récupère aussi les comptes bancaires du participant
            $stmt = $bdd->prepare('SELECT id, banque, numero_compte FROM informations_bancaires WHERE id_participant='.$id_participant);
            $stmt->execute();
            $comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($comptes as $index => $compte) {
                $id_comptes[] = $compte['id'];
            }
        }
    }else{
        $redirect = false;
    }
}

if($redirect){
    header('location:voir_participants.php');
    exit;
}

// On entame les validations pour achever la liaison
$champs_attendus = ['titre', 'compte_bancaire'];

if (isset($_POST['lier'])) {
    foreach ($champs_attendus as $champ) {
        if(!isset($_POST[$champ])){
            $erreurs[$champ][] = "Veuillez sélectionner une valeur";
        }else{
            $valeur = $_POST[$champ];
            // Les champs sont présents
            if($champ == 'titre'){
                // On s'assure que le titre indiqué est bien dans les titres attendus
                if(!in_array($valeur, $titres)){
                    $erreurs[$champ] = "Le titre que vous avez choisi n'est pas valide";
                }
            }else if($champ == 'compte_bancaire'){
                // On s'assure que le compte indiqué est bien présent dans les comptes bancaires récupérés en bdd
                if(!in_array($valeur, $id_comptes)){
                    $erreurs[$champ] = "Le compte bancaire sélectionné n'est pas valide";
                }
            }
        }
    }
}



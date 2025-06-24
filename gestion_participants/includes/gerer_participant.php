<?php

// On vérifie la présence de l'id du participant à gérer et si il n'est pas présent on redirige vers 'voir_participants.php'

$redirect = true;
if (filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)) {
    // On vérifie la présence de l'id indiqué dans la table activités
    $stmt = $bdd->prepare('SELECT * FROM participants WHERE id_participant=' . $_GET['id'] . ' AND id_user=' . $_SESSION['user_id']);
    $stmt->execute();
    $participant = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($participant) != 0) {
        $participant = $participant[0];
        $id_participant = $participant['id_participant'];
        $redirect = false;
    }
}

if ($redirect) {
    header('location:voir_participants.php');
    exit;
}

// Informations à afficher

$infos = [
    'nom' => "Nom",
    "prenoms" => "Prénom(s)",
    "matricule_ifu" => "Matricule/IFU",
    "date_naissance" => "Date de naissance",
    "lieu_naissance" => "Lieu de naissance",
];

// Informations bancaires
$stmt = $bdd->prepare("SELECT banque, numero_compte FROM informations_bancaires WHERE id_participant=" . $id_participant);
$stmt->execute();
$comptes = $stmt->fetchAll(PDO::FETCH_NUM);


// Merge avec $infos
$index = 0;
foreach ($comptes as $compte) {
    $index++;
    $infos['compte_' . $index][] = $compte[0]; // Banque
    $infos['compte_' . $index][] = $compte[1]; // numéro de compte, rib probablement
}

// Vérification du nombre de comptes associé au participant pour afficher ou non l'option d'ajout de comptes

$stmt = $bdd->prepare("SELECT id FROM informations_bancaires WHERE id_participant = :val");
$stmt->bindParam(':val', $id_participant, PDO::PARAM_INT);
$stmt->execute();
// $resultats = $stmt->fetchAll(PDO::FETCH_NUM);
// $nombre_comptes_existants = $stmt->rowCount();

$quota_comptes_bancaires_atteint = (NOMBRE_MAXIMAL_COMPTES - $stmt->rowCount()) == 0 ? true : false; // avec $stmt->rowCount() donnant le nombre de lignes retrouvé dans la table
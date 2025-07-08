<?php

// On vérifie la présence de l'id de l'activité à gérer et si elle n'est pas présente on redirige vers la page précédente
$redirect = true;

if (filter_input(INPUT_GET, 'id_activite', FILTER_VALIDATE_INT)) {
    // On s'assure d'abord que l'activité en question a des participants associés, soit elle est présente dans la table participations
    $stmt = $bdd->prepare('SELECT id FROM participations WHERE id_activite=' . $_GET['id_activite']);
    $stmt->execute();
    $resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(count($resultat) != 0){
        if(valider_id('', '', $bdd, 'activites', $_GET['id_activite'])){
            $id_activite = $_GET['id_activite'];
            $redirect = false;
        }
    }
}

if ($redirect) {
    header('location:' . $_SESSION['previous_url']);
    exit;
}

// Arrivé ici on a une activité valide

$documents = [
    'note_service' => 'Note de service',
    'attestation_collective' => 'Attestation collective de travail',
    'etat_paiement' => 'Etat de paiement',
    'synthese_ordres_virements' => 'Synthèse des ordres de virements',
    'liste_rib'
];
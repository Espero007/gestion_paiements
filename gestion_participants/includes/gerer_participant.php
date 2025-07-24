<?php

if (!valider_id('get', 'id', '')) {
    redirigerVersPageErreur();
}

$id_participant = dechiffrer($_GET['id']);
$stmt = $bdd->query('SELECT * FROM participants WHERE id_participant=' . $id_participant);
$participant = $stmt->fetch(PDO::FETCH_ASSOC);
$stmt->closeCursor();

// Informations à afficher

$infos = [
    'nom' => "Nom",
    "prenoms" => "Prénom(s)",
    "matricule_ifu" => "Matricule IFU",
    "date_naissance" => "Date de naissance",
    "lieu_naissance" => "Lieu de naissance",
    "diplome_le_plus_eleve" => "Diplôme le plus élevé"
];


// Informations bancaires
$stmt = $bdd->prepare("
SELECT ib.banque, ib.numero_compte, f.chemin_acces 
FROM informations_bancaires ib 
INNER JOIN fichiers f ON ib.id_rib=f.id_fichier
WHERE id_participant=" . $id_participant);
$stmt->execute();
$comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$index = 0;
$comptes_str = '';
foreach ($comptes as $compte) {
    $index++;
    $comptes_str .= '<a href="/' . traiterCheminAcces($compte['chemin_acces']) . '" target="_blank">' . $compte['banque'] . ' (<strong>' . $compte['numero_compte'] . '</strong>)</a>';
    if ($index != count($comptes)) {
        $comptes_str .= ', ';
    }
}

// Vérification du nombre de comptes associé au participant pour afficher ou non l'option d'ajout de comptes

$quota_comptes_bancaires_atteint = quotaComptesAtteint($id_participant);

// Récupération des activités auxquelles le participant est déjà associé
$stmt = $bdd->query('
SELECT p1.id, p1.id_activite, a.id as id_activite, a.nom, a.description, a.type_activite, t.nom as titre, p1.nombre_jours as nbr_jours, p1.nombre_taches as nbr_taches, ib.banque, ib.numero_compte
FROM participations p1
INNER JOIN activites a ON p1.id_activite = a.id
INNER JOIN titres t ON t.id_titre = p1.id_titre
INNER JOIN informations_bancaires ib ON p1.id_compte_bancaire = ib.id
WHERE p1.id_participant=' . $id_participant);

$activites_associees = $stmt->fetchAll(PDO::FETCH_ASSOC);
$compteur = 0;
$type_3 = false;
foreach ($activites_associees as $activite) {
    if ($activite['type_activite'] == 3) {
        $type_3 = true;
    }
}

if (count($activites_associees) != 0) {
    $informations[0] = ['Titre de l\'activité', 'Titre', 'Nombre de jours'];
    if ($type_3)
        $informations[0][] = 'Nombre de tâches';
    $informations[0][] = 'Compte bancaire';

    foreach ($activites_associees as $activite) {
        $informations[1][] = [$activite['nom'], $activite['titre'], $activite['nbr_jours']];
        if ($type_3)
            $informations[1][count($informations[1]) - 1][] = $activite['nbr_taches'];
        $informations[1][count($informations[1]) - 1][] = $activite['banque'] . ' (' . $activite['numero_compte'] . ')';

        // Actions possibles
        $informations[2][$compteur][] = [
            'intitule' => 'Modifier',
            'lien' => '/gestion_participants/liaison.php?modifier=' . chiffrer($activite['id']) . '&s=0'
        ];
        $informations[2][$compteur][] = [
            'intitule' => 'Gérer l\'activité',
            'lien' => '/gestion_activites/gerer_activite.php?id=' . chiffrer($activite['id_activite'])
        ];
        $informations[2][$compteur][] = [
            'intitule' => 'Rompre la liaison',
            'lien' => '#',
            'style' => 'text-danger',
            'dernier' => true,
            'modal' => true,
            'id_modal' => '#ruptureLiaison',
            'id' => chiffrer($activite['id'])
        ];
        $compteur++;
    }
}

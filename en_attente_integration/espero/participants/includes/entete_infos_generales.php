<?php

// Dans ces tableaux, j'indique le champ attendu, la valeur à mettre pour son label et la valeur à mettre pour son placeholder dans cet ordre. Cela pour automatiser de création des groupes et pour alléger le fichier principal qui va affciher le formulaire

$informations_generales = [
    "nom" => ["Nom", "Entrez le nom"],
    "prenoms" => ["Prénom(s)", "Entrez le(s) prénom(s)"],
    "matricule_ifu" => ["Matricule/IFU", "Entrez le matricule ou l'IFU"],
    "date_naissance" => ["Date de naissance", ""],
    "lieu_naissance" => ["Lieu de naissance", "Entrez le lieu de naissance"]
];

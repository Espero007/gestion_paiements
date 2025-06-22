<?php

// Définition de constantes

const BDD_HOST = 'localhost';
// const MYSQL_PORT = 3306;
const BDD_NAME = 'gestion_paiements';
const BDD_USER = 'root';
const BDD_PASSWORD = '';

<<<<<<< HEAD
qualite
=======

>>>>>>> e12d5757bb365b7b037ae4ba343c4f948fc300dc
try {
    // Connexion au serveur MySQL
    $bdd = new PDO('mysql:host=' . BDD_HOST, BDD_USER, BDD_PASSWORD);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Création de la base de données
    $stmt = "CREATE DATABASE IF NOT EXISTS `" . BDD_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
    $bdd->exec($stmt);

    //echo "Base de données créé avec succès.<br>";

} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}

// Si on est ici c'est que la base de données a été créée avec succès donc on peut s'y connecter et créer les tables qu'elle est supposée contenir$

// $dsn = 'mysql:host=' . BDD_HOST . ';dbname=' . BDD_NAME . ';';

try {
    $bdd = new PDO('mysql:host=' . BDD_HOST . ';dbname=' . BDD_NAME . ';', BDD_USER, BDD_PASSWORD);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Création des tables

    $sqlTables = "

        CREATE TABLE IF NOT EXISTS activites
        (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        type_activite INT NOT NULL,
        id_user INT NOT NULL,
        nom VARCHAR(100) NOT NULL,
        description VARCHAR(255) NOT NULL,
        date_debut DATE NOT NULL,
        date_fin DATE NOT NULL,
        centre VARCHAR(100) NOT NULL,
        premier_responsable VARCHAR(100) NOT NULL,
        titre_responsable VARCHAR(100) NULL,
        organisateur VARCHAR(100) NOT NULL,
        titre_organisateur VARCHAR(100) NULL,
        financier VARCHAR(100) NOT NULL,
        titre_financier VARCHAR(100) NULL,
        id_note_generatrice INT NOT NULL,
        taux_journalier DECIMAL(50) NOT NULL,
        frais_deplacement_journalier DECIMAL(50) NULL,
        taux_taches DECIMAL(50) NULL
        );

        CREATE TABLE IF NOT EXISTS connexion
        (
        user_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        prenoms VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(100) NOT NULL,
        token_verification VARCHAR(255) NOT NULL,
        est_verifie BOOLEAN DEFAULT FALSE
        );

        CREATE TABLE IF NOT EXISTS diplomes
        (
        id_diplome INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        id_activite INT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS fichiers
        (
        id_fichier INT NOT NULL AUTO_INCREMENT PRIMARY KEY, chemin_acces VARCHAR(100) NOT NULL,
        nom_original VARCHAR(100) NOT NULL,
        date_upload DATE NOT NULL,
        type_fichier VARCHAR(50) NOT NULL
        );

        CREATE TABLE IF NOT EXISTS informations_bancaires
        (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        id_participant INT NOT NULL,
        banque VARCHAR(100) NOT NULL,
        numero_compte VARCHAR(100) UNIQUE NOT NULL,
        id_rib INT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS participants
        (
        id_participant INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        id_user INT NOT NULL,
        nom VARCHAR(100) NOT NULL,
        prenoms VARCHAR(100) NOT NULL,
        matricule_ifu VARCHAR(100) UNIQUE NOT NULL,
        date_naissance DATE NOT NULL,
        lieu_naissance VARCHAR(100) NOT NULL,
        id_diplome INT NOT NULL
        );

        CREATE TABLE IF NOT EXISTS participations
        (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        id_participant INT NOT NULL,
        id_activite INT NOT NULL,
        id_titre INT NOT NULL,
        id_compte_bancaire INT NOT NULL,
        nombre_jours INT NULL,
        nombre_taches INT NULL
        );

        CREATE TABLE IF NOT EXISTS titres
        (
        id_titre INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        id_activite INT NOT NULL,
        indemnite_forfaitaire VARCHAR(100) NULL
        );
    ";

    $bdd->exec($sqlTables);

    //echo "Tables créées avec succès.";

} catch (PDOException $e) {
    echo "Echec lors de la connexion : " . $e->getMessage();
}

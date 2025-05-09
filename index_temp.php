<?php
session_start();
require_once(__DIR__ . '/includes/bdd.php');

// $loggedUser = $_SESSION['loguser'];
// $user_id = $loggedUser['user_id'];

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link rel="stylesheet" href="./bootstrap-5.3.5-dist/css/bootstrap.min.css">
    <!-- Custom styles for this template-->
    <!-- <link href="./sb-admin-2.min.css" rel="stylesheet"> -->
</head>

<body>

    <div class="container-md">
        <h4 class="my-4">Actions</h4>
        <hr>
        <button class="btn btn-primary">Créer une activité</button>
        <form action="auth/deconnexion.php" method="post" class="d-inline">
            <button class="btn btn-primary">Se déconnecter</button>
        </form>

        <h4 class="my-4">Activités créées</h4>
        <hr>

    </div>

    <?php

    // Récuperer les activités créées par l'utilisateur connecté

    $stmt = $bdd->prepare("SELECT * FROM activites WHERE id_user = :id"); // stmt pour statement
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $resultats = $stmt->fetchAll();


    ?>

    <script src="./bootstrap-5.3.5-dist/js/bootstrap.min.js"></script>
</body>

</html>
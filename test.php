<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/assets/bootstrap-5.3.5-dist/css/bootstrap.min.css">
</head>

<body>

    <?php
    $email = $_GET['email'];
    $stmt = $bdd->prepare('SELECT nom, email FROM informations WHERE email=:email');
    $stmt->execute([':email' => $email]);
    $infos = $stmt->fetch(PDO::FETCH_ASSOC);
    $infos->closeCursor();

    if (count($infos) != 0) {
        // Il y a une correspondance
        echo "Le nom de l'utilisateur est " . $infos['nom'] . " et son email est " . $infos['email'];
    } else {
        echo "L'email indiqué ne correspond à aucun utilisateur";
    }
    ?>
</body>

<!-- <div class="container-md">
    <div class="alert alert-danger mt-4 text-center">
        Voici une information
    </div>
</div> -->

</html>
<?php
require_once(__DIR__ . '/includes/bdd.php') ?>
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
    $id_participant = 3433;
    $stmt = $bdd->prepare('SELECT reference_carte_identite FROM participants WHERE id_participant=' . $id_participant);
    $stmt->execute();
    $reference_acteur = $stmt->fetch(PDO::FETCH_NUM)[0];
    var_dump($reference_acteur);


    ?>



</body>

</html>
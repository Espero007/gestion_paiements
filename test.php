<?php
session_start();
require_once('includes/constantes_utilitaires.php');
require_once('includes/bdd.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/assets/bootstrap-5.3.5-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/sb-admin-2.min.css">
</head>

<body>
<?php $stmt = $bdd->query('SELECT centre FROM activites WHERE id=4');
$resultat = $stmt->fetchAll(PDO::FETCH_ASSOC);
$informations_activite = $resultat[0];
?>
    <pre><?php var_dump($informations_activite);?></pre>
<?php
?>

</body>

</html>
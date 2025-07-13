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

</body>

<?php
if (!valider_id('get', 'id', $bdd, 'participations_activites')) {
    redirigerVersPageErreur(404, $_SESSION['previous_url']);
}
?>

<div id="content" class="d-none">
    Mon contenu principal
</div>

</html>
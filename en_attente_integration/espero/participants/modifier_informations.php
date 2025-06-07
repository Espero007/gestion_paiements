<?php require_once('submit_modifier_infos.php') ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modification des informations</title>
    <link rel="stylesheet" href="../../../assets/bootstrap-5.3.5-dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container-md">
        <?php if (isset($message_succes)) : ?>
            <div class="alert alert-success mt-2">Les informations du participant ont été modifiées avec succès !</div>
        <?php endif; ?>
        <h4 class="my-4">Modification des infos du participant</h4>
        <hr>
        <form action="" method="post" enctype="multipart/form-data">
            <!-- Fieldset : Informations générales début -->
            <?php require_once('includes/fieldsets.php') ?>
            <!-- Fieldset : Informations générales fin -->
            <hr>

            <button type="submit" name="modifier_infos" class="btn btn-primary mt-5 mb-4">Modifier les informations</button>
            <br>
            <a href="index.php" class="btn btn-primary mb-4">Revenir à la page principale</a>
        </form>
    </div>

</body>

</html>
<?php require_once('submit_ajout_participant.php') ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajout du participant</title>
    <link rel="stylesheet" href="../../../assets/bootstrap-5.3.5-dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container-md">

        <?php
        if (isset($_SESSION['comptes_ajoutes'])) {
        ?>
            <div class="alert alert-success mt-2">Le(s) compte(s) bancaire(s) a(ont) été ajouté(s) avec succès !</div>
        <?php
            unset($_SESSION['comptes_ajoutes']);
        }
        ?>

        <?php if (isset($message_succes)) : ?>
            <div class="alert alert-success mt-2">Le participant a été enregistré avec succès ! <a href="./ajouter_comptes.php?id_participant=<?php echo $id_participant; ?>">Cliquez ici</a> si vous souhaitez lui ajouter des comptes bancaires.</div>
        <?php endif; ?>

        <h4 class="my-4">Page d'ajout d'un participant</h4>
        <hr>

        <form action="" method="post" enctype="multipart/form-data">

            <!-- Fieldset : Informations générales début -->
            <?php require_once('includes/fieldset_infos_generales.php') ?>
            <!-- Fieldset : Informations générales fin -->
            <hr>
            <?php require_once('includes/fieldset_informations_bancaires.php') ?>

            <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $taille_admissible_fichiers_pdf; ?>">
            <button type="submit" name="ajouter_participant" class="btn btn-primary mt-5 mb-4">Ajouter le participant</button>
            <br>
            <a href="index.php" class="btn btn-primary mb-4">Revenir à la page principale</a>
        </form>

    </div>

</body>

</html>
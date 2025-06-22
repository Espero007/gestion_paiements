<?php require_once('submit_ajouter_comptes.php') ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajout de comptes bancaires</title>
    <link rel="stylesheet" href="../../../assets/bootstrap-5.3.5-dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container-md">
        <h4 class="my-4">Page d'ajout de comptes bancaires</h4>

        <?php if (isset($quota_comptes_bancaires_atteint) && $quota_comptes_bancaires_atteint) : ?>
            <hr>
            <p>Il semble que vous avez atteint le nombre maximal de comptes bancaires permis (<?php echo NOMBRE_MAXIMAL_COMPTES; ?>) pour ce participant.</p>

            <a href="index.php" class="btn btn-primary mb-4">Revenir à la page principale</a>

        <?php else: ?>
            <?php if ($recuperer_nbr_comptes_bancaires) : ?>

                <h5>Dîtes nous...</h5>
                <hr>
                <p>Combien de comptes bancaires voulez-vous ajouter au participant ? Il en a déjà (<strong><?php echo $nombre_comptes_existants; ?></strong>) et ne peut en avoir que (<strong><?php echo NOMBRE_MAXIMAL_COMPTES; ?></strong>) au total.</p>

                <form action="" method="get">
                    <div class="mb-2 row">
                        <label for="nombre_comptes_bancaires" class="col-sm-5 col-form-label">Nombre de comptes à ajouter : </label>
                        <div class="col-sm-7">
                            <input type="hidden" name="id_participant" value="<?php echo $id_participant; ?>">
                            <input type="number" name="nombre_comptes_bancaires" id="nombre_comptes_bancaires" class="form-control" placeholder="Indiquez le nombre" max="<?php echo $nombre_comptes_bancaires_permis; ?>" min="1" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary my-2">Continuer</button>
                </form>

            <?php else: ?>
                <form action="" method="post" enctype="multipart/form-data">
                    <?php require_once('includes/fieldset_informations_bancaires.php') ?>

                    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $taille_admissible_fichiers_pdf; ?>">
                    <button type="submit" name="ajouter_comptes" class="btn btn-primary mt-5 mb-4">Ajouter le(s) compte(s)</button>
                    <br>
                    <a href="index.php" class="btn btn-primary mb-4">Revenir à la page principale</a>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>

</html>
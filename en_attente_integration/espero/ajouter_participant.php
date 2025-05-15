<?php require_once('submit_ajout_participant.php') ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajout du participant</title>
    <link rel="stylesheet" href="../../assets/bootstrap-5.3.5-dist/css/bootstrap.min.css">
</head>

<body>
    <div class="container-md">

        <h4 class="my-4">Page d'ajout d'un participant</h4>
        <hr>

        <form action="" method="post">

            <!-- Fieldset : Informations générales début -->
            <fieldset>
                <legend>
                    <h5>Informations générales</h5>
                </legend>

                <!-- Début Nom -->
                <div class="mb-2 row">
                    <label for="nom" class="col-sm-3 col-form-label">Nom</label>

                    <div class="col-sm-9">
                        <input
                            type="text"
                            name="nom"
                            maxlength="100"
                            id="nom"
                            class="
                            form-control
                            <?php if (isset($erreurs['nom'])) {
                                echo "is-invalid";
                            } ?>
                            "
                            aria-describedby="nomAide"
                            placeholder="Entrez le nom">

                        <?php if (isset($erreurs['nom'])) : ?>
                            <div id="nomAide" class="form-text">
                                <?php echo $erreurs['nom'][0]; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Fin Nom -->

                <!-- Début Prénom(s) -->
                <!-- Fin Prénom(s) -->

            </fieldset>
            <!-- Fieldset : Informations générales fin -->
            <hr>
            <!-- Fieldset : Informations bancaires début -->
            <fieldset>
                <legend>
                    <h5>Informations bancaires</h5>
                </legend>
            </fieldset>
            <!-- Fieldset : Informations bancaires fin -->

            <button type="submit" name="ajouter_participant" class="btn btn-primary mt-5 mb-4">Ajouter le participant</button>
            <br>
            <a href="index.php" class="btn btn-primary mb-4">Revenir à la page principale</a>
        </form>

    </div>

</body>

</html>
<?php require_once(__DIR__ . "/submit/submit_compte.php"); ?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page d'inscription</title>
    <link rel="stylesheet" href="assets/bootstrap-5.3.5-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="custom_style.css">
</head>

<body>
    <!-- Helpers -->


    <div class="container-xxl">
        <!-- Outer Row -->
        <div class="row justify-content-center">
            <div class="col-md-6 col-sm-10">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row justify-content-center">
                            <div class="col-12">
                                <div class="p-5">
                                    <?php
                                    if (isset($echec_inscription)) {
                                    ?>
                                        <div class="alert alert-danger">Echec de la connexion ! Assurez-vous d'indiquer des informations valides !</div>
                                    <?php
                                    }
                                    ?>

                                    <?php
                                    if (isset($echec_enregistrement_donnees)) {
                                    ?>
                                        <div class="alert alert-danger">Une erreur s'est produite lors de l'enregistrement des données ! Réeassayez ultérieurement.</div>
                                    <?php
                                    }
                                    ?>
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Bienvenu(e) parmi nous !</h1>
                                        <p>Faîtes de la gestion des paiements de vos participants un jeu avec nous.</h2>
                                    </div>

                                    <form action="" method="post">
                                        <div class="form-group">
                                            <label for="nom" class="col-form-label form-label">Nom</label>
                                            <input type="text" name="nom" id="nom" aria-describedby="nomHelp" placeholder="Entrez votre nom" class="form-control 
                                                <?php if (isset($erreurs["nom"])) {
                                                    echo "is-invalid";
                                                } ?>"
                                                <?php if (isset($erreurs)) {
                                                    echo "value = \"" . $_POST["nom"] . "\"";
                                                } ?>>

                                            <?php if (isset($erreurs["nom"])) {
                                            ?>
                                                <div id="nomHelp" class="form-text"><?php echo $erreurs["nom"] ?></div>
                                            <?php } ?>
                                        </div>

                                        <div class="form-group">
                                            <label for="prenoms" class="col-form-label form-label">Prénoms</label>
                                            <input type="text" name="prenoms" id="prenoms" aria-describedby="prenomsHelp" placeholder="Entrez vos prénoms" class="form-control 
                                                <?php if (isset($erreurs["prenoms"])) {
                                                    echo "is-invalid";
                                                } ?>"
                                                <?php if (isset($erreurs)) {
                                                    echo "value = \"" . $_POST["prenoms"] . "\"";
                                                } ?>>

                                            <?php if (isset($erreurs["prenoms"])) {
                                            ?>
                                                <div id="prenomsHelp" class="form-text"><?php echo $erreurs["prenoms"] ?></div>
                                            <?php } ?>


                                        </div>

                                        <div class="form-group">
                                            <label for="email" class="col-form-label form-label">email</label>
                                            <input type="email" name="email" id="email" aria-describedby="emailHelp" placeholder="Entrez votre adresse email" class="form-control 
                                            <?php if (isset($erreurs["email"])) {
                                                echo "is-invalid";
                                            } ?>"
                                                <?php if (isset($erreurs)) {
                                                    echo "value = \"" . $_POST["email"] . "\"";
                                                } ?>>

                                            <?php if (isset($erreurs["email"])) {
                                            ?>
                                                <div id="emailHelp" class="form-text"><?php echo $erreurs["email"] ?></div>
                                            <?php } ?>


                                        </div>

                                        <div class="form-group form-password-toggle">
                                            <label for="password" class="col-form-label form-label">Mot de passe</label>

                                            <input type="text" class="form-control
                                                 <?php if (isset($erreurs["password"])) {
                                                        echo "is-invalid";
                                                    } ?>" id="password" name="password" placeholder="Entrez votre mot de passe" aria-describedby="passwordHelp">

                                            <?php if (isset($erreurs["password"])) {
                                            ?>
                                                <div id="passwordHelp" class="form-text"><?php echo $erreurs["password"] ?></div>
                                            <?php } ?>
                                        </div>

                                        <div class=" mt-4 mb-3">
                                            <button class="btn btn-primary w-100" type="submit" name="inscription">S'inscrire</button>
                                        </div>
                                    </form>
                                    <hr>
                                    <div class="text-center">
                                        <span>Déjà un compte sur la plateforme ?</span>
                                        <a href="connexion.php" class="is-primary">Connectez-vous</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php require_once(__DIR__ . '/../includes/footer.php') ?>
</body>

</html>
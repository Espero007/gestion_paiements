<?php
require_once(__DIR__ . '/../includes/bdd.php');
require_once(__DIR__ . '/../includes/constantes_utilitaires.php');
require_once("submit/submit_connexion.php");
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page de connexion</title>
    <link rel="stylesheet" href="../assets/bootstrap-5.3.5-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css">
    <link rel="stylesheet" href="custom_style.css">
    <script src="assets/vendor/js/helpers.js"></script>
</head>

<body>
    <!-- Helpers -->
    <div class="container-xxl mt-4">
        <!-- Outer Row -->
        <div class="row justify-content-center">
            <div class="col-md-6 col-sm-10">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row justify-content-center">
                            <div class="col-12">
                                <div class="p-5">
                                    <!-- Messages divers -->
                                    <?php
                                    if (isset($echec_connexion)) {
                                    ?>
                                        <div class="alert alert-danger text-center">Echec de la connexion ! Assurez-vous d'indiquer correctement vos identifiants de connexion !</div>
                                    <?php
                                    }
                                    ?>
                                    <?php
                                    if (isset($_SESSION['email_envoye'])) {
                                    ?>
                                        <div class="alert alert-info text-center">Un lien de vérification a été envoyé à votre mail. Confirmez le pour accéder à votre compte.</div>
                                    <?php
                                        unset($_SESSION['email_envoye']);
                                    }
                                    ?>

                                    <?php
                                    if (isset($_SESSION['deconnexion']) && !isset($_SESSION['timeout_atteint'])) {
                                        // echo "bonjour";
                                        session_unset(); // On détruit les varaibles de la session
                                        session_destroy(); // On détruit la session
                                    ?>
                                        <div class="alert alert-success text-center">Vous êtes à présent déconnecté(e) !</div>
                                    <?php
                                    } elseif (isset($_SESSION['deconnexion']) && isset($_SESSION['timeout_atteint'])) {
                                        // déconnexion due au timeout
                                    ?>
                                        <div class="alert alert-info text-center">Vous avez été déconnecté(e) pour cause d'inactivité. Veuillez vous reconnecter avant de poursuivre.</div>
                                    <?php
                                        session_unset();
                                        session_destroy();
                                    }
                                    ?>
                                    <?php if (isset($email_non_valide)) : ?>
                                        <div class="alert alert-info text-center">Votre email n'a pas encore été confirmé. Veuillez consulter votre boite mail ou <a href="<?= 'renvoyerLienConfirmation.php?email=' . $_POST['email'] ?>">renvoyer un lien de confirmation</a> si vous n'avez pas reçu de lien.</div>
                                    <?php endif; ?>

                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Contents de vous revoir !</h1>
                                        <p>Connectez-vous à votre compte pour retourner à la gestion de vos activités.</h2>
                                    </div>
                                    <form action="" method="post">
                                        <div class="form-group">
                                            <label for="email" class="col-form-label form-label">email</label>
                                            <input type="email" name="email" id="email" aria-describedby="emailHelp" placeholder="Entrez votre adresse email..." class="form-control 
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

                                            <div class="input-group input-group-merge">
                                                <input type="password" class="form-control
                                                 <?php if (isset($erreurs["password"])) {
                                                        echo "is-invalid";
                                                    } ?>" id="password" name="password" placeholder="············" aria-describedby="passwordHelp">
                                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                            </div>

                                            <?php if (isset($erreurs["password"])) {
                                            ?>
                                                <div id="passwordHelp" class="form-text"><?php echo $erreurs["password"] ?></div>
                                            <?php } ?>

                                            <!-- <div class="d-flex justify-content-end mt-3"><a href="./inscription.php">Mot de passe oublié ?</a></div> -->
                                        </div>

                                        <div class="mb-3">
                                            <button class="btn btn-primary w-100" type="submit" name="connexion">Se connecter</button>
                                        </div>
                                    </form>
                                    <hr>
                                    <div class="text-center">
                                        <span>Nouveau sur la plateforme ?</span>
                                        <a href="inscription.php" class="is-primary">Créer un compte</a>
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

    <script>
        (function() {
            // Toggle Password Visibility
            window.Helpers.initPasswordToggle();
        })();
    </script>

</body>

</html>
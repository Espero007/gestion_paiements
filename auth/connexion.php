<?php
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
    <!-- Style loader -->
    <link rel="stylesheet" href="/assets/css/loader.css">
    <script src="assets/vendor/js/helpers.js"></script>
</head>

<body class="pb-4">
    <!-- Loader -->
    <div id="loader">
        <div class="spinner"></div>
        <p class="mt-2">Chargement...</p>
    </div>

    <div id="wrapper">
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
                                        <!-- Messages divers -->
                                        <?php if (isset($echec_connexion)) : ?>
                                            <?php afficherAlerte('Les identifiants de connexion que vous avez indiqués sont invalides', 'danger') ?>
                                        <?php endif; ?>

                                        <?php if (isset($_SESSION['email_envoye'])) : ?>
                                            <?php afficherAlerte('email_envoye', 'info', true); ?>
                                        <?php endif; ?>

                                        <?php if (isset($_SESSION['email_non_envoye'])): ?>
                                            <?php afficherAlerte('email_non_envoye', 'info', true); ?>
                                        <?php endif; ?>

                                        <?php

                                        if (isset($_SESSION['deconnexion'])) {
                                            if (isset($_SESSION['timeout_atteint'])) {
                                                // déconnexion due au timeout
                                                afficherAlerte('Vous avez été déconnecté(e) pour cause d\'inactivité. Veuillez vous reconnecter avant de poursuivre.', 'info');
                                            } elseif (isset($_SESSION['cookie_expire'])) {
                                                // déconnexion due au fait que le cookie de l'utilisateur est expiré
                                                afficherAlerte('cookie_expire', 'info', true);
                                            } else {
                                                // bh c'est une simple déconnexion
                                                afficherAlerte('Vous êtes à présent déconnecté(e) !', 'info');
                                            }
                                            session_unset(); // On détruit les variables de la session
                                            session_destroy(); // On détruit la session
                                        }

                                        // if (isset($_SESSION['deconnexion']) && !isset($_SESSION['timeout_atteint'])) {

                                        //     afficherAlerte('Vous êtes à présent déconnecté(e) !', 'info');
                                        // } elseif (isset($_SESSION['deconnexion']) && isset($_SESSION['timeout_atteint'])) {
                                        //     // déconnexion due au timeout
                                        //     afficherAlerte('Vous avez été déconnecté(e) pour cause d\'inactivité. Veuillez vous reconnecter avant de poursuivre.', 'info');
                                        //     session_unset();
                                        //     session_destroy();
                                        // }elseif(isset($_SESSION['deconnexion']) && isset($_SESSION['cookie_expire'])){
                                        //     // déconnexion due au fait que le cookie est expiré

                                        // }
                                        ?>
                                        <?php if (isset($email_non_valide)) : ?>
                                            <?php
                                            $message = 'Votre email n\'a pas encore été confirmé. Veuillez consulter votre boite mail ou <a href="' . 'renvoyerLienConfirmation.php?email=' . htmlspecialchars($_POST['email']) . '">renvoyer un lien de confirmation</a> si vous n\'avez pas reçu de lien.';
                                            afficherAlerte($message, 'info')
                                            ?>
                                        <?php endif; ?>

                                        <?php if (isset($_SESSION['erreur_avec_verification_email'])) : ?>
                                            <?php afficherAlerte('erreur_avec_verification_email', 'danger', true); ?>
                                        <?php endif; ?>

                                        <?php if (isset($_SESSION['lien_invalide'])) : ?>
                                            <?php afficherAlerte('lien_invalide', 'info', true); ?>
                                        <?php endif; ?>

                                        <div class="text-center">
                                            <h1 class="h4 text-gray-900 mb-4">Contents de vous revoir !</h1>
                                            <p>Connectez-vous à votre compte pour retourner à la gestion de vos activités.</h2>
                                        </div>
                                        <form action="" method="post" class="user">
                                            <div class="form-group">
                                                <label for="email" class="col-form-label form-label">email</label>
                                                <input type="email" name="email" id="email" aria-describedby="emailHelp" placeholder="Entrez votre adresse email..." class="form-control form-control-user<?= isset($erreurs['email']) ? ' is-invalid"' : '' ?>" <?= isset($erreurs['email']) ? ' value="' . htmlspecialchars($_POST['email']) . '"' : '' ?>>
                                                <?php if (isset($erreurs['email'])) : ?>
                                                    <div id="emailHelp" class="form-text"><?= $erreurs["email"] ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="form-group form-password-toggle">
                                                <label for="password" class="col-form-label form-label">Mot de passe</label>
                                                <div class="input-group input-group-merge">
                                                    <input type="password" class="form-control form-control-user<?= isset($erreurs['password']) ? ' is-invalid' : '' ?>" id="password" name="password" placeholder="························" aria-describedby="passwordHelp">
                                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                                </div>

                                                <?php if (isset($erreurs['password'])) : ?>
                                                    <div id="passwordHelp" class="form-text"><?= $erreurs["password"] ?></div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Se souvenir de moi -->
                                            <div class="d-flex justify-content-between">
                                                <div class="form-group">
                                                    <div class="custom-control custom-checkbox small">
                                                        <input type="checkbox" class="custom-control-input" id="customCheck" name="souvenir" value="yes">
                                                        <label class="custom-control-label" for="customCheck">Se souvenir de moi</label>
                                                    </div>
                                                </div>

                                                <a href="/auth/mdp_oublie">
                                                    <small>Mot de passe oublié ?</small>
                                                </a>
                                            </div>

                                            <div class="mb-3">
                                                <button class="btn btn-primary w-100 btn-user" type="submit" name="connexion">Se connecter</button>
                                            </div>
                                        </form>
                                        <hr>
                                        <div class="text-center">
                                            <small>
                                                <span>Nouveau sur la plateforme ?</span>
                                                <a href="<?= generateUrl('inscription') ?>" class="is-primary">Créer un compte</a>
                                            </small>
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
    </div>
    <script src="/assets/bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once(__DIR__ . '/../includes/loader.php') ?>

    <script>
        (function() {
            // Toggle Password Visibility
            window.Helpers.initPasswordToggle();
        })();
    </script>

</body>

</html>
<?php
session_start();
require_once(__DIR__ . '/../includes/bdd.php');
require_once(__DIR__ . '/../includes/constantes_utilitaires.php');

// Redirection vers la page d'accueil si l'utilisateur est déjà connecté

if (isset($_SESSION['user_id']) && !isset($_SESSION['deconnexion'])) {
    // L'utilisateur est connecté
    header('location:/index.php');
    exit;
}

$anomalie = false;
$echec = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['envoyer_lien'])) {
    if (!isset($_POST['email'])) {
        $anomalie = true;
    } elseif (empty($_POST['email'])) {
        $erreurs['email'] = "Veuillez remplir ce champ.";
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs['email'] = "L'email que vous avez indiqué n'est pas valide !";
    } else {
        $email = $_POST['email'];
        $stmt = $bdd->prepare('SELECT * FROM connexion WHERE email=:email');
        $stmt->execute(['email' => $email]);
        if ($stmt->rowCount() == 0) {
            $email_inexistant = true;
            $erreurs['email'] = 'L\'email que vous avez indiqué n\'a pas été enregistré. Assurez-vous d\'indiquer un email valide.';
        } else {
            $stmt = $bdd->prepare('SELECT * FROM connexion WHERE email=:email AND est_verifie=1');
            $stmt->execute(['email' => $email]);
            if ($stmt->rowCount() == 0) {
                $erreurs['email'] = 'L\'email que vous avez indiqué n\'a pas encore été confirmé. Confirmez-le en consultant votre boite mail puis réessayez ou <a href="' . 'renvoyerLienConfirmation.php?email=' . htmlspecialchars(urlencode($email)) . '">renvoyer un lien de confirmation</a> si vous n\'avez pas reçu de lien.';
            }
        }
    }

    if (!isset($erreurs)) {
        $stmt = $bdd->prepare('SELECT * FROM connexion WHERE email=:email AND est_verifie=1');
        $stmt->execute(['email' => $_POST['email']]);
        $users = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($stmt->rowCount() != 0) {
            // L'utilisateur avec cet email est bien présent. On effectue à présent les actions adéquates pour la réinitialisation du mot de passe
            $token = bin2hex(random_bytes(16));
            $email = $_POST['email'];
            $lien_verif = obtenirURLcourant(true) . '/auth/submit/verifie_email.php?email=' . urlencode($email) . '&token=' . $token . '&mdp_oublie=1';
            if (envoyerLienValidationEmail($lien_verif, $_POST['email'], $users['nom'], $users['prenoms'], 2)) {
                $stmt = $bdd->prepare('UPDATE connexion SET token_verification=:token WHERE email=:email');
                if ($stmt->execute(['token' => $token, 'email' => $email])) {
                    // L'insertion est ok donc on peut le rediriger vers la page de connexion avec un message de succès
                    $_SESSION["email_envoye"] = 'Un lien de vérification a été envoyé au mail : <span class="text-primary">' . htmlspecialchars($_POST['email']) . '</span>. Cliquez dessus pour confirmer votre email et réinitialiser votre mot de passe.';
                    // header('location:connexion.php');
                    // exit;
                }
            } else {
                $anomalie = true;
            }
        } else {
            $echec = true;
        }
    }
}

/**Second volet de la page : l'email a été validé */
$email_confirme = false;
if (isset($_SESSION['email_confirme']) || isset($_SESSION['email_utilisateur'])) {
    $email_confirme = true;
    $email_utilisateur = $_SESSION['email_utilisateur'];
}

// $email_confirme = true;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reinitialiser'])) {
    // Il faut ici valider l'email et les mots de passe
    if (!isset($_POST['email'])) {
        redirigerVersPageErreur();
    } else {
        $email = $_POST['email'];
        $stmt = $bdd->prepare('SELECT * FROM connexion WHERE email = :email AND token_verification=\'\'');
        $stmt->execute(['email' => $email]);
        if ($stmt->rowCount() == 1) {
            // Email ok donc on peut tester le nouveau mot de passe

            $mdp = $_POST['mdp'];
            $mdp_confirme = $_POST['mdp_confirme'];

            if (empty($mdp)) {
                $erreurs['mdp'] = 'Veuillez remplir ce champ';
            }
            if (empty($mdp_confirme)) {
                $erreurs['mdp_confirme'] = 'Veuillez remplir ce champ';
            }

            if (!empty($mdp) && !empty($mdp_confirme)) {
                if (strlen($_POST["mdp"]) < 6 || !preg_match('/^[A-Z]/', $_POST["mdp"]) || !preg_match('/\d/', $_POST["mdp"])) {
                    $erreurs['mdp'] = "Le mot de passe doit contenir au moins 06 caractères; commencer par une lettre majuscule et contenir au moins un chiffre";
                } else {
                    // Pas de problèmes avec le mot de passe
                    if ($_POST['mdp'] != $_POST['mdp_confirme']) {
                        $erreurs['mdp_confirme'] = "Vous devez indiquer exactement le même mot de passe ici";
                    }
                }
            }


            if (!isset($erreurs)) {
                $stmt = $bdd->prepare('UPDATE connexion SET password=:mdp WHERE email=:email');

                if ($stmt->execute(['mdp' => password_hash($_POST['mdp'], PASSWORD_DEFAULT), 'email' => $email])) {
                    $_SESSION["mdp_reinitialise"] = 'Votre mot de passe a été réinitialisé avec succès !';
                    unset($_SESSION['email_utilisateur']);
                    // On redirige vers la page de connexion
                    header('location:connexion.php');
                    exit;
                } else {
                    die('Une erreur s\'est produite. Veuillez réessayer plus tard');
                }
            }
        } else {
            // Email non ok
            redirigerVersPageErreur();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" href="../assets/bootstrap-5.3.5-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css">
    <link rel="stylesheet" href="custom_style.css">
    <!-- Style loader -->
    <link rel="stylesheet" href="/assets/css/loader.css">
</head>

<body class="pb-4">
    <!-- Loader -->

    <div id="loader">
        <div class="spinner"></div>
        <p class="mt-2">Chargement...</p>
    </div>

    <!-- Helpers -->

    <div id="wrapper">
        <div class="container-xxl mt-5">
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
                                        <?php if ($anomalie) : ?>
                                            <?php afficherAlerte('Une erreur s\'est produite, veuillez réessayer plus tard', 'danger') ?>
                                        <?php endif; ?>

                                        <?php if ($echec) : ?>
                                            <?php afficherAlerte('L\'email que vous avez indiqué est invalide', 'danger') ?>
                                        <?php endif; ?>

                                        <?php if (isset($_SESSION['email_envoye'])) : ?>
                                            <?php afficherAlerte('email_envoye', 'info', true); ?>
                                        <?php endif; ?>

                                        <?php if (isset($_SESSION['lien_invalide'])) : ?>
                                            <?php afficherAlerte('lien_invalide', 'info', true); ?>
                                        <?php endif; ?>

                                        <?php if (isset($_SESSION['email_confirme'])) : ?>
                                            <?php afficherAlerte('email_confirme', 'success', true); ?>
                                        <?php endif; ?>
                                        <!-- Fin Messages divers -->

                                        <div class="text-center mb-4">
                                            <h1 class="h4 text-gray-900 mb-4">Mot de passe oublié ?</h1>
                                            <p>Nous le comprenons, ça peut arriver. Entrez juste votre email ci-dessous et nous vous enverrons un lien pour réinitialiser votre mot de passe!</h2>
                                        </div>
                                        <form action="" method="post" class="user mb-4">
                                            <?php if (!$email_confirme) : ?>
                                                <div class="form-group mb-4">
                                                    <input type="email" name="email" aria-describedby="emailHelp" placeholder="Entrez votre adresse email..." class="form-control form-control-user<?= isset($erreurs['email']) ? ' is-invalid"' : '' ?>" <?= isset($erreurs['email']) ? ' value="' . htmlspecialchars($_POST['email']) . '"' : '' ?>>
                                                    <?php if (isset($erreurs['email'])) : ?>
                                                        <div id="emailHelp" class="form-text"><?= $erreurs["email"] ?></div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="mb-3">
                                                    <button class="btn btn-primary w-100 btn-user" type="submit" name="envoyer_lien">Envoyer le lien</button>
                                                </div>
                                            <?php else: ?>
                                                <!-- L'email est confirmé -->

                                                <?php if (isset($email_utilisateur)) : ?>
                                                    <input type="hidden" name="email" value="<?= $email_utilisateur ?>">
                                                <?php endif; ?>

                                                <!-- Nouveau Mot de passe -->
                                                <div class="form-group mb-4">
                                                    <label for="mdp" class="col-form-label form-label">Nouveau mot de passe</label>
                                                    <input type="text" name="mdp" id="mdp" aria-describedby="mdpHelp" placeholder="Entrez votre nouveau mot de passe" class="form-control form-control-user<?= isset($erreurs['mdp']) ? ' is-invalid"' : '' ?>">
                                                    <?php if (isset($erreurs['mdp'])) : ?>
                                                        <div id="mdpHelp" class="form-text"><?= $erreurs["mdp"] ?></div>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Confirmer le mot de passe -->
                                                <div class="form-group mb-4">
                                                    <label for="mdp_confirme" class="col-form-label form-label">Confirmation du mot de passe</label>
                                                    <input type="text" name="mdp_confirme" id="mdp_confirme" aria-describedby="mdpHelp" placeholder="Entrez à nouveau le mot de passe" class="form-control form-control-user<?= isset($erreurs['mdp_confirme']) ? ' is-invalid"' : '' ?>">
                                                    <?php if (isset($erreurs['mdp_confirme'])) : ?>
                                                        <div id="mdpHelp" class="form-text"><?= $erreurs["mdp_confirme"] ?></div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="mb-3">
                                                    <button class="btn btn-primary w-100 btn-user" type="submit" name="reinitialiser">Réinitialiser le mot de passe</button>
                                                </div>
                                            <?php endif; ?>
                                        </form>
                                        <hr>
                                        <div class="text-center">
                                            <small>
                                                <a href="/auth/inscription.php" class="is-primary">Créer un compte</a>
                                            </small>
                                        </div>
                                        <div class="text-center">
                                            <small>
                                                <span>Déjà un compte ?</span>
                                                <a href="/auth/connexion.php" class="is-primary">Se connecter</a>
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
    <script src="assets/vendor/js/helpers.js"></script>
    <script src="/assets/bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once(__DIR__ . '/../includes/loader.php')
    ?>

</body>

</html>
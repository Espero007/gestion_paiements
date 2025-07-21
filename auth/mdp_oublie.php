<?php
require_once(__DIR__ . '/../includes/bdd.php');
require_once(__DIR__ . '/../includes/constantes_utilitaires.php');

// Redirection vers la page d'accueil si l'utilisateur est déjà connecté

if (isset($_SESSION['user_id']) && !isset($_SESSION['deconnexion'])) {
    // L'utilisateur est connecté
    header('location:'.generateUrl(''));
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
    }

    if (!isset($erreurs)) {
        $stmt = $bdd->prepare('SELECT * FROM connexion WHERE email=:email AND est_verifie=1');
        $stmt->execute(['email' => $_POST['email']]);
        $users = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($stmt->rowCount() != 0) {
            
            // L'utilisateur avec cet email est bien présent. On effectue à présent les actions adéquates pour la réinitialisation du mot de passe
            $token = bin2hex(random_bytes(16));
            $lien_verif = obtenirURLcourant(true) . '/auth/forgot_password.php';
            if(envoyerLienValidationEmail($lien_verif,$_POST['email'],$users['nom'],$users['prenoms'],1)){
               $_SESSION["email_envoye"] = 'Un lien de vérification a été envoyé au mail : <span class="text-primary">' . htmlspecialchars($_POST['email']) . '</span>. Cliquez dessus pour confirmer votre email et réinitialiser votre mot de passe.'; 
            }else {
                $anomalie = true;
            }

        } else {
            $echec = true;
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
    <link rel="stylesheet" href="/includes/loader.css">
</head>

<body class="pb-4">
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
                                        <!-- Fin Messages divers -->

                                        <div class="text-center mb-4">
                                            <h1 class="h4 text-gray-900 mb-4">Mot de passe oublié ?</h1>
                                            <p>Nous le comprenons, ça peut arriver. Entrez juste votre email ci-dessous et nous vous enverrons un lien pour réinitialiser votre mot de passe!</h2>
                                        </div>
                                        <form action="" method="post" class="user mb-4">
                                            <div class="form-group mb-4">
                                                <input type="email" name="email" aria-describedby="emailHelp" placeholder="Entrez votre adresse email..." class="form-control form-control-user<?= isset($erreurs['email']) ? ' is-invalid"' : '' ?>" <?= isset($erreurs['email']) ? ' value="' . htmlspecialchars($_POST['email']) . '"' : '' ?>>
                                                <?php if (isset($erreurs['email'])) : ?>
                                                    <div id="emailHelp" class="form-text"><?= $erreurs["email"] ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mb-3">
                                                <button class="btn btn-primary w-100 btn-user" type="submit" name="envoyer_lien">Envoyer le lien</button>
                                            </div>
                                        </form>
                                        <hr>
                                        <div class="text-center">
                                            <small>
                                                <a href="inscription" class="is-primary">Créer un compte</a>
                                            </small>
                                        </div>
                                        <div class="text-center">
                                            <small>
                                                <span>Déjà un compte ?</span>
                                                <a href="connexion" class="is-primary">Se connecter</a>
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
    <?php require_once(__DIR__ . '/../includes/loader.php') ?>

</body>

</html>
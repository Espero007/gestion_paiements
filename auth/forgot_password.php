<?php
require_once(__DIR__ . "/submit/submit_forgot.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié</title>
    <link rel="stylesheet" href="../assets/bootstrap-5.3.5-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/vendor/fonts/boxicons.css">
    <link rel="stylesheet" href="/auth/custom_style.css">
    <!-- Style loader -->
    <link rel="stylesheet" href="/assets/css/loader.css">
    <script src="assets/vendor/js/helpers.js"></script>

</head>

<body class="pb-4">
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

                                        <h3 class="text-center text-secondary"> Mot de passe oublé</h3>

                                        <?php
                                        if (isset($_SESSION["error_message"])) {

                                            afficherAlerte('error_message', 'danger', true);
                                        }
                                        ?>

                                        <form action="" method="POST" class="user">
                                            <div class="form-group">
                                                <label for="email" class="form-text"> Insérer votre email </label>
                                                <div class="input-group input-group-merge">
                                                    <input type="email" name="email" class="form-control form-control-user<?= isset($_SESSION['email']) ? ' is-invalid' : '' ?>" id="email" placeholder="votre_nom@gmail.com" value="<?php htmlspecialchars($_POST["email"] ?? "") ?>">

                                                </div>
                                                <small style="color:red"> <?php if ((isset($_SESSION["email"]))) {
                                                                                echo $_SESSION["email"];
                                                                                unset($_SESSION["email"]);
                                                                            } ?>
                                                </small>
                                            </div>

                                            <div class="form-group form-password-toggle">
                                                <label for="new_password" class="form-text"> Nouveau mot de passe</label>
                                                <div class="input-group input-group-merge">
                                                    <input type="password" class=" form-control form-control-user<?= isset($_SESSION['password']) ? ' is-invalid' : '' ?>" name="new_password" id="new_password" value="<?= htmlspecialchars($_POST["new_password"] ?? "") ?>">
                                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                                </div>
                                                <small style="color:red"> <?php if ((isset($_SESSION["password"]))) {
                                                                                echo $_SESSION["password"];
                                                                                unset($_SESSION["password"]);
                                                                            } ?>
                                                </small>
                                            </div>

                                            <div class="form-group form-password-toggle">

                                                <label for="password" class="form-text"> Confirmer le mot de passe</label>
                                                <div class="input-group input-group-merge">
                                                    <input type="password" class="form-control form-control-user<?= isset($_SESSION['invalid_password']) ? ' is-invalid' : '' ?>" name="password" id="password" value="<?= htmlspecialchars($_POST["password"] ?? "") ?>">
                                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                                </div>
                                                <small style="color:red"> <?php if ((isset($_SESSION["invalid_password"]))) {
                                                                                echo $_SESSION["invalid_password"];
                                                                                unset($_SESSION["invalid_password"]);
                                                                            } ?> </small>
                                            </div>
                                            <div> <button type="submit" class="btn btn-primary w-100 btn-user"> Envoyer </button> </div>
                                        </form>


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
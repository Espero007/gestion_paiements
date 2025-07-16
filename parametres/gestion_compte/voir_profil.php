<?php
$section = 'Paramètres';
$titre_page = "Mon compte";
require_once(__DIR__ . '/../../includes/header.php');
require_once('traitements/voir_profil.php');
require_once('traitements/desactiver_compte.php');

?>
<link rel="stylesheet" href="/auth/assets/vendor/fonts/boxicons.css">
<link rel="stylesheet" href="/auth/custom_style.css">

<body id="page-top">

    <style>
        .card-header {
            background-color: transparent;
            font-weight: 700;
        }

        .form-label,
        .col-form-label {
            text-transform: inherit;
        }

        .input-group> :not(:first-child):not(.dropdown-menu):not(.valid-tooltip):not(.valid-feedback):not(.invalid-tooltip):not(.invalid-feedback) {
            margin-left: calc(-1 * var(--bs-border-width));
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }
    </style>

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php require_once(__DIR__ . '/../../includes/sidebar.php') ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <?php require_once(__DIR__ . '/../../includes/topbar.php') ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->

                    <div>
                        <h1 class="h4 mb-4 text-gray-800">Paramètres / <strong>Mon compte</strong></h1>
                        <div class="card mb-4">
                            <h6 class="h6 card-header">Détails du profil</h5>
                                <!-- Informations du compte -->
                                <div class="card-body">

                                    <!-- Messages divers -->
                                    <?php if (isset($photo_modifie)) : ?>
                                        <div class="alert alert-success alert-dismissible text-center">
                                            Votre photo a été modifiée avec succès !
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Informations modifiées avec succès -->
                                    <?php if (isset($infos_modifiees)) : ?>
                                        <?php afficherAlerte('Vos informations ont été modifiées avec succès !', 'success') ?>
                                    <?php endif; ?>

                                    <!-- Erreur ou pas lors de l'envoi de l'email -->

                                    <?php if (isset($email_envoye)) : ?>
                                        <?php
                                        $message = $email_envoye ? ' Vous êtes presque au bout, nous avons envoyé un lien de confirmation à l\'email indiqué, cliquez dessus pour achever le processus !' : 'Une erreur s\'est produite lors de l\'envoi du lien de confirmation à votre email, veuillez réessayer la mise à jour de votre email plus tard';
                                        $type = $email_envoye ? 'success' : 'info';
                                        afficherAlerte($message, $type);
                                        ?>
                                    <?php endif; ?>

                                    <!-- Email confirmé avec succès -->

                                    <?php if (isset($_SESSION['email_modifie']) && $_SESSION['email_modifie']) : ?>
                                        <?php
                                        afficherAlerte('Votre email a été confirmé et mis à jour avec succès !', 'success');
                                        unset($_SESSION['email_modifie']);
                                        ?>
                                    <?php endif; ?>

                                    <!-- Lien de confirmation d'email invalide -->

                                    <?php if (isset($_SESSION['lien_invalide'])) : ?>
                                        <?php afficherAlerte('lien_invalide', 'info', true); ?>
                                    <?php endif; ?>

                                    <div class="d-flex align-items-start align-items-sm-center gap-4">
                                        <img src=" <?= (!empty($utilisateur['photo_profil'])) ? '/photos_profil/' . $utilisateur['photo_profil'] : '/assets/img/undraw_profile.svg' ?>" alt="photo-profil" class="d-block rounded" height="100" width="100" style="aspect-ratio: 1;">
                                        <div class="button-wrapper">
                                            <div class="mb-4">
                                                <div>
                                                    <form action="" method="post" enctype="multipart/form-data">
                                                        <label for="upload" class="btn btn-primary mr-2 mb-0" tabindex="0">
                                                            <span class="d-none d-sm-block">Choisir une nouvelle photo</span>
                                                            <i class="bi bi-cloud-upload d-block d-sm-none"></i>
                                                            <input type="file" name="photo" id="upload" class="account-file-input" hidden accept='image/png, image/jpeg, image/jpg'>
                                                        </label>
                                                        <button type="submit" class="btn btn-outline-secondary account-image-reset" name='choisir_photo'>
                                                            <i class="bx bx-reset d-block d-sm-none"></i>
                                                            <span class="d-none d-sm-block">Changer</span>
                                                        </button>
                                                    </form>
                                                </div>
                                                <?php if (isset($erreurs['photo'])) : ?>
                                                    <p class="text-danger"><small><?= $erreurs['photo'][0] ?></small></p>
                                                <?php endif; ?>
                                            </div>
                                            <p class="text-muted mb-0">JPG, JPEG ou PNG autorisés (Taille maximale de 2Mo) </p>
                                            <input type="hidden" name="MAX_FILE_SIZE" value="<?= $taille_image; ?>">
                                        </div>
                                    </div>
                                </div>
                                <hr class="my-0">
                                <div class="card-body">
                                    <form action="" method="post">
                                        <div class="row">
                                            <div class="mb-3 col-md-6">
                                                <label for="nom" class="col-form-label">Nom</label>
                                                <input type="text" class="form-control <?= (isset($erreurs['nom'])) ? 'is-invalid' : '' ?>" id="nom" name="nom" value='<?= (!isset($erreurs)) ? htmlspecialchars($utilisateur['nom']) : htmlspecialchars($_POST['nom']) ?>' autofocus aria-describedby="nomAide">

                                                <!-- Message d'erreur -->
                                                <?php if (isset($erreurs['nom'])) : ?>
                                                    <div id="nomAide" class="form-text">
                                                        <small class="text-danger"><?= $erreurs['nom'][0] ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label for="prenoms" class="col-form-label">Prénom(s)</label>
                                                <input type="text" class="form-control <?= (isset($erreurs['prenoms'])) ? 'is-invalid' : '' ?>" id="prenoms" name="prenoms" value="<?= (!isset($erreurs)) ? htmlspecialchars($utilisateur['prenoms']) : htmlspecialchars($_POST['prenoms']) ?>" aria-describedby="prenomsAide">

                                                <!-- Message d'erreur -->
                                                <?php if (isset($erreurs['prenoms'])) : ?>
                                                    <div id="nomAide" class="form-text">
                                                        <small class="text-danger"><?= $erreurs['prenoms'][0] ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label for="email" class="col-form-label">Email</label>
                                                <input type="email" class="form-control<?= (isset($erreurs['email'])) ? ' is-invalid' : '' ?>" id="email" name="email" value="<?= (!isset($erreurs)) ? htmlspecialchars($utilisateur['email']) : htmlspecialchars($_POST['email']) ?>">

                                                <!-- Message d'erreur -->
                                                <?php if (isset($erreurs['email'])) : ?>
                                                    <div id="nomAide" class="form-text">
                                                        <small class="text-danger"><?= $erreurs['email'][0] ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="mt-2">
                                            <button type="submit" class="btn btn-primary mr-2" name="modifier_infos">Enregistrer les modifications</button>
                                            <button type="reset" class="btn btn-outline-secondary">Annuler</button>
                                        </div>
                                    </form>
                                </div>
                                <!-- Informations du compte -->
                        </div>

                        <!-- Actualisation du mot de passe -->
                        <div class="card mb-4">
                            <h5 class="card-header h6">Actualisation du mot de passe</h5>
                            <div class="card-body">
                                <form action="" method="post">
                                    <div class="row">
                                        <div class="mb-3 col-md-6 form-group form-password-toggle">
                                            <label for="password" class="col-form-label">Mot de passe actuel</label>
                                            <div class="input-group input-group-merge">
                                                <input type="password" class="form-control" id="password" name="mdp_actuel" value='' autofocus placeholder="············">
                                                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label for="nouveau_mdp" class="col-form-label">Nouveau mot de passe</label>
                                            <input type="text" class="form-control" id="nouveau_mdp" name="nouveau_mdp" value="">
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        <button type="submit" class="btn btn-primary mr-2" name="modifier_mdp">Modifier le mot de passe</button>
                                        <button type="reset" class="btn btn-outline-secondary">Annuler</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <!-- Fin Actualisation du mot de passe -->

                        <!-- Suppresion compte -->
                        <div class="card">
                            <h5 class="card-header h6">Suppression du compte</h5>
                            <div class="card-body">
                                <div class="mb-3 col-12 mb-0">
                                    <div class="alert alert-warning">
                                        <h6 class="alert-heading font-weight-bold mb-1">Êtes-vous sûr(e) de vouloir supprimer votre compte ?</h6>
                                        <p class="mb-0">Une fois que vous aurez supprimé votre compte, il n'y aura pas de retour en arrière possible. Soyez certain(e) de votre choix.</p>
                                    </div>
                                </div>
                                <form action="" method="POST">
                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" name='suppressionCompte' id='suppressionCompte' value="yes">
                                        <label for="suppressionCompte" class="form-check-label"> Je confirme la suppression de mon compte </label>
                                        <?php echo !$confirmation ? '<p> <small class="text-danger"> Veillez confirmer la suppression de votre compte </small></p>' : '' ?>
                                    </div>
                                    <button type="submit" name="desactiver" class="btn btn-danger desactiver-compte">Supprimer compte</button>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <?php require_once(__DIR__ . '/../../includes/footer.php') ?>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Logout Modal-->
    <?php require_once(__DIR__ . '/../../includes/logoutModal.php') ?>
    <?php require_once(__DIR__ . '/../../includes/scripts.php') ?>
    <script src="/auth/assets/vendor/js/helpers.js"></script>
    <script>
        (function() {
            // Toggle Password Visibility
            window.Helpers.initPasswordToggle();
        })();
    </script>
</body>

</html>
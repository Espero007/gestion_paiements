<?php
$section = 'Paramètres';
$titre_page = "Mon compte";
require_once(__DIR__ . '/../../includes/header.php');
// require_once('traitements/voir_profil.php');
// require_once('traitements/actualiser_mdp.php');
// require_once('traitements/desactiver_compte.php');

// Récupération des informations de l'utilisateur
$stmt = $bdd->query('SELECT nom, prenoms, email, photo_profil FROM connexion WHERE user_id=' . $_SESSION['user_id']);
$utilisateur = $stmt->fetchAll(PDO::FETCH_ASSOC);
$utilisateur = $utilisateur[0];

// Récupération de quelques informations que les pages de traitement pourraient renvoyer
if (isset($_SESSION['erreurs']) && !empty($_SESSION['erreurs'])) {
    $erreurs = $_SESSION['erreurs'];
    unset($_SESSION['erreurs']);
}
?>
<link rel="stylesheet" href="/auth/assets/vendor/fonts/boxicons.css">

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
                                    <div class="messages_divers">
                                        <?php if (isset($_SESSION['photo_modifiee'])) : ?>
                                            <?php afficherAlerte('photo_modifiee', 'success', true) ?>
                                        <?php endif; ?>

                                        <!-- Informations modifiées avec succès -->
                                        <?php if (isset($_SESSION['infos_modifiees'])) : ?>
                                            <?php afficherAlerte('infos_modifiees', 'success', true) ?>
                                        <?php endif; ?>

                                        <?php if (isset($_SESSION['mdp_actualise'])) : ?>
                                            <?php afficherAlerte('mdp_actualise', 'success', true) ?>
                                        <?php endif; ?>

                                        <!-- Email déjà utilisé par un autre utilisateur -->
                                        <?php if (isset($_SESSION['email_deja_pris'])) : ?>
                                            <?php afficherAlerte('email_deja_pris', 'info', true) ?>
                                        <?php endif; ?>

                                        <!-- Erreur ou pas lors de l'envoi de l'email -->
                                        <?php if (isset($_SESSION['email_envoye'])) : ?>
                                            <?php
                                            $email_envoye = $_SESSION['email_envoye'];
                                            unset($_SESSION['email_envoye']);

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
                                    </div>
                                    <!-- Fin Messages divers -->

                                    <!-- Changement de la photo de profil -->
                                    <div class="d-flex align-items-start align-items-sm-center gap-4">
                                        <img src="<?= (!empty($_SESSION['photo_profil'])) ? '/photos_profil/' . $_SESSION['photo_profil'] : '/assets/img/undraw_profile.svg' ?>" alt="photo-profil" class="d-block rounded" height="100" width="100" style="aspect-ratio: 1;">
                                        <div class="button-wrapper">
                                            <div class="mb-4">
                                                <div>
                                                    <form action="./traitements/voir_profil.php" method="post" enctype="multipart/form-data">
                                                        <label for="upload" class="btn btn-primary mr-2 mb-0" tabindex="0">
                                                            <span class="d-none d-sm-block">Choisir une nouvelle photo</span>
                                                            <i class="bi bi-cloud-upload d-block d-sm-none"></i>
                                                            <input type="file" name="photo" id="upload" class="account-file-input" hidden accept='image/png, image/jpeg, image/jpg'>
                                                        </label>
                                                        <button type="submit" class="btn btn-outline-secondary account-image-reset" name='changer_photo'>
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
                                    <!-- Fin Changement de la photo de profil-->
                                </div>

                                <hr class="my-0">

                                <div class="card-body">
                                    <form action="./traitements/voir_profil.php" method="post">
                                        <div class="row">
                                            <div class="mb-3 col-md-6">
                                                <label for="nom" class="col-form-label">Nom</label>
                                                <input type="text" class="form-control <?= (isset($erreurs['nom'])) ? 'is-invalid' : '' ?>" id="nom" name="nom" value='<?= (!isset($erreurs['nom'])) ? htmlspecialchars($utilisateur['nom']) : htmlspecialchars($_POST['nom']) ?>' autofocus aria-describedby="nomAide">

                                                <!-- Message d'erreur -->
                                                <?php if (isset($erreurs['nom'])) : ?>
                                                    <div id="nomAide" class="form-text">
                                                        <small class="text-danger"><?= $erreurs['nom'][0] ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label for="prenoms" class="col-form-label">Prénom(s)</label>
                                                <input type="text" class="form-control <?= (isset($erreurs['prenoms'])) ? 'is-invalid' : '' ?>" id="prenoms" name="prenoms" value="<?= (!isset($erreurs['prenoms'])) ? htmlspecialchars($utilisateur['prenoms']) : htmlspecialchars($_POST['prenoms']) ?>" aria-describedby="prenomsAide">

                                                <!-- Message d'erreur -->
                                                <?php if (isset($erreurs['prenoms'])) : ?>
                                                    <div id="nomAide" class="form-text">
                                                        <small class="text-danger"><?= $erreurs['prenoms'][0] ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label for="email" class="col-form-label">Email</label>
                                                <input type="email" class="form-control<?= (isset($erreurs['email'])) ? ' is-invalid' : '' ?>" id="email" name="email" value="<?= (!isset($erreurs['email'])) ? htmlspecialchars($utilisateur['email']) : htmlspecialchars($_POST['email']) ?>">

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
                                <!-- Fin Informations du compte -->
                        </div>

                        <!-- Actualisation du mot de passe -->

                        <div class="card mb-4">
                            <h5 class="card-header h6">Actualisation du mot de passe</h5>
                            <div class="card-body">
                                <form action="./traitements/actualiser_mdp.php" method="post">
                                    <div class="row">
                                        <!-- Ancien Mot de passe -->
                                        <div class="mb-3 col-md-6 form-group form-password-toggle">
                                            <label for="password" class="col-form-label">Mot de passe actuel</label>
                                            <div class="input-group input-group-merge">
                                                <input type="password" class="form-control" id="password" name="mdp_actuel" value='' autofocus placeholder="············">
                                                <span class="input-group-text cursor-pointer pasToi"><i class="bx bx-hide"></i></span>
                                            </div>
                                            <?php echo isset($erreurs['mdp_actuel']) ? '<p class="m-0"><small class="text-danger">' . htmlspecialchars($erreurs['mdp_actuel']) . '</small></p>' : '' ?>
                                        </div>
                                        <!-- Nouveau Mot de passe -->
                                        <div class="mb-3 col-md-6">
                                            <label for="nouveau_mdp" class="col-form-label">Nouveau mot de passe</label>
                                            <input type="text" class="form-control" id="nouveau_mdp" name="nouveau_mdp" value=''>
                                            <?php echo isset($erreurs['nouveau_mdp']) ? '<p class="m-0"><small class="text-danger">' . htmlspecialchars($erreurs['nouveau_mdp']) . '</small> </p>' : '' ?>
                                            <small><strong>Note</strong> : Veillez à ce que le nouveau mot de passe contienne au moins 06 caractères, qu'il contienne par une lettre majuscule et contienne au moins un chiffre.</small>
                                        </div>
                                    </div>

                                    <div class="mt-2">
                                        <button type="submit" class="btn btn-primary mr-2" name="modifier_mdp">Modifier le mot de passe</button>
                                        <button type="reset" class="btn btn-outline-secondary">Annuler</button>
                                    </div>
                                </form>
                                <div class="my-2">
                                    <small>
                                        <a href="/auth/mdp_oublie.php">Mot de passe oublié ? Cliquez ici pour le réinitialiser</a>
                                    </small>
                                </div>
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
                                <form action="./traitements/desactiver_compte.php" method="post">
                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" name='suppressionCompte' id='suppressionCompte' value="yes">
                                        <label for="suppressionCompte" class="form-check-label"> Je confirme la suppression de mon compte </label>
                                        <?php if (isset($erreurs['suppression_non_confirmee'])) : ?>
                                            <p><small class="text-danger">Veuillez confirmer la suppression de votre compte avant de continuer.</small></p>
                                        <?php endif; ?>
                                    </div>
                                    <button type="submit" name="supprimerCompte" class="btn btn-danger desactiver-compte">Supprimer compte</button>
                                </form>
                            </div>
                        </div>
                        <!-- Fin Suppression compte -->
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
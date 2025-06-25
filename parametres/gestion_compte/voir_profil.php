<?php
$section = 'Paramètres';
$titre_page = "Mon compte";
require_once(__DIR__ . '/../../includes/header.php');
require_once('traitements/voir_profil.php');

// Récupération des informations de l'utilisateur
$stmt = $bdd->query('SELECT nom, prenoms, email FROM connexion WHERE user_id=' . $_SESSION['user_id']);
$utilisateur = $stmt->fetchAll(PDO::FETCH_ASSOC);
$utilisateur = $utilisateur[0];

?>

<body id="page-top">

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
                            <h6 class="h6 card-header custom-card-header">Détails du profil</h5>
                                <!-- Informations du compte -->
                                <div class="card-body">
                                    <div class="d-flex align-items-start align-items-sm-center gap-4">
                                        <img src="/assets/img/undraw_profile.svg" alt="photo-profil" class="d-block rounded" height="100" width="100">
                                        <div class="button-wrapper">
                                            <div class="mb-4">
                                                <div>
                                                    <label for="upload" class="btn btn-primary mr-2" tabindex="0">
                                                        <span class="d-none d-sm-block">Choisir une nouvelle photo</span>
                                                        <i class="bx bx-upload d-block d-sm-none"></i>
                                                        <input type="file" name="choisir_photo" id="upload" class="account-file-input" hidden accept='image/png, image/jpeg, image/jpg'>
                                                        <button type="button" class="btn btn-outline-secondary account-image-reset">
                                                            <i class="bx bx-reset d-block d-sm-none"></i>
                                                            <span class="d-none d-sm-block">Réinitialiser</span>
                                                        </button>
                                                    </label>
                                                </div>
                                                <p class="text-danger"><small>Bonjour</small></p>
                                            </div>
                                            <p class="text-muted mb-0">JPG, JPEG ou PNG autorisés (Taille maximale de 2Mo) </p>
                                        </div>
                                    </div>
                                </div>
                                <hr class="my-0">
                                <div class="card-body">
                                    <form action="" method="post">
                                        <div class="row">
                                            <div class="mb-3 col-md-6">
                                                <label for="nom" class="col-form-label">Nom</label>
                                                <input type="text" class="form-control" id="nom" name="nom" value='<?= htmlspecialchars($utilisateur['nom']) ?>' autofocus>
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label for="prenoms" class="col-form-label">Prénom(s)</label>
                                                <input type="text" class="form-control" id="prenoms" name="prenoms" value="<?= htmlspecialchars($utilisateur['prenoms']) ?>">
                                            </div>
                                            <div class="mb-3 col-md-6">
                                                <label for="email" class="col-form-label">Email</label>
                                                <input type="text" class="form-control" id="email" name="email" value="<?= htmlspecialchars($utilisateur['email']) ?>">
                                            </div>
                                        </div>

                                        <div class="mt-2">
                                            <button type="submit" class="btn btn-primary mr-2">Enregistrer les modifications</button>
                                            <button type="reset" class="btn btn-outline-secondary">Annuler</button>
                                        </div>
                                    </form>
                                </div>
                                <!-- Informations du compte -->
                        </div>

                        <!-- Suppresion compte -->
                        <div class="card">
                            <h5 class="card-header h6 card-header custom-card-header">Suppression du compte</h5>
                            <div class="card-body">
                                <div class="mb-3 col-12 mb-0">
                                    <div class="alert alert-warning">
                                        <h6 class="alert-heading font-weight-bold mb-1">Êtes-vous sûr(e) de vouloir supprimer votre compte ?</h6>
                                        <p class="mb-0">Une fois que vous aurez supprimé votre compte, il n'y aura pas de retour en arrière possible. Soyez certain(e) de votre choix.</p>
                                    </div>
                                </div>
                                <form action="">
                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" name='desactivationCompte' id='desactivationCompte'>
                                        <label for="desactivationCompte" class="form-check-label">Je confirme la désactovation de mon compte</label>
                                    </div>
                                    <button type="submit" class="btn btn-danger desactiver-compte">Désactiver compte</button>
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
</body>

</html>
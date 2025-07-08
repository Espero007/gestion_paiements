<?php
$section = 'Activités';
$titre_page = "Génération de documents";
require_once(__DIR__ . '/../includes/header.php');
require_once('traitements/generation_documents.php');
?>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php require_once(__DIR__ . '/../includes/sidebar.php') ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <?php require_once(__DIR__ . '/../includes/topbar.php') ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <h1 class="h4 mb-4 text-gray-800">Activités / <strong>Génération de documents</strong></h1>
                    <p class="mt-2">Générez les documents associés à votre activité.</p>

                    <!-- Documents à générer -->
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h6 class="text-primary font-weight-bold">Documents à générer</h6>
                        </div>
                        <div class="card-body">

                            <p class=""><strong>Note</strong> : Pour une activité, 06 documents peuvent être générés et téléchargés en PDF : la note de service, l'attestation collective de travail, l’état de paiement, les ordres de virements, la synthèse des ordres de virements et la liste des RIB des participants. Choisissez en dessous les documents que vous voulez générer et télécharger parmi les 06.</p>

                            <div class="divider text-start">
                                <div class="divider-text"><strong>Liste des documents <?= isset($documents_choisis) ? 'choisis' : '' ?> </strong></div>
                            </div>

                            <?php if (!isset($documents_choisis)) : ?>
                                <form action="" method="post">
                                    <div class="ml-4">
                                        <?php foreach ($documents as $document => $label) : ?>
                                            <div class="form-check mt-3">
                                                <input type="checkbox" name="<?= $document ?>" value="<?= $document ?>" id="<?= $document ?>" class="form-check-input">
                                                <label for="<?= $document ?>" class="form-check-label"><?= $label ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="divider text-start">
                                        <div class="divider-text"><strong>Action</strong></div>
                                    </div>

                                    <!-- Boutons d'actions -->
                                    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST)) : ?>
                                        <p class="mt-0 mb-2"><small><strong>Note</strong> : Sélectionnez des documents à générer</small></p>
                                    <?php endif; ?>
                                    <button type="submit" class="btn btn-primary">Sélectionner</button>
                                </form>
                            <?php else: ?>

                                <div class="col-lg-6 mb-4 mb-xl-0">
                                    <div class="mt-3">
                                        <ol class="list-group list-group-numbered">
                                            <?php foreach ($documents_choisis as $document) : ?>
                                                <li class="list-group-item border-0"><?= $documents[$document] ?></li>
                                            <?php endforeach; ?>
                                        </ol>
                                    </div>
                                </div>

                                <div class="divider text-start">
                                    <div class="divider-text"><strong>Action</strong></div>
                                </div>
                                <button class="btn btn-primary">Générer</button>
                            <?php endif; ?>


                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <?php require_once(__DIR__ . '/../includes/footer.php') ?>
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
    <?php require_once(__DIR__ . '/../includes/logoutModal.php') ?>
    <?php require_once(__DIR__ . '/../includes/scripts.php') ?>
</body>

</html>
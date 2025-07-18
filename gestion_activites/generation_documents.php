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
                            <h6 class="text-primary font-weight-bold">Documents</h6>
                        </div>
                        <div class="card-body">

                            <!-- Messages divers -->

                            <?php if (isset($_SESSION['edition_entete_ok'])) : ?>
                                <?php afficherAlerte('edition_entete_ok', 'success', true) ?>
                            <?php endif; ?>

                            <?php if (!$entete_editee) : ?>
                                <?php
                                $message = 'Il semble que vous n\'avez pas encore défini les informations à utiliser pour l\'entête de vos documents. Cliquez ici pour le faire si vous souhaitez personnaliser l\'entête de vos documents <a href="/gestion_activites/edition_en_ligne.php?id=' . $id_activite . '">Editer l\'entête</a>';
                                afficherAlerte($message, 'info', false, false);
                                ?>
                            <?php else: ?>
                                <?php
                                $message = 'Cliquez ici si vous souhaitez personnaliser l\'entête de vos documents <a href="/gestion_activites/edition_en_ligne.php?id=' . $id_activite . '">Editer l\'entête</a>';
                                afficherAlerte($message, 'info');
                                ?>
                            <?php endif; ?>

                            <!-- Fin Messages divers -->

                            <small><strong>Note</strong> : Pour une activité, 06 types de documents distincts peuvent être générés et téléchargés en PDF : la note de service, l'attestation collective de travail, l’état de paiement, les ordres de virements, la synthèse des ordres de virements et la liste des RIB des participants. Choisissez en dessous les documents que vous voulez générer et télécharger parmi les 06.</small>

                            <div class="divider text-start">
                                <div class="divider-text"><strong>Liste des documents <?= isset($documents_choisis) ? 'choisis' : '' ?> </strong></div>
                            </div>

                            <?php if (!isset($documents_choisis)) : ?>
                                <form action="" method="post">
                                    <div class="ml-4">
                                        <?php $premiere_fois = false ?>
                                        <?php foreach ($documents as $document => $label) : ?>
                                            <?php if (str_contains($document, 'ordre_virement') && !$premiere_fois) : ?>
                                                <div class="mt-3">
                                                    <p class="m-0 font-weight-bold">Ordres de virements bancaires</p>
                                                </div>
                                                <?php $premiere_fois = true ?>
                                            <?php endif; ?>

                                            <div class="form-check<?= str_contains($document, 'ordre_virement') ? ' ml-3 mt-2' : ' mt-3' ?>">
                                                <input type="checkbox" name="<?= $document ?>" value="<?= $document ?>" id="<?= $document ?>" class="form-check-input">
                                                <label for="<?= $document ?>" class="form-check-label"><?= $label ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="divider text-start">
                                        <div class="divider-text"><strong>Actions</strong></div>
                                    </div>

                                    <!-- Boutons d'actions -->
                                    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST)) : ?>
                                        <p class="mt-0 mb-2"><small class="text-danger"><strong>Note</strong> : Sélectionnez des documents à générer</small></p>
                                    <?php endif; ?>
                                    <button type="submit" class="btn btn-primary">Continuer</button>
                                    <a href="<?= $_SESSION['previous_url'] ?>" class="btn btn-secondary ml-2">Annuler</a>
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
                                    <div class="divider-text"><strong>Actions</strong></div>
                                </div>
                                <div class="btn-group dropup">
                                    <button class="btn btn-primary" id='generer'>Générer</button>
                                    <button type="button" class="btn btn-primary btn-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"></button>

                                    <ul class="dropdown-menu">
                                        <li>
                                            <a href="#" class="dropdown-item custom-dropdown-item" title="Générez et téléchargez un fichier compressé (zip) contenant tous les documents sélectionnés.">Générer un fichier zip</a>
                                        </li>

                                        <li>
                                            <a href="#" class="dropdown-item custom-dropdown-item" title="Générez et téléchargez un seul fichier contenant tous les documents sélectionnés.">Générer un seul fichier</a>
                                        </li>
                                    </ul>
                                </div>
                                <a href="<?= $_SESSION['previous_url'] ?>" class="btn btn-secondary ml-2">Annuler</a>
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

    <script>
        const BtnGenerer = document.getElementById('generer');

        function ouvrirPDFs() {
            const pdfs = <?php echo json_encode($pdfs); ?>;
            for (let i = 0; i < pdfs.length; i++) {
                ouvert = window.open(pdfs[i], '_blank');
                if (!ouvert) {
                    alert("Popups bloqués. Veuillez autoriser les fenêtres surgissantes pour ce site.");
                    break;
                }
            }
        }
        BtnGenerer.addEventListener('click', ouvrirPDFs);
    </script>
</body>

</html>
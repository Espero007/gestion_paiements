<?php
$section = 'Activités';
$titre_page = "Gestion de l'activité";
require_once(__DIR__ . '/../includes/header.php');
require_once('traitements/gerer_activite.php');

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
                    <h1 class="h4 mb-4 text-gray-800">Activités / <strong>Gestion de l'activité</strong></h1>
                    <p class="mt-2">Gérez ici votre activité</p>

                    <!-- Fiche informative -->
                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h6 class="text-primary font-weight-bold">Fiche informative</h3>
                        </div>
                        <div class="card-body">

                            <?php if (isset($_SESSION['success'])) : ?>
                                <?php afficherAlerte('success', 'success', true) ?>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['erreur_modifier_infos'])) : ?>
                                <?php afficherAlerte('erreur_modifier_infos', 'danger', true) ?>
                            <?php endif; ?>

                            <!-- Nom -->
                            <p class="mb-3">
                                <span class="font-weight-bold">Nom : </span>
                                <span><?= htmlspecialchars($activite['nom']) ?></span>
                                <span> (Activité de type <strong><?= $activite['type_activite'] ?></strong>)</span>
                            </p>

                            <!-- Description -->
                            <p class="mb-3">
                                <span class="font-weight-bold">Description : </span>
                                <span><?= htmlspecialchars($activite['description']) ?></span>
                            </p>

                            <!-- Période -->
                            <p class="mb-3">
                                <span class="font-weight-bold">Période : </span>
                                <span>Du <?= $activite['date_debut'] ?> au <?= $activite['date_fin'] ?></span>
                            </p>

                            <!-- Centre -->
                            <p class="mb-3">
                                <span class="font-weight-bold">Centre : </span>
                                <span><?= htmlspecialchars($activite['centre']) ?></span>
                            </p>

                            <!-- Premier responsable et titre premier responsable -->
                            <p class="mb-3">
                                <span class="font-weight-bold">Premier responsable : </span>
                                <span><?= htmlspecialchars($activite['premier_responsable']) ?></span>
                                <?php if (!empty($activite['titre_responsable'])) : ?>
                                    <span>(<strong><?= htmlspecialchars($activite['titre_responsable']) ?></strong>)</span>
                                <?php endif; ?>
                            </p>

                            <!-- Organisateur et titre organisateur -->
                            <p class="mb-3">
                                <span class="font-weight-bold">Organisateur : </span>
                                <span><?= htmlspecialchars($activite['organisateur']) ?></span>
                                <?php if (!empty($activite['titre_organisateur'])) : ?>
                                    <span>(<strong><?= htmlspecialchars($activite['titre_organisateur']) ?></strong>)</span>
                                <?php endif; ?>
                            </p>

                            <!-- Financier & titre financier -->
                            <p class="mb-3">
                                <span class="font-weight-bold">Financier : </span>
                                <span><?= htmlspecialchars($activite['financier']) ?></span>
                                <?php if (!empty($activite['titre_financier'])) : ?>
                                    <span>(<strong><?= htmlspecialchars($activite['titre_financier']) ?></strong>)</span>
                                <?php endif; ?>
                            </p>

                            <?php if ($activite['type_activite'] == 1 || $activite['type_activite'] == 2) : ?>
                                <!-- Taux journalier -->
                                <p class="mb-0">
                                    <span class="font-weight-bold">Taux journalier : </span>
                                    <span><?= htmlspecialchars($activite['taux_journalier']) ?> FCFA</span>
                                </p>
                            <?php endif; ?>


                            <?php if ($activite['type_activite'] == 2 || $activite['type_activite'] == 3) : ?>

                                <!-- Indemnités forfaitaires avec les titres associés -->
                                <p class="my-3">
                                    <span class="font-weight-bold">Indemnités forfaitaires : </span>
                                    <span><?= $indemnite_str ?></span>
                                </p>
                                <!-- <div class="col-lg-6 mb-4 mb-xl-0">
                                            <div class="mt-3">
                                                <ol class="list-group list-group-numbered">
                                                    <li class="list-group-item">Bear claw cake biscuit</li>
                                                    <li class="list-group-item">Soufflé pastry pie ice</li>
                                                    <li class="list-group-item">Tart tiramisu cake</li>
                                                    <li class="list-group-item">Bonbon toffee muffin</li>
                                                    <li class="list-group-item">Dragée tootsie roll</li>
                                                </ol>
                                            </div>
                                        </div> -->

                            <?php endif; ?>

                            <?php if ($activite['type_activite'] == 3) : ?>
                                <!-- Frais de déplacement journalier -->
                                <p class="mb-3">
                                    <span class="font-weight-bold">Frais de déplacement journalier : </span>
                                    <span><?= htmlspecialchars($activite['frais_deplacement_journalier']) ?> FCFA</span>
                                </p>

                                <!-- Taux par tâche -->
                                <p class="mb-0">
                                    <span class="font-weight-bold">Taux par tâche : </span>
                                    <span><?= htmlspecialchars($activite['taux_taches']) ?> FCFA</span>
                                </p>
                            <?php endif; ?>
                        </div>
                        <hr class="m-0">
                        <div class="card-body">
                            <!-- Boutons d'action -->
                            <div>
                                <a href="modifier_infos.php?id=<?= $activite['id'] ?>" class="btn btn-primary mr-2">Modifier les informations</a>

                                <!-- <a href="#" class="btn btn-outline-primary mr-2">Supprimer</a> -->

                                <!-- Autres options -->

                                <div class="btn-group dropup">
                                    <button type="button" class="dropdown-toggle btn btn-outline-primary" data-bs-toggle="dropdown" aria-expanded="false">Autres actions</button>

                                    <ul class="dropdown-menu shadow">
                                        <!-- <li>
                                            <hr class="dropwdown-divider">
                                        </li> -->
                                        <li>
                                            <a href="/gestion_participants/lier_participant_activite.php?id_activite=<?= $activite['id'] ?>" class="dropdown-item fs-6 custom-dropdown-item">Associer des participants</a>
                                        </li>
                                        <li>
                                            <a href="/<?= $chemin_note_generatrice ?>" class="dropdown-item fs-6 custom-dropdown-item" target="_blank">Visualiser la note génératrice de l'activité</a>
                                        </li>
                                        <li>
                                            <a href="#" class="dropdown-item text-danger fs-6 custom-dropdown-item">Supprimer</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h6 class="text-primary font-weight-bold">Participants associés</h6>
                        </div>
                        <div class="card-body">
                            <!-- Messages divers -->
                            <?php if (isset($_SESSION['liaison_reussie'])) : ?>
                                <?php afficherAlerte($_SESSION['liaison_reussie'], 'success');
                                unset($_SESSION['liaison_reussie'])
                                ?>
                            <?php endif; ?>
                            <!-- Messages divers -->

                            <?php if (count($participants_associes) == 0) : ?>
                                <div class="text-center">
                                    <p class="">Il semble que vous n'avez encore associé à votre activité aucun participant. Pourquoi ne pas le faire dès maintenant ?</p>
                                    <a href="/gestion_participants/lier_participant_activite.php?id_activite=<?= $activite['id'] ?>" class="btn btn-outline-primary">Associer des participants</a>
                                </div>
                            <?php else : ?>
                                <?php afficherSousFormeTableau($informations, 'table-responsive text-nowrap', 'table-bordered text-center', false, false) ?>
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
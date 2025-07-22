<?php
$section = 'Participants';
$titre_page = "Liste des acteurs";
require_once(__DIR__ . '/../includes/header.php');
// require_once(__DIR__ . '/../crypto.php');

$stmt = 'SELECT id_participant, nom, prenoms, matricule_ifu, date_naissance, lieu_naissance FROM participants WHERE id_user=' . $_SESSION['user_id'] . ' ORDER BY id_participant DESC';
$resultat = $bdd->query($stmt);

if (!$resultat) {
    redirigerVersPageErreur(500, obtenirURLcourant());
} else {
    // Les données sont récupérées
    while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC)) {
        $stmtBanques = $bdd->prepare("SELECT COUNT(*) AS total FROM informations_bancaires WHERE id_participant = ?");
        $stmtBanques->execute([$ligne['id_participant']]);
        $banqueCount = $stmtBanques->fetch(PDO::FETCH_ASSOC)['total'];
        // Intégrer le nombre de banques dans les données du participant
        $ligne['banque_count'] = $banqueCount;
        // Ajouter au tableau final
        $participants[] = $ligne;
    }
}
$resultat->closeCursor();

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

                    <?php if (isset($participants)) : ?>
                        <!-- Nous avons des participants -->

                        <!-- Page Heading -->
                        <h1 class="h4 mb-4 text-gray-800">Acteurs / <strong>Vos acteurs</strong></h1>
                        <p class="mt-2 mb-3">Ici vous avez la liste de tous les acteurs que vous avez déjà ajoutés. A partir des options disponibles vous pouvez modifier leurs informations, les supprimer, les associer à des activités, et bien plus.</p>

                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Liste des acteurs</h6>
                            </div>
                            <div class="card-body">
                                <!-- Messages divers -->

                                <!-- Liaison participant-activité réussie -->
                                <?php if (isset($_SESSION['liaison_reussie'])) : ?>
                                    <?php afficherAlerte($_SESSION['liaison_reussie'], 'success');
                                    unset($_SESSION['liaison_reussie'])
                                    ?>
                                <?php endif; ?>

                                <!-- Liaison participant-activité non autorisée -->
                                <?php if (isset($_SESSION['liaison_non_autorisee'])) : ?>
                                    <div class="alert alert-danger mt-2 text-center alert-dismissible">
                                        Cet acteur a déjà été lié à cette activité !
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                                    </div>
                                    <?php unset($_SESSION['liaison_non_autorisee']); ?>
                                <?php endif; ?>

                                <!-- Participant supprimé avec succès -->
                                <?php if (isset($_SESSION['suppression_acteur_ok'])) : ?>
                                    <div class="alert alert-success mt-2 text-center alert-dismissible">
                                        L'acteur a été supprimé avec succès !
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                                    </div>
                                    <?php unset($_SESSION['suppression_ok']); ?>
                                <?php endif; ?>

                                <?php if (isset($_SESSION['comptes_supprimes'])) : ?>
                                    <?php afficherAlerte('comptes_supprimes', 'success', true) ?>
                                <?php endif; ?>

                                <?php if (verifierDemoActive()) : ?>
                                    <!-- Des informations de démo ont été générées -->
                                    <?php
                                    $message = 'Il semble que vous avez généré les données de démonstration. <a href="/parametres/retrait_infos_demo.php" title="Cette option vous permet de supprimer toutes les informations de démonstration qui avaient été générées, vous permettant ainsi de ne garder que vos informations. Reférez-vous à la documentation pour plus d\'informations.">Cliquez ici</a> si vous souhaitez les retirer';
                                    ?>
                                    <?php afficherAlerte($message, 'info') ?>
                                <?php endif; ?>

                                <!-- Fin Messages divers -->

                                <form action="">
                                    <div class="">
                                        <table class="table table-bordered text-center" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Choix</th>
                                                    <th>Nom</th>
                                                    <th>Prénom(s)</th>
                                                    <th>Matricule IFU</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tfoot>
                                                <tr>
                                                    <th>Choix</th>
                                                    <th>Nom</th>
                                                    <th>Prénom(s)</th>
                                                    <th>Matricule/IFU</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </tfoot>
                                            <tbody>
                                                <?php foreach ($participants as $participant) : ?>

                                                    <tr>
                                                        <td><input type="checkbox" name="bref" id="bref"></th>
                                                        <td><?= htmlspecialchars($participant['nom']) ?></td>
                                                        <td><?= htmlspecialchars($participant['prenoms']) ?></td>
                                                        <td><?= htmlspecialchars($participant['matricule_ifu']) ?></td>

                                                        <!-- Options -->
                                                        <td>
                                                            <div class="btn-group">
                                                                <a href="/gestion_participants/gerer_participant.php?id=<?= chiffrer($participant['id_participant']) ?>" class="btn btn-primary">Gérer</a><br>

                                                                <button type="button" class="btn btn-primary btn-icon dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"></button>

                                                                <?php include('options.php') ?>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center pt-4">
                            <!-- Messages divers -->
                            <?php if (isset($_SESSION['suppression_acteur_ok'])) : ?>
                                <div class="alert alert-success mt-2 text-center alert-dismissible">
                                    L'acteur a été supprimé avec succès !
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                                </div>
                                <?php unset($_SESSION['suppression_acteur_ok']); ?>
                            <?php endif; ?>
                            <!-- Fin Messages divers -->
                            <h3 class="font-weight-bold">Aucun acteur retrouvé !</h3>
                            <p class="mt-3 text-center">Il semble que vous n'ayiez aucun acteur déjà ajouté. Pourquoi ne pas remédier à celà et en ajouter dès maintenant ?</p>
                            <a href="/gestion_participants/ajouter_participant" class="btn btn-outline-primary">Ajouter un acteur</a>
                            <div class="mt-5 mb-5">
                                <img src="/assets/illustrations/no-results-1.png" alt="no results" class="img-fluid" width="500">
                            </div>
                        </div>


                    <?php endif; ?>
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
    <!-- Autres modals -->
    <?php require_once(__DIR__ . '/../includes/modals.php') ?>
    <?php require_once(__DIR__ . '/../includes/scripts.php') ?>
</body>

</html>
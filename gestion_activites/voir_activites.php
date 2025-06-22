<?php
$titre = "Liste des activités";
require_once('includes/header.php');

$stmt = 'SELECT id, nom, description, date_debut, date_fin FROM activites ORDER BY id DESC LIMIT ' . NBR_ACTIVITES_A_AFFICHER;
$resultat = $bdd->query($stmt);

if (!$resultat) {
    redirigerVersPageErreur(500, obtenirURLcourant());
} else {
    // Les données sont récupérées
    while ($ligne = $resultat->fetch(PDO::FETCH_ASSOC)) {
        $activites[] = $ligne;
    }

    foreach ($activites as $index => $activite) {

        # Traitement de la description pour qu'elle n'excède pas 18 mots
        // $nbr_mots = 18;
        // $description = explode(' ', $activite['description']); // retourne dans un tableau les mots de la description

        // if (count($description) > $nbr_mots) {
        //     $description = array_slice($description, 0, $nbr_mots); // retourne les $nbr_mots premiers éléments du tableau
        //     $description[count($description)-1] = '...';
        // }

        // $description = implode(' ', $description);
        // $activites[$index]['description'] = $description;

        # Traitement des dates de début et de fin pour obtenir la période de l'activité
        $fmt = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE, 'Africa/Lagos', IntlDateFormatter::GREGORIAN);
        $activites[$index]['periode'] = "Du " . $fmt->format(new DateTime($activite['date_debut'])) . " au " . $fmt->format(new DateTime($activite['date_fin']));
    }
}
$resultat->closeCursor();
?>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php require_once('includes/sidebar.php') ?>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <?php require_once('includes/topbar.php') ?>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <?php if (isset($erreur_recuperation)) : ?>
                        <div class="alert alert-danger">La récupération des données a rencontré un problème.</div>
                    <?php endif; ?>

                    <?php

                    /** Bon je vais définir le mécanisme d'affichage des activités. Il faudra penser à un système de voir plus plus tard mais pour l'instant on se limite à trois activités
                     * 
                     * 1- Je détermine le nombre de rangées à faire apparaître. On affiche trois activités par ranger donc si le nombre d'activités est un multiple de 3, le nombre de rangées à afficher correspond tout simplement au nombre d'activités divisé par 3. Si ce nombre n'est pas un multiple, c'est qu'il y a un surplus donc ajoute dans ce cas le nombre de rangées initial + 1
                     * 
                     * 2- A présent il faut qu'on soit capable d'afficher uniquement trois activités par rangée. A cet effet on peut donc essayer une variable nombre d'activités affiché. QUand on affiche une activté on l'incrémente. S'il arrive à deux on saute le for actuel et on passe au suivant et on le réinitialise
                     * 
                     */
                    ?>
                    <?php if (isset($activites)) : ?>
                        <!-- Page Heading -->
                        <h1 class="h4 mb-4 text-gray-800">Activités / <strong>Vos activités</strong></h1>
                        <p class="mt-2">Ici vous avez accès à toutes les activités que vous avez créées. (Cliquez <a href="creer_activite.php">ici</a> pour en créer une autre)</p>

                        <?php
                        $valeur = 3; // nombre d'activités à afficher par ligne
                        $nbr_activites = count($activites);
                        $nbr_rangees = ($nbr_activites % $valeur == 0) ? $nbr_activites / $valeur : $nbr_activites / $valeur + 1;
                        $activites_affichees = 0; // nombre d'activités affichées
                        $activites_temp = $activites; // activites_temp sera détruit donc il me permet de conserver les activités pour des usages ultérieurs
                        ?>
                        <?php for ($i = 1; $i <= $nbr_rangees; $i++) : ?>
                            <?php // Affichage de chaque rangée 
                            ?>
                            <div class="row">
                                <?php for ($j = 1; $j <= $valeur; $j++) : ?>
                                    <?php if (count($activites_temp) != 0) : ?>
                                        <?php $activite_courante = array_shift($activites_temp) //on prend la première activité dans le tableau. Je précise que les activités sont classées dans le table dans l'ordre décroissant donc du plus récemment enregistré au plus anciennement enregistré
                                        ?>
                                        <div class="col-lg-<?= 12 / $valeur ?>">
                                            <div class="card mb-4">
                                                <div class="card-header">
                                                    <h6 class="text-primary font-weight-bold"><?= htmlspecialchars($activite_courante['nom']) ?></h6>
                                                </div>
                                                <div class="card-body">
                                                    <!-- <h5 class="card-title mb-3 text-primary"><strong><?= $activite_courante['nom'] ?></strong></h5> -->
                                                    <div class="card-subtitle text-muted mb-3"><strong>Période</strong> : <?= htmlspecialchars($activite_courante['periode']) ?></div>
                                                    <p class="card-text">
                                                        <?= htmlspecialchars(couperTexte($activite_courante['description'],18,100)) ?>
                                                    </p>
                                                    <a href="gerer_activite.php?id=<?= $activite_courante['id'] ?>" class="btn btn-outline-primary">Gérer l'activité</a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        <?php endfor; ?>
                    <?php else : ?>
                        <div class="text-center">
                            <h3 class="font-weight-bold">Aucune activité retrouvée !</h1>
                                <p class="mt-4 text-center">Il semble que vous n'ayiez aucune activité déjà créée. Pourquoi ne pas corriger le tir et en créer dès maintenant ?</p>
                                <a href="creer_activite.php" class="mt-4"><button class="btn btn-outline-primary">Créer une activité</button></a>
                                <div class="mt-5 mb-5">
                                    <img src="/assets/illustrations/no-results.png" alt="no results" class="img-fluid" width="400">
                                </div>
                        </div>
                    <?php endif; ?>


                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <?php require_once('includes/footer.php') ?>
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
    <?php require_once('includes/logoutModal.php') ?>
    <?php require_once('includes/scripts.php') ?>
</body>

</html>
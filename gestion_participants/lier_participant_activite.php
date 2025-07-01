<?php
$section = 'Participants';
$titre_page = 'Liaison Participant - Activité';
require_once(__DIR__ . '/../includes/header.php');
require_once('includes/traitements_lier_participant_activite.php');
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
                    <h1 class="h4 mb-4 text-gray-800">Participants / <strong>Liaison Participant - Activité</strong></h1>
                    <p class="mt-2">Liez vos participants à vos activités pour profiter de toutes les fonctionnalités disponibles !</p>

                    <div class="card shadow mb-4">
                        <div class="card-header">
                            <h6 class="font-weight-bold text-primary">
                                <?= ($etape_1 && !$etape_2) ? 'Etape 1' : 'Etape 2' ?>
                            </h6>
                        </div>

                        <div class="card-body">
                            <?php if ($etape_1 && !$etape_2) : ?>

                                <!-- On est encore à l'étape 1 -->

                                <p>Sélectionnez <?= ($sens == 0) ? 'l\'activité.' : 'le participant.' ?></p>
                                <?= ($aucun_participant_1 || $aucun_participant_2 || $aucune_activite_1 || $aucune_activite_2) ? '<hr>' : '' ?>
                                <form action="" method="post">
                                    <input type="hidden" name="<?= ($sens == 0) ? 'id_participant' : 'id_activite' ?>" value="<?= ($sens == 0) ? $id_participant : $id_activite ?>">

                                    <div class="table">
                                        <table class="table table-bordered text-center">
                                            <?php if ($sens == 0) : ?>
                                                <?php if ($aucune_activite_1) : ?>
                                                    <p>Il semble que vous ne disposez d'aucune activité ajoutée. <a href="/gestion_activites/creer_activite.php">Cliquez ici</a> pour créer de nouvelles activités auxquelles vous pourrez ensuite des participants. (<a href="<?= $_SESSION['previous_url'] ?>">Revenir à la page précédente</a>)</p>
                                                <?php elseif ($aucune_activite_2): ?>
                                                    <p class="">Il semble que vous avez déjà associé à ce participant toutes les activités que vous avez créés. <a href="/gestion_activites/creer_activite.php">Cliquez ici</a> pour créer une nouvelle activité. (<a href="<?= $_SESSION['previous_url'] ?>">Revenir à la page précédente</a>)</p>
                                                <?php else: ?>
                                                    <!-- Participant vers activité -->
                                                    <thead>
                                                        <tr>
                                                            <th>Choix</th>
                                                            <th>Nom</th>
                                                            <th>Période</th>
                                                            <th>Descritption</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($activites as $activite) : ?>
                                                            <tr>
                                                                <td><input type="checkbox" name="checkBoxTab[]" value="<?= $activite['id'] ?>"></td>
                                                                <td><?= htmlspecialchars($activite['nom']) ?></td>
                                                                <td><?= htmlspecialchars(determinerPeriode($activite['date_debut'], $activite['date_fin'])) ?></td>
                                                                <td><?= htmlspecialchars(couperTexte($activite['description'], 13, 100)) ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                <?php endif; ?>
                                            <?php elseif ($sens == 1): ?>
                                                <!-- Activité vers participant -->
                                                <?php if ($aucun_participant_1) : ?>
                                                    <p>Il semble que vous ne disposez d'aucun participant ajouté./ <a href="/gestion_participants/ajouter_participant.php">Cliquez ici</a> pour ajouter de nouveaux participants, ensuite vous pourrez les associer à l'activité. (<a href="<?= $_SESSION['previous_url'] ?>">Revenir à la page précédente</a>)</p>
                                                <?php elseif ($aucun_participant_2): ?>
                                                    <p class="">Il semble que vous avez déjà associé à cette activité tous les participants que vous avez créés. <a href="/gestion_participants/ajouter_participant.php">Cliquez ici</a> pour ajouter un nouveau participant, ensuite vous pourrez l'associer à l'activité. (<a href="<?= $_SESSION['previous_url'] ?>">Revenir à la page précédente</a>)</p>
                                                <?php else: ?>
                                                    <thead>
                                                        <tr>
                                                            <th>Choix</th>
                                                            <th>Nom</th>
                                                            <th>Prénom(s)</th>
                                                            <th>Matricule/IFU</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($participants as $participant) : ?>
                                                            <tr>
                                                                <td><input type="checkbox" name="participants_id[]" value="<?= $participant['id_participant'] ?>"></td>
                                                                <td><?= htmlspecialchars($participant['nom']) ?></td>
                                                                <td><?= htmlspecialchars($participant['prenoms']) ?></td>
                                                                <td><?= htmlspecialchars($participant['matricule_ifu']) ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>

                                                    <!-- Elément qui nous permette d'autoriser la sélection multiple -->
                                                    <span id="multi" class="d-none"></span>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </table>
                                    </div>

                                    <!-- Boutons d'actions -->
                                    <?php if (!$aucun_participant_1 && !$aucun_participant_2 && !$aucune_activite_1 && !$aucune_activite_2) : ?>
                                        <div class="mt-2">
                                            <button type="submit" class="btn btn-primary mr-2" id="submitBtn1" name='continuer'>Continuer</button>
                                            <a href="gerer_participant.php?id=<?= $id_participant ?>" class="btn btn-outline-primary">Annuler</a>
                                        </div>
                                    <?php endif; ?>
                                </form>
                            <?php elseif ($etape_2 && !$etape_1): ?>
                                <!-- Etape 2 -->
                                <!-- <p>Sélectionnez le titre pour lequel le participant sera associé à l'activité, le nombre de jours qu'il a effectué et le compte bancaire qu'on devra lui considérer pour l'activité.</p> -->
                                <form action="" method="post">
                                    <?php $i = 0; ?>
                                    <?php foreach ($participants as $participant) : ?>
                                        <input type="hidden" name="participants_id[]" value="<?= $participant['id_participant'] ?>">
                                        <div class="divider text-start">
                                            <div class="divider-text"><strong><?= htmlspecialchars($participant['nom'] . ' ' . $participant['prenoms']) ?></strong></div>
                                        </div>

                                        <!-- Titre -->
                                        <div class="mb-4 row">
                                            <label for="titre_<?= $i ?>" class="col-form-label col-sm-4">Titre</label>
                                            <div class="col-sm-8">
                                                <select name="titre[]" id="titre_<?= $i ?>" class="form-control <?= isset($erreurs['titre'][$i]) ? 'is-invalid' : '' ?>" aria-describdly=" titreAide_<?= $i ?>">
                                                    <option value="defaut" <?= (!isset($_POST['titre'][$i]) || !in_array($_POST['titre'][$i], $titres_intitules)) ? 'selected' : '' ?>>Choisissez le titre du participant</option>
                                                    <?php foreach ($titres as $titre) : ?>
                                                        <option value="<?= $titre['nom'] ?>" <?= (isset($erreurs) && $titre['nom'] == $_POST['titre'][$i]) ? 'selected' : '' ?>><?= htmlspecialchars($titre['nom']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>

                                                <?php if (isset($erreurs['titre'][$i])) : ?>
                                                    <div class="form-text" id="titreAide_<?= $i ?>">
                                                        <small class="text-danger"><?= $erreurs['titre'][$i][0] ?></small>
                                                    </div>
                                                <?php endif; ?>

                                                <small>Note : Ici vous avez la liste des titres que vous avez indiqué lors de la création de votre activité. Vous avez oublié d'enregistrer un titre ? <a href="/gestion_activites/modifier_infos.php?id=<?= $id_activite ?>">Cliquez ici</a> pour accéder aux informations de votre activité et modifier les titres qui lui sont associés.</small>
                                            </div>
                                        </div>

                                        <!-- Diplome -->
                                        <div class="mb-4 row">
                                            <label for="diplome_<?= $i ?>" class="col-form-label col-sm-4">Diplôme</label>
                                            <div class="col-sm-8">
                                                <select name="diplome[]" id="diplome<?= $i ?>" class="form-control <?= isset($erreurs['diplome'][$i]) ? 'is-invalid' : '' ?>" aria-describdly=" diplomeAide_<?= $i ?>">
                                                    <option value="defaut" <?= (!isset($_POST['diplome']) || !in_array($_POST['diplome'][$i], $diplomes)) ? 'selected' : '' ?>>Choisissez le diplôme du participant</option>
                                                    <?php foreach ($diplomes as $diplome) : ?>
                                                        <option value="<?= $diplome ?>" <?= (isset($erreurs) && $diplome == $_POST['diplome'][$i]) ? 'selected' : '' ?>><?= htmlspecialchars($diplome) ?></option>
                                                    <?php endforeach; ?>
                                                </select>

                                                <?php if (isset($erreurs['diplome'][$i])) : ?>
                                                    <div class="form-text" id="diplomeAide_<?= $i ?>">
                                                        <small class="text-danger"><?= $erreurs['diplome'][$i][0] ?></small>
                                                    </div>
                                                <?php endif; ?>

                                                <small>Note : Ici vous avez la liste des diplômes que vous avez indiqués lors de la création de votre activité. Vous avez oublié d'en enregistrer un ? <a href="/gestion_activites/modifier_infos.php?id=<?= $id_activite ?>">Cliquez ici</a> pour accéder aux informations de votre activité et modifier les diplômes qui lui sont associés.</small>
                                            </div>
                                        </div>

                                        <!-- Nombre de jours -->

                                        <div class="mb-4 row">
                                            <label for="nbr_jours_<?= $i ?>" class="col-form-label col-sm-4">Nombre de jours</label>
                                            <div class="col-sm-8">
                                                <input type="number" name="nbr_jours[]" id="nbr_jours_<?= $i ?>" placeholder="Indiquez le nombre de jours du participant" class="form-control <?= isset($erreurs['nbr_jours'][$i]) ? 'is-invalid' : '' ?>" value="<?= isset($erreurs) ? htmlspecialchars($_POST['nbr_jours'][$i]) : '' ?>" aria-describedby="nbr_joursAide_<?= $i ?>">

                                                <?php if (isset($erreurs['nbr_jours'][$i])) : ?>
                                                    <div class="form-text" id="nbr_joursAide_<?= $i ?>">
                                                        <small class="text-danger"><?= $erreurs['nbr_jours'][$i][0] ?></small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <?php if ($type_activite == 3) : ?>
                                            <!-- Champs additionnels -->
                                            <?php $champs = [
                                                'nbr_taches' => 'Nombre de tâches réalisées',
                                            ]
                                            ?>
                                            <div class="mb-2 row">
                                                <label for="nbr_taches" class="col-form-label col-sm-4">Nombre de tâches</label>
                                                <div class="col-sm-8">
                                                    <input type="number" name="nbr_taches" id="nbr_taches" class="form-control <?= isset($erreurs['nbr_taches']) ? 'is-invalid' : '' ?> " aria-describedby="<?= 'nbr_taches' ?>Aide" <?= (isset($erreurs) && isset($_POST['nbr_taches'])) ? 'value ="' . htmlspecialchars($_POST['nbr_taches']) . '"' : '' ?> placeholder="Indiquez le <?= strtolower($label) ?>">
                                                    <?php if (isset($erreurs['nbr_taches'])) : ?>
                                                        <div class="form-text" id="nbr_tachesAide">
                                                            <small class="text-danger"><?= $erreurs['nbr_taches'][0] ?></small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Compte bancaire -->

                                        <div class="mb-2 row">
                                            <span class="col-form-label col-sm-4">Compte(s) bancaire(s)</span>
                                            <div class="col-sm-8">
                                                <div class="d-flex col-form-label pb-0">
                                                    <?php $index = 0; ?>
                                                    <?php foreach ($comptes as $compte) : ?>
                                                        <?php $index++; ?>
                                                        <div class="form-check mr-4">
                                                            <input name="compte_bancaire[]" class="form-check-input" type="radio" value="<?= $compte['id'] ?>" id="compte<?= $index ?>_<?= $i ?>" <?= (isset($_POST['compte_bancaire'][$i]) && $compte['id'] == $_POST['compte_bancaire'][$i] && isset($erreurs)) ? 'checked' : '' ?> aria-describedby="compte_bancaireAide_<?= $i ?>">
                                                            <label class="form-check-label" for="compte<?= $index ?>_<?= $i ?>"> <?= htmlspecialchars($compte['banque']) . ' (<i>' . htmlspecialchars($compte['numero_compte']) . '</i>)' ?></label>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                <?php if (isset($erreurs['compte_bancaire'][$i])) : ?>
                                                    <div class="form-text" id="compte_bancaireAide_<?= $i ?>">
                                                        <small class="text-danger"><?= $erreurs['compte_bancaire'][$i][0] ?></small>
                                                    </div>
                                                <?php endif; ?>

                                                <small>Note : Sélectionnez le compte bancaire du participant qu'on devra considérer dans le cadre de l'activité.</small>
                                            </div>
                                        </div>

                                        <?php $i++ ?>
                                    <?php endforeach; ?>

                                    <!-- Boutons d'action -->
                                    <input type="hidden" name="continuer">
                                    <div class="divider text-start">
                                        <div class="divider-text"><strong>Vous avez terminé ?</strong></div>
                                    </div>
                                    <div class="mt-4">
                                        <button type="submit" class="btn btn-primary mr-2" name="lier">Réaliser la liaison</button>
                                        <a href="voir_participants.php" class="btn btn-outline-primary">Annuler</a>
                                    </div>
                                </form>
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
        const multi = document.getElementById('multi');
        if (multi) {
            console.log('on peut sélectionner de multiples participants');
        } else {
            // La sélection multiple n'est pas permise pour l'instant, on est notamment dans le cas du participant vers les activités
            let cbxes = document.querySelectorAll('input[type=checkbox]');
            const submitBtn1 = document.querySelector('#submitBtn1'); // Le bouton pour choisir l'activité
            // submitBtn.disabled = true;
            // console.log(submitBtn);

            cbxes.forEach(cbx => {
                cbx.addEventListener('change', (e) => {
                    if (e.target.checked)
                        uncheckOthers(e.target);
                })
            })

            function uncheckOthers(clicked) {
                cbxes.forEach(other => {
                    if (other != clicked)
                        other.checked = false;
                })
            }

            let submit = false;

            submitBtn1.addEventListener('click', (e) => {
                cbxes.forEach(cbx => {
                    if (cbx.checked == true)
                        submit = true;
                })
                if (!submit)
                    e.preventDefault();
            })
        }
    </script>
</body>

</html>
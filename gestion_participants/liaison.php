<?php
// Avant toute chose je dois disposer soit de l'id du participant si on va dans le sens participant vers activités ou de l'id de l'activité si on va dans l'autre sens. Pareillement je pourrais disposer uniquement de la variable 'modifier' dans le cas d'une modification des informations donc on checke ces trois éléments et si ils sont absents on redirige purement et simplement vers la page d'erreur

if (!isset($_GET['id_participant']) && !isset($_GET['id_activite']) && !isset($_GET['modifier']) && !isset($_GET['sens'])) {
    redirigerVersPageErreur(404);
    exit;
}

if (isset($_GET['id_participant'])) {
    $titre_page = 'Liaison Acteur - Activités';
    $section = 'Participants';
} elseif (isset($_GET['id_activite'])) {
    $titre_page = 'Liaison Activité - Acteurs';
    $section = 'Activités';
} elseif (isset($_GET['modifier'])) {
    $titre_page = 'Modification Liaison';
    $redirect = true;
    $sens = filter_input(INPUT_GET, 'sens', FILTER_VALIDATE_INT);

    if (in_array($_GET['sens'], [0, 1])) {
        $redirect = false;
        if ($_GET['sens'] == 0) {
            $section = 'Participants';
        } elseif ($_GET['sens'] == 1) {
            $section = 'Activités';
        }
    }
}
require_once(__DIR__ . '/../includes/header.php');
if (isset($redirect) && $redirect) {
    // Essentiellement pour le cas d'une modification de la liaison
    redirigerVersPageErreur(404);
}
require_once('includes/liaison.php');

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body id='page-top'>

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
                    <div>

                        <h1 class="h4 mb-4 text-gray-800">
                            <?php if ($sens == 0) : ?>
                                Acteur / <strong>Activités</strong>
                            <?php elseif ($sens == 1): ?>
                                Activité / <strong>Acteurs</strong>
                            <?php elseif (isset($modification)): ?>
                                Modification - Liaison
                            <?php endif; ?>
                        </h1>

                        <p class="mt-2"> <?= !isset($modification) ? 'Liez vos acteurs à vos activités pour profiter de toutes les fonctionnalités disponibles !' : 'Vous avez fait une erreur lors de la liaison de votre acteur à l\'activité ? Vous êtes au bon endroit !' ?></p>
                    </div>
                    <!-- Messages en cas d'erreur -->
                    <?php if ($sens == 0) : ?>
                        <?php if ($aucune_activite_1) : ?>
                            <hr>
                            <h4 class="font-weight-bold mb-2">Aucune activité retrouvée</h4>
                            <p>Il semble que vous ne disposez d'aucune activité ajoutée. <a href="/gestion_activites/creer_activite.php">Cliquez ici</a> pour créer de nouvelles activités auxquelles vous pourrez ensuite associer des acteurs. (<a href="<?= $_SESSION['previous_url'] ?>">Revenir à la page précédente</a>)</p>
                        <?php elseif ($aucune_activite_2): ?>
                            <hr>
                            <h4 class="font-weight-bold mb-2">Aucune activité retrouvée</h4>
                            <p class="">Il semble que vous avez déjà associé à cet acteur toutes les activités que vous avez créées. <a href="/gestion_activites/creer_activite.php">Cliquez ici</a> pour créer une nouvelle activité. (<a href="<?= $_SESSION['previous_url'] ?>">Revenir à la page précédente</a>)</p>
                        <?php endif; ?>
                    <?php elseif ($sens == 1): ?>

                    <?php endif; ?>
                    <!-- Fin Messages en cas d'erreur -->

                    <?php if (!$aucune_activite_1 && !$aucune_activite_2 && !$aucun_participant_1 && !$aucun_participant_2) : ?>
                        <div class="card shadow mb-4">
                            <div class="card-header">
                                <h6 class="font-weight-bold text-primary">
                                    <?= isset($modification) ? 'Formulaire de modification' : ($etape_1 ? 'Etape 1' : 'Etape 2') ?>
                                </h6>
                            </div>

                            <div class="card-body<?= $etape_2 ? ' pt-0' : '' ?>">
                                <?php if ($etape_1) : ?>
                                    <!-- Etape 1 : Sélections -->

                                    <?php if ($sens == 0) : ?>
                                        <!-- Participant vers activités -->
                                        <p>Sélectionnez les activités auxquelles vous souhaitez associer l'acteur</p>
                                        <?php
                                        $informations[0] = ['Nom', 'Description', 'Centre'];
                                        $cbxs[0] = 'activites_id';
                                        foreach ($activites as $activite) {
                                            $informations[1][] = [$activite['nom'], $activite['description'], $activite['centre']];
                                            $cbxs[1][] = $activite['id'];
                                        }
                                        ?>

                                        <form action="" method="post" class="pt-2">
                                            <input type="hidden" name="id_participant" value="<?= $id_participant ?>">
                                            <!-- Tableau -->
                                            <?php afficherSousFormeTableau($informations, 'table-responsive', 'table-bordered text-center', true, false, $cbxs); ?>
                                            <!-- Boutons d'actions -->
                                            <?php if (!$aucune_activite_1 && !$aucune_activite_2) : ?>
                                                <div class="mt-4">
                                                    <button type="submit" class="btn btn-primary mr-2" id="submitBtn1" name='continuer'>Continuer</button>
                                                    <a href="<?= $_SESSION['previous_url'] ?>" class="btn btn-outline-primary">Annuler</a>
                                                </div>
                                            <?php endif; ?>
                                        </form>
                                    <?php elseif ($sens == 1): ?>
                                        <!-- Activité vers Participants -->
                                        <p>Sélectionnez les acteurs que vous souhaitez associer à l'activité</p>
                                        <?php
                                        $informations[0] = ['Nom', 'Prénom(s)', 'Matricule IFU'];
                                        $cbxs[0] = 'participants_id';

                                        foreach ($participants as $participant) {
                                            $informations[1][] = [$participant['nom'], $participant['prenoms'], $participant['matricule_ifu']];
                                            $cbxs[1][] = $participant['id_participant'];
                                        }
                                        ?>
                                        <form action="" method="post" class="pt-2">
                                            <input type="hidden" name="id_activite" value="<?= $id_activite ?>">
                                            <!-- Tableau -->
                                            <?php afficherSousFormeTableau($informations, 'table-responsive', 'table-bordered text-center', true, false, $cbxs) ?>
                                            <!-- Boutons d'actions -->
                                            <?php if (!$aucun_participant_1 && !$aucun_participant_2) : ?>
                                                <div class="mt-4">
                                                    <button type="submit" class="btn btn-primary mr-2" id="submitBtn1" name='continuer'>Continuer</button>
                                                    <a href="<?= $_SESSION['previous_url'] ?>" class="btn btn-outline-primary">Annuler</a>
                                                </div>
                                            <?php endif; ?>
                                        </form>

                                    <?php else: ?>
                                        <p>Un comportement inattendu</p>
                                    <?php endif; ?>
                                <?php elseif ($etape_2): ?>
                                    <form action="" method="post">
                                        <?php if ($modification) : ?>
                                            <div class="divider text-start">
                                                <div class="divider-text"><strong><?= $sens == 0 ? htmlspecialchars($participant['nom'] . ' ' . $participant['prenoms'] . ' & ' . $activites[0]['nom']) : htmlspecialchars($activite['nom'] . ' & ' . $participants[0]['nom'] . ' ' . $participants[0]['prenoms']) ?></strong></div>
                                            </div>
                                        <?php endif; ?>
                                        <?php $i = 0; ?>
                                        <?php if ($sens == 0) : ?>
                                            <?php foreach ($activites as $activite) : ?>
                                                <?php if (!$modification) : ?>
                                                    <input type="hidden" name="activites_id[]" value="<?= $activite['id'] ?>">
                                                    <div class="divider text-start">
                                                        <div class="divider-text"><strong><?= htmlspecialchars($activite['nom']) ?></strong></div>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Titre  -->

                                                <div class="mb-4 row">
                                                    <label for="titre_<?= $i ?>" class="col-form-label col-sm-4">Titre</label>
                                                    <div class="col-sm-8">
                                                        <select name="titre[<?= $i ?>]" id="titre_<?= $i ?>" class="form-control <?= isset($erreurs['titre'][$i]) ? 'is-invalid' : '' ?>" aria-describdly=" titreAide_<?= $i ?>">
                                                            <option value="defaut" <?= ((!isset($_POST['titre'][$i]) || !in_array($_POST['titre'][$i], $titres_intitules[$i])) && !$modification) ? 'selected' : '' ?>>Choisissez le titre de l'acteur</option>
                                                            <?php foreach ($titres_intitules[$i] as $titre) : ?>
                                                                <option value="<?= $titre ?>" <?= isset($erreurs) ? ($titre == $_POST['titre'][$i] ? 'selected' : '') : ($modification && $titre == $infos_liaison['titre_liaison'] ? 'selected' : '') ?>><?= htmlspecialchars($titre) ?></option>
                                                            <?php endforeach; ?>
                                                        </select>

                                                        <?php if (isset($erreurs['titre'][$i])) : ?>
                                                            <div class="form-text" id="titreAide_<?= $i ?>">
                                                                <small class="text-danger"><?= $erreurs['titre'][$i][0] ?></small>
                                                            </div>
                                                        <?php endif; ?>

                                                        <small>Note : Ici vous avez la liste des titres que vous avez indiqué lors de la création de votre activité. Vous avez oublié d'enregistrer un titre ? <a href="/gestion_activites/modifier_infos.php?id=<?= $activite['id'] ?>">Cliquez ici</a> pour accéder aux informations de votre activité et modifier les titres qui lui sont associés.</small>
                                                    </div>
                                                </div>

                                                <!-- Nombre de jours -->
                                                <div class="mb-4 row">
                                                    <label for="nbr_jours_<?= $i ?>" class="col-form-label col-sm-4">Nombre de jours</label>
                                                    <div class="col-sm-8">
                                                        <input type="number" name="nbr_jours[<?= $i ?>]" id="nbr_jours_<?= $i ?>" placeholder="Indiquez le nombre de jours de l'acteur" class="form-control <?= isset($erreurs['nbr_jours'][$i]) ? 'is-invalid' : '' ?>" value="<?= isset($erreurs) ? htmlspecialchars($_POST['nbr_jours'][$i]) : ($modification ? $infos_liaison['nbr_jours'] : '') ?>" aria-describedby="nbr_joursAide_<?= $i ?>" min="1">

                                                        <?php if (isset($erreurs['nbr_jours'][$i])) : ?>
                                                            <div class="form-text" id="nbr_joursAide_<?= $i ?>">
                                                                <small class="text-danger"><?= $erreurs['nbr_jours'][$i][0] ?></small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <?php if ($activite['type_activite'] == 3) : ?>
                                                    <!-- Nombre de tâches -->
                                                    <div class="mb-2 row">
                                                        <label for="nbr_taches_<?= $i ?>" class="col-form-label col-sm-4">Nombre de tâches</label>
                                                        <div class="col-sm-8">
                                                            <input type="number" name="nbr_taches[<?= $i ?>]" id="nbr_taches_<?= $i ?>" class="form-control <?= isset($erreurs['nbr_taches'][$i]) ? 'is-invalid' : '' ?> " value="<?= isset($erreurs) ? htmlspecialchars($_POST['nbr_taches'][$i]) : ($modification ? $infos_liaison['nbr_taches'] : '') ?>" placeholder="Indiquez le nombre de tâches réalisées" aria-describedby="nbr_tachesAide_<?= $i ?>" min="1">

                                                            <?php if (isset($erreurs['nbr_taches'][$i])) : ?>
                                                                <div class="form-text" id="nbr_tachesAide_<?= $i ?>">
                                                                    <small class="text-danger"><?= $erreurs['nbr_taches'][$i][0] ?></small>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Compte bancaire -->

                                                <div class="mb-2 row">
                                                    <span class="col-form-label col-sm-4"><?= count($comptes) > 1 ? 'Comptes bancaires' : 'Compte bancaire' ?></span>
                                                    <div class="col-sm-8">
                                                        <div class="col-form-label pb-0">
                                                            <?php $index = 0; ?>
                                                            <div class="row">
                                                                <?php foreach ($comptes as $compte) : ?>
                                                                    <div class="col mb-2">
                                                                        <?php $index++; ?>
                                                                        <div class="form-check mr-4">
                                                                            <input name="compte_bancaire[<?= $i ?>]" class="form-check-input" type="radio" value="<?= $compte['id'] ?>" id="compte<?= $index ?>_<?= $i ?>" <?= isset($erreurs) ? isset($_POST['compte_bancaire'][$i]) && ($compte['id'] == $_POST['compte_bancaire'][$i] ? 'checked' : '') : ($modification && $compte['numero_compte'] == $infos_liaison['numero_compte'] ? 'checked' : '') ?> aria-describedby="compte_bancaireAide_<?= $i ?>" <?= $index == 1 ? ' checked' : '' ?>>
                                                                            <label class="form-check-label" for="compte<?= $index ?>_<?= $i ?>"> <?= htmlspecialchars($compte['banque']) . ' (<i>' . htmlspecialchars($compte['numero_compte']) . '</i>)' ?></label>
                                                                        </div>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            </div>

                                                        </div>
                                                        <?php if (isset($erreurs['compte_bancaire'][$i])) : ?>
                                                            <div class="form-text" id="compte_bancaireAide_<?= $i ?>">
                                                                <small class="text-danger"><?= $erreurs['compte_bancaire'][$i][0] ?></small>
                                                            </div>
                                                        <?php endif; ?>

                                                        <?php if (count($comptes) > 1) : ?>
                                                            <small>Note : Sélectionnez le compte bancaire de l'acteur qu'on devra considérer dans le cadre de l'activité.</small>
                                                        <?php endif; ?>

                                                        <?php if (count($comptes) < NOMBRE_MAXIMAL_COMPTES) : ?>
                                                            <small> <a href="/gestion_participants/ajouter_comptes.php?id_participant=<?= $id_participant ?>"></a> Cliquez ici si vous avez besoin d'ajouter à cet acteur des comptes bancaires.</small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php $i++ ?>
                                            <?php endforeach; ?>
                                        <?php elseif ($sens == 1): ?>
                                            <?php foreach ($participants as $participant) : ?>
                                                <?php if (!$modification) : ?>
                                                    <input type="hidden" name="participants_id[]" value="<?= $participant['id_participant'] ?>">
                                                    <div class="divider text-start">
                                                        <div class="divider-text"><strong><?= htmlspecialchars($participant['nom'] . ' ' . $participant['prenoms']) ?></strong></div>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Titre -->
                                                <div class="mb-4 row">
                                                    <label for="titre_<?= $i ?>" class="col-form-label col-sm-4">Titre</label>
                                                    <div class="col-sm-8">
                                                        <select name="titre[<?= $i ?>]" id="titre_<?= $i ?>" class="form-control <?= isset($erreurs['titre'][$i]) ? 'is-invalid' : '' ?>" aria-describdly=" titreAide_<?= $i ?>">
                                                            <option value="defaut" <?= ((!isset($_POST['titre'][$i]) || !in_array($_POST['titre'][$i], $titres_intitules)) && !$modification) ? 'selected' : '' ?>>Choisissez le titre du participant</option>
                                                            <?php foreach ($titres as $titre) : ?>
                                                                <option value="<?= $titre['nom'] ?>" <?= isset($erreurs) ? ($titre['nom'] == $_POST['titre'][$i] ? 'selected' : '') : ($modification && $titre['nom'] == $infos_liaison['titre_liaison'] ? 'selected' : '') ?>><?= htmlspecialchars($titre['nom']) ?></option>
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

                                                <!-- Nombre de jours -->

                                                <div class="mb-4 row">
                                                    <label for="nbr_jours_<?= $i ?>" class="col-form-label col-sm-4">Nombre de jours</label>
                                                    <div class="col-sm-8">
                                                        <input type="number" name="nbr_jours[<?= $i ?>]" id="nbr_jours_<?= $i ?>" placeholder="Indiquez le nombre de jours de l'acteur" class="form-control <?= isset($erreurs['nbr_jours'][$i]) ? 'is-invalid' : '' ?>" value="<?= isset($erreurs) ? htmlspecialchars($_POST['nbr_jours'][$i]) : ($modification ? $infos_liaison['nbr_jours'] : '') ?>" aria-describedby="nbr_joursAide_<?= $i ?>" min="1">

                                                        <?php if (isset($erreurs['nbr_jours'][$i])) : ?>
                                                            <div class="form-text" id="nbr_joursAide_<?= $i ?>">
                                                                <small class="text-danger"><?= $erreurs['nbr_jours'][$i][0] ?></small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <?php if ($type_activite == 3) : ?>
                                                    <!-- Champs additionnels -->
                                                    <div class="mb-2 row">
                                                        <label for="nbr_taches_<?= $i ?>" class="col-form-label col-sm-4">Nombre de tâches</label>
                                                        <div class="col-sm-8">
                                                            <input type="number" name="nbr_taches[<?= $i ?>]" id="nbr_taches_<?= $i ?>" class="form-control <?= isset($erreurs['nbr_taches'][$i]) ? 'is-invalid' : '' ?> " value="<?= isset($erreurs) ? htmlspecialchars($_POST['nbr_taches'][$i]) : ($modification ? $infos_liaison['nbr_taches'] : '') ?>" placeholder="Indiquez le nombre de tâches réalisées" aria-describedby="nbr_tachesAide_<?= $i ?>">

                                                            <?php if (isset($erreurs['nbr_taches'][$i])) : ?>
                                                                <div class="form-text" id="nbr_tachesAide_<?= $i ?>">
                                                                    <small class="text-danger"><?= $erreurs['nbr_taches'][$i][0] ?></small>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Compte bancaire -->
                                                <?php $index_participant = array_search($participant, $participants) ?>

                                                <div class="mb-2 row">
                                                    <span class="col-form-label col-sm-4"><?= count($comptes[$index_participant]) > 1 ? 'Comptes bancaires' : 'Compte bancaire' ?></span>
                                                    <div class="col-sm-8">
                                                        <div class="d-flex col-form-label pb-0">
                                                            <?php $index = 0; ?>
                                                            <?php foreach ($comptes[$i] as $compte) : ?>
                                                                <?php $index++; ?>
                                                                <div class="form-check mr-4">
                                                                    <input name="compte_bancaire[<?= $i ?>]" class="form-check-input" type="radio" value="<?= $compte['id'] ?>" id="compte<?= $index ?>_<?= $i ?>" <?= isset($erreurs) ? ($compte['id'] == $_POST['compte_bancaire'][$i] ? 'checked' : '') : ($modification && $compte['numero_compte'] == $infos_liaison['numero_compte'] ? 'checked' : '') ?> aria-describedby="compte_bancaireAide_<?= $i ?>" <?= $index == 1 ? ' checked' : '' ?>>
                                                                    <label class="form-check-label" for="compte<?= $index ?>_<?= $i ?>"> <?= htmlspecialchars($compte['banque']) . ' (<i>' . htmlspecialchars($compte['numero_compte']) . '</i>)' ?></label>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                        <?php if (isset($erreurs['compte_bancaire'][$i])) : ?>
                                                            <div class="form-text" id="compte_bancaireAide_<?= $i ?>">
                                                                <small class="text-danger"><?= $erreurs['compte_bancaire'][$i][0] ?></small>
                                                            </div>
                                                        <?php endif; ?>

                                                        <?php if (count($comptes[$index_participant]) > 1) : ?>
                                                            <small>Note : Sélectionnez le compte bancaire de l'acteur qu'on devra considérer dans le cadre de l'activité.</small>
                                                        <?php endif; ?>

                                                        <?php if (count($comptes[$index_participant]) < NOMBRE_MAXIMAL_COMPTES) : ?>
                                                            <small> <a href="/gestion_participants/ajouter_comptes.php?id_participant=<?= $participant['id_participant'] ?>">Cliquez ici</a> si vous avez besoin d'ajouter à cet acteur des comptes bancaires.</small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <?php $i++ ?>
                                            <?php endforeach; ?>

                                        <?php endif; ?>

                                        <!-- Boutons d'action -->
                                        <input type="hidden" name="continuer">
                                        <div class="divider text-start">
                                            <div class="divider-text"><strong>Vous avez terminé ?</strong></div>
                                        </div>
                                        <div class="mt-4">
                                            <button type="submit" class="btn btn-primary mr-2" name="<?= !$modification ? 'lier' : 'enregistrer' ?>"> <?= !$modification ? 'Réaliser la liaison' : 'Enregistrer les modifications' ?></button>
                                            <a href="<?= $_SESSION['previous_url'] ?>" class="btn btn-outline-primary">Annuler</a>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <p>Etrange résultat</p>
                                <?php endif; ?>
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
    <?php require_once(__DIR__ . '/../includes/scripts.php') ?>

    <script>
        <?php if ($etape_1) : ?>
            let cbxs = document.querySelectorAll('input[type=checkbox]');
            const submitBtn1 = document.querySelector('#submitBtn1'); // Le bouton 'Continuer'
            let submit = false;
            submitBtn1.addEventListener('click', (e) => {
                cbxs.forEach(cbx => {
                    if (cbx.checked == true) {
                        submit = true;
                    }
                })

                if (!submit) {
                    e.preventDefault();
                }
            })
        <?php endif; ?>
    </script>

</body>

</html>
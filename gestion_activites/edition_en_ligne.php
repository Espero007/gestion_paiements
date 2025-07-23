<?php
$section = 'Activités';
$titre_page = "Edition en ligne";
require_once(__DIR__ . '/../includes/header.php');
require_once('traitements/edition.php');
?>
<link rel="stylesheet" href="traitements/edition_en_ligne.css">

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <?php // require_once(__DIR__ . '/../includes/sidebar.php')
        ?>
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
                    <h1 class="h4 mb-4 text-gray-800">Activités / Génération de documents / <strong>Edition en ligne</strong></h1>
                    <!-- <div class="d-sm-flex align-items-center justify-content-between mb-0">
                    </div> -->
                    <p class="mt-2">Vous êtes sur la page d'édition en ligne de vos documents à générer. C'est ici que vous indiquez les valeurs que vous aimeriez garder pour l'entête de vos documents. Veuillez remplir tous les champs adéquatement. (Pour le cas d'espèce, l'entête correspond à celle d'un état de paiement)</p>

                    <div class="container-fluid p-0 mb-4 mt-4">
                        <div class="row gx-3">
                            <!-- Début formulaire  -->
                            <div class="col-6 ">
                                <div class="card">
                                    <div class="card-body pb-0 pl-0 pr-0">
                                        <h6 class="font-weight-bolder text-center mb-3 text-primary">Entrées</h6>
                                    </div>
                                    <hr class="m-0">
                                    <div class="card-body">
                                        <small><strong>Note</strong> : Chacune des valeurs que vous saisirez sera utilisée pour remplir vos documents. Si vous ne saisissez rien, les valeurs par défaut de chaque document seront maintenues.</small>

                                        <form action="" method="post">
                                            <!-- Informations de l'entête -->
                                            <fieldset>
                                                <legend>
                                                    <div class="divider text-start my-0">
                                                        <div class="divider-text"><strong>Bloc de gauche</strong></div>
                                                    </div>
                                                </legend>
                                                <?php foreach ($bloc_gauche as $champ => $label) : ?>
                                                    <div class="mb-2 row">
                                                        <label for="<?= $champ ?>" class="col-sm-3 col-form-label"><?= $label ?></label>
                                                        <div class="col-sm-9">
                                                            <input id="<?= $champ ?>" type="text" name="<?= $champ ?>" class="form-control<?= isset($erreurs[$champ]) ? ' is-invalid' : '' ?>" value="<?= isset($erreurs) ? (isset($_POST[$champ]) ? htmlspecialchars($_POST[$champ]) : '') : ($modification ? $informations[$champ] : '') ?>" placeholder="Entrez une valeur" <?= isset($erreurs[$champ]) ? 'aria-describedby="' . $champ . 'Aide"' : '' ?>>

                                                            <?php if (isset($erreurs[$champ])) : ?>
                                                                <div id="<?= $champ ?>Aide">
                                                                    <small class="text-danger"><?= $erreurs[$champ] ?? '' ?></small>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </fieldset>

                                            <!-- Bloc de droite -->

                                            <fieldset>
                                                <legend>
                                                    <div class="divider text-start my-0">
                                                        <div class="divider-text"><strong>Bloc de droite</strong></div>
                                                    </div>
                                                </legend>

                                                <?php foreach ($bloc_droite as $champ => $label) : ?>
                                                    <div class="mb-2 row">
                                                        <label for="<?= $champ ?>" class="col-sm-3 col-form-label"><?= $label ?></label>
                                                        <div class="col-sm-9">
                                                            <input id="<?= $champ ?>" type="text" name="<?= $champ ?>" class="form-control<?= isset($erreurs[$champ]) ? ' is-invalid' : '' ?>" value="<?= isset($erreurs) ? (isset($_POST[$champ]) ? htmlspecialchars($_POST[$champ]) : '') : ($modification ? $informations[$champ] : '') ?>" placeholder="Entrez une valeur" <?= isset($erreurs[$champ]) ? 'aria-describedby="' . $champ . 'Aide"' : '' ?>>

                                                            <?php if (isset($erreurs[$champ])) : ?>
                                                                <div id="<?= $champ ?>Aide">
                                                                    <small class="text-danger"><?= $erreurs[$champ] ?? '' ?></small>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </fieldset>

                                            <!-- Boutons -->

                                            <div class="mt-4">
                                                <button type="submit" class="d-none d-sm-inline-block btn btn-primary shadow-sm" name="enregistrer"><i class="fas fa-save text-white-50 mr-2"></i> Enregistrer les informations</button>
                                                <a href="<?= $_SESSION['previous_url'] ?>" class="d-none d-sm-inline-block btn btn-secondary shadow-sm ml-2">Annuler</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <!-- Fin Formulaire -->

                            <!-- Début Prévisualisation -->
                            <div class="col-6">
                                <div class="card">
                                    <div class="card-body pb-0 pl-0 pr-0">
                                        <h6 class="font-weight-bolder text-center mb-3 text-primary">Aperçu des modifications</h6>
                                    </div>
                                    <hr class="m-0">
                                    <div class="card-body">
                                        <div class="row mb-4" id="entete">
                                            <div class="col-6 text-center fs-6 text-uppercase" id="blocGauche">
                                                <p class="m-0"><span id="prev_ligne1">REPUBLIQUE DU BENIN <strong class="text-capitalize">(ligne 1)</strong></span></p>
                                                <p class="m-0">*-*-*-*-*</p>
                                                <p class="m-0"><span id="prev_ligne2"><span>MINISTERE DE </span>............<strong class="text-capitalize">(ligne 2)</strong></span></p>
                                                <p class="m-0">*-*-*-*-*</p>
                                                <p class="m-0"><span id="prev_ligne3">DIRECTION DES......<strong class="text-capitalize">(ligne 3)</strong></span></p>
                                                <p class="m-0">*-*-*-*-*</p>
                                                <p class="m-0"><span id="prev_ligne4">SERVICE .........<strong class="text-capitalize">(ligne 4)</strong></span></p>
                                                <p class="m-0">*-*-*-*-*</p>
                                            </div>
                                            <div class="col-6 text-center" id="blocDroite">
                                                <p><span id="prev_ville" class="text-capitalize"><strong>(Ville)</strong></span>, le <span id="prev_date"><strong>(Date)</strong></span></p>
                                                <p class="text-uppercase font-weight-bold">Titre</p>
                                                <p class="">Sous-titre..., <span id="prev_texte">SESSION 2020 <strong>(Texte)</strong></span></p>
                                            </div>
                                        </div>

                                        <div class="mb-4" id="milieu">
                                            <?php
                                            $stmt = $bdd->query('SELECT timbre, reference FROM activites WHERE id=' . $id_activite);
                                            $resultats = $stmt->fetch(PDO::FETCH_ASSOC);
                                            $reference = $resultats['reference'];
                                            $timbre = $resultats['timbre'];
                                            $stmt->closeCursor();
                                            ?>
                                            <div class="container-fluid p-0">
                                                <div class="row">
                                                    <div class="col-2" style="padding : 0 0.75rem"><strong>N°</strong></div>
                                                    <div class="col-10"><?= htmlspecialchars($timbre) ?></div>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-1" style="padding : 0 0.75rem"><strong class="text-decoration-unerderline">REF</strong></div>
                                                    <div class="col-11"><?= htmlspecialchars($reference) ?></div>
                                                </div>

                                            </div>
                                        </div>

                                        <div class="mb-4" id="tableau">
                                            <table class="table table-bordered" width="100%" cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th>Element 1</th>
                                                        <th>Element 2</th>
                                                        <th>Element 3</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>Contenu ligne 1...</td>
                                                        <td>Contenu ligne 1...</td>
                                                        <td>Contenu ligne 1...</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Contenu ligne 2...</td>
                                                        <td>Contenu ligne 2...</td>
                                                        <td>Contenu ligne 2...</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Contenu ligne 3...</td>
                                                        <td>Contenu ligne 3...</td>
                                                        <td>Contenu ligne 3...</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="text-center mb-4" id="bas">
                                            <p class="m-0 font-weight-bold">LE CMAP</p>
                                            <p class="m-0">(le titre du 1er responsable et son nom)</p>
                                            <br>
                                            <p class="m-0 text-decoration-underline">Hui P. BOKO</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Fin Prévisualisation -->

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
        const champLigne1 = document.getElementById('ligne1');
        const champLigne2 = document.getElementById('ligne2');
        const champLigne3 = document.getElementById('ligne3');
        const champLigne4 = document.getElementById('ligne4');
        const champVille = document.getElementById('ville');
        const champDate = document.getElementById('date1');
        const champLigne5 = document.getElementById('ligne5');

        const prevLigne1 = document.getElementById('prev_ligne1');
        const prevLigne2 = document.getElementById('prev_ligne2');
        const prevLigne3 = document.getElementById('prev_ligne3');
        const prevLigne4 = document.getElementById('prev_ligne4');
        const prevVille = document.getElementById('prev_ville');
        const prevDate = document.getElementById('prev_date');
        const prevLigne5 = document.getElementById('prev_texte');

        const champs = [champLigne1, champLigne2, champLigne3, champLigne4, champLigne5, champVille, champDate];
        const prevs = [prevLigne1, prevLigne2, prevLigne3, prevLigne4, prevLigne5, prevVille, prevDate];
        const valeursDefaut = ['REPUBLIQUE DU BENIN <strong class="text-capitalize">(ligne 1)</strong>', 'MINISTERE DE </span>............<strong class="text-capitalize">(ligne 2)</strong>', 'DIRECTION DES......<strong class="text-capitalize">(ligne 3)</strong>', 'SERVICE .........<strong class="text-capitalize">(ligne 4)</strong>', 'SESSION 2020 <strong>(Texte)</strong>', '<strong>(Ville)</strong>', '<strong>(Date)</strong'];

        function updatePreview(input, prev) {
            prev.textContent = input.value;
        }

        for (let i = 0; i < champs.length; i++) {
            const input = champs[i];
            const prev = prevs[i];
            input.addEventListener('input', () => updatePreview(input, prev));
            if (input.value.trim() != '') {
                updatePreview(input, prev);
            }
        }

        document.addEventListener('keyup', () => {
            champs.forEach(input => {
                if (input.value.trim() === "") {
                    let index = champs.indexOf(input);
                    prevs[index].innerHTML = valeursDefaut[index];
                }
            })
        })
    </script>
</body>

</html>
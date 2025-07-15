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
                    <form action="" method="post">
                        <div class="d-sm-flex align-items-center justify-content-between mb-0">
                            <h1 class="h4 mb-4 text-gray-800">Activités / Génération de documents / <strong>Edition en ligne</strong></h1>
                            <div>
                                <button type="submit" class="d-none d-sm-inline-block btn btn-primary shadow-sm" name="enregistrer"><i class="fas fa-save text-white-50 mr-2"></i> Enregistrer les informations</button>

                                <a href="<?= $_SESSION['previous_url'] ?>" class="d-none d-sm-inline-block btn btn-secondary shadow-sm ml-2">Annuler</a>
                            </div>
                        </div>
                        <p class="mt-2">Vous êtes sur la page d'édition en ligne de vos documents à générer. C'est ici que vous indiquez les valeurs que vous aimeriez garder pour l'entête de vos documents par exemple, et bien d'autres...</p>

                        <div class="container-fluid p-0 mb-4">
                            <div class="row gx-3">
                                <div class="col-6 ">
                                    <div class="card">
                                        <div class="card-body pb-0 pl-0 pr-0">
                                            <h6 class="font-weight-bolder text-center mb-3 text-primary">Entrées</h6>
                                        </div>
                                        <hr class="m-0">
                                        <div class="card-body">
                                            <small><strong>Note</strong> : Chacune des valeurs que vous saisirez sera utilisée pour remplir vos documents. Si vous ne saisissez rien, les valeurs par défaut de chaque document seront maintenues.</small>

                                            <!-- Informations de l'entête -->
                                            <fieldset>
                                                <legend>
                                                    <div class="divider text-start my-0">
                                                        <div class="divider-text"><strong>Entête</strong></div>
                                                    </div>
                                                </legend>

                                                <!-- Ligne 1 -->
                                                <div class="mb-2 row">
                                                    <label for="ligne1" class="col-sm-3 col-form-label">Ligne 1</label>
                                                    <div class="col-sm-9">
                                                        <input id="ligne1" type="text" name="ligne1" class="form-control<?= isset($erreurs['ligne1']) ? ' is-invalid' : '' ?>" value="<?= isset($erreurs) ? (isset($_POST['ligne1']) ? htmlspecialchars($_POST['ligne1']) : '') : '' ?>" placeholder="Entrez une valeur" <?= isset($erreurs['ligne1']) ? 'aria-describedby="ligne1Aide"' : '' ?>>

                                                        <?php if(isset($erreurs['ligne1'])) : ?>
                                                            <div id="ligne1Aide">
                                                                <small class="text-danger"><?= $erreurs['ligne1'] ?? '' ?></small>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <!-- Ligne 2 -->
                                                <div class="mb-2 row">
                                                    <label for="ligne2" class="col-sm-3 col-form-label">Ligne 2</label>
                                                    <div class="col-sm-9">
                                                        <input id="ligne2" type="text" name="ligne2" class="form-control" value="" placeholder="Entrez une valeur">
                                                        <!-- <small class="text-danger"><?= $errors['nom'] ?? '' ?></small> -->
                                                    </div>
                                                </div>

                                                <!-- Ligne 3 -->
                                                <div class="mb-2 row">
                                                    <label for="ligne3" class="col-sm-3 col-form-label">Ligne 3</label>
                                                    <div class="col-sm-9">
                                                        <input id="ligne3" type="text" name="ligne3" class="form-control" value="" placeholder="Entrez une valeur">
                                                        <!-- <small class="text-danger"><?= $errors['nom'] ?? '' ?></small> -->
                                                    </div>
                                                </div>

                                                <!-- Ville -->
                                                <div class="mb-2 row">
                                                    <label for="ville" class="col-sm-3 col-form-label">Ville</label>
                                                    <div class="col-sm-9">
                                                        <input id="ville" type="text" name="ville" class="form-control" value="" placeholder="Entrez une valeur">
                                                        <!-- <small class="text-danger"><?= $errors['nom'] ?? '' ?></small> -->
                                                    </div>
                                                </div>

                                                <!-- Date 1 -->
                                                <div class="mb-2 row">
                                                    <label for="date1" class="col-sm-3 col-form-label">Date 1</label>
                                                    <div class="col-sm-9">
                                                        <input id="date1" type="text" name="date1" class="form-control" value="" placeholder="Entrez une valeur">
                                                        <!-- <small class="text-danger"><?= $errors['nom'] ?? '' ?></small> -->
                                                    </div>
                                                </div>
                                            </fieldset>

                                            <!-- Autres informations -->
                                            <fieldset>
                                                <legend>
                                                    <div class="divider text-start my-0">
                                                        <div class="divider-text"><strong>Autres</strong></div>
                                                    </div>
                                                </legend>

                                                <!-- Date 2 -->
                                                <div class="mb-2 row">
                                                    <label for="date2" class="col-sm-3 col-form-label">Date 2</label>
                                                    <div class="col-sm-9">
                                                        <input id="date2" type="text" name="date2" class="form-control" value="" placeholder="Entrez une valeur">
                                                        <!-- <small class="text-danger"><?= $errors['nom'] ?? '' ?></small> -->
                                                    </div>
                                                </div>
                                            </fieldset>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">

                                    <div class="card">
                                        <div class="card-body pb-0 pl-0 pr-0">
                                            <h6 class="font-weight-bolder text-center mb-3 text-primary">Aperçu des modifications</h6>
                                        </div>
                                        <hr class="m-0">
                                        <div class="card-body">
                                            <div class="row mb-4" id="entete">
                                                <div class="col-6 text-center fs-6" id="blocGauche">
                                                    <p class="m-0">REPUBLIQUE DU BENIN</p>
                                                    <p class="m-0">*-*-*-*-*</p>
                                                    <p class="m-0">MINISTERE DE <span id="prev_ligne1">............<strong>(ligne 1)</strong></span></p>
                                                    <p class="m-0">*-*-*-*-*</p>
                                                    <p class="m-0">DIRECTION DES <span id="prev_ligne2">......<strong>(ligne 2)</strong></span></p>
                                                    <p class="m-0">*-*-*-*-*</p>
                                                    <p class="m-0">SERVICE <span id="prev_ligne3">.........<strong class="text-">(ligne 3)</strong></span></p>
                                                    <p class="m-0">*-*-*-*-*</p>
                                                </div>
                                                <div class="col-6 text-center" id="blocDroite">
                                                    <p><span id="prev_ville" class="text-capitalize"><strong>(Ville)</strong></span>, <span id="prev_date1"><strong>(Date 1)</strong></span></p>
                                                    <p class="text-uppercase font-weight-bold">Titre</p>
                                                    <p class="text-uppercase font-weight-bold">Sous-titre</p>
                                                </div>
                                            </div>

                                            <div class="mb-4" id="milieu">
                                                <table>
                                                    <tbody>
                                                        <tr>
                                                            <td><strong>N°</strong></td>
                                                            <td>&ensp; /DEG/MAS/SAFM/SDDC/SEL/SEMC/SIS/SD</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong class="text-decoration-underline">REF</strong></td>
                                                            <td> &ensp;: /DEG/MAS/SAFM/SDDC/SEL/SEMC/SIS/SD du <span id="prev_date2"><strong>(Date 2)</strong></span></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
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
                            </div>
                        </div>
                    </form>
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
        const champVille = document.getElementById('ville');
        const champDate1 = document.getElementById('date1');
        const champDate2 = document.getElementById('date2');

        const prevLigne1 = document.getElementById('prev_ligne1');
        const prevLigne2 = document.getElementById('prev_ligne2');
        const prevLigne3 = document.getElementById('prev_ligne3');
        const prevVille = document.getElementById('prev_ville');
        const prevDate1 = document.getElementById('prev_date1');
        const prevDate2 = document.getElementById('prev_date2');

        const champs = [champLigne1, champLigne2, champLigne3, champVille, champDate1, champDate2];
        const prevs = [prevLigne1, prevLigne2, prevLigne2, prevVille, prevDate1, prevDate2];
        const valeursDefaut = ['............<strong>(ligne 1)</strong>', '......<strong>(ligne 2)</strong>', '.........<strong>(ligne 3)</strong>', '<strong>(Ville)</strong>', '<strong>(Date 1)</strong>', '<strong>(Date 2)</strong>'];

        function updatePreview(input, prev) {
            prev.textContent = input.value;
        }

        for (let i = 0; i < champs.length; i++) {
            const input = champs[i];
            const prev = prevs[i];
            input.addEventListener('input', () => updatePreview(input, prev));
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
<?php

if (in_array('infos_generales', $elements_a_inclure)) {
?>
    <!-- Fieldset : Informations générales début -->
    <fieldset>
        <legend class="h6">
            <div class="divider text-start mt-0">
                <div class="divider-text"><strong>Informations générales</strong></div>
            </div>
        </legend>

        <?php foreach ($informations_generales as $champ => $infos) : ?>

            <?php
            $label = $infos[0];
            $placeholder = $infos[1];
            ?>

            <!-- Début  <?php echo $label; ?> -->
            <div class="mb-2 row">

                <label for="<?php echo $champ; ?>" class="col-sm-3 col-form-label"><?php echo $label; ?></label>

                <div class="col-sm-9">
                    <input
                        type="<?= $champ != "date_naissance" ? "text" : "date" ?>"
                        name="<?= $champ ?>"
                        <?php if ($champ != "date_naissance") : ?>
                        maxlength="100"
                        <?php endif; ?>
                        id="<?= $champ ?>"
                        class="
                    form-control <?= isset($erreurs[$champ]) ? "is-invalid" : "" ?>"
                        aria-describedby="<?= $champ ?>Aide"
                        placeholder="<?= $placeholder ?>"
                        <?php if (!isset($erreurs) && isset($infos_participant)) : ?>
                        value="<?= $infos_participant[$champ] ?>"
                        <?php endif; ?>
                        <?php if (isset($erreurs)) : ?>
                        value="<?= htmlspecialchars($_POST[$champ]) ?>"
                        <?php endif; ?>>

                    <?php if (isset($erreurs[$champ])) : ?>
                        <div id="<?= $champ ?>Aide" class="form-text">
                            <small class="text-danger"><?php echo $erreurs[$champ][0]; ?></small>
                        </div>
                    <?php endif; ?>

                    <!-- Explications additionnels -->
                    <?php if ($champ == 'reference_carte_identite') : ?>
                        <small><strong>Note : </strong> Il s'agit du <strong>N° du Doc</strong> ou du <strong>NPI</strong> (Numéro d'Identification Personnel)</small>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Fin  <?php echo $infos[0]; ?> -->

        <?php endforeach; ?>
    </fieldset>
    <!-- Fieldset : Informations générales fin -->
<?php
}

if (in_array('infos_bancaires', $elements_a_inclure)) {
?>
    <!-- Fieldset : Informations bancaires début -->
    <fieldset class="<?= isset($page_ajout_participant) ? 'mt-4' : '' ?>">
        <legend class="h6">
            <div class="divider text-start mt-0">
                <div class="divider-text"><strong>Informations bancaires</strong></div>
            </div>
        </legend>

        <?php if (isset($page_ajout_participant)) : ?>
            <?php
            // Nous sommes sur la page d'ajout de l'acteur. Je veux y afficher un message expliquant que l'utilisateur peut ne pas remplir les informations bancaires si l'acteur n'en dispose pas 
            ?>
            <!-- Note informative -->

            <small class="mb-4 d-block"><strong>Note : </strong> Si l'acteur que vous ajoutez ne dispose pas d'informations bancaires, vous pouvez ne pas remplir cette section.</small>
        <?php endif; ?>

        <?php
        $counter = 0; // Compteur 
        $premier_groupe = true;
        ?>

        <?php foreach ($informations_bancaires as $champ => $infos) : ?>

            <?php if ($counter == 0 && !$premier_groupe) : ?>
                <div class="mb-2 row justify-content-end">
                    <div class="col-sm-10">
                        <hr>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            $premier_groupe = false;
            $champ_type_fichier = str_contains($champ, 'pdf_rib');
            $counter++;
            $label = $infos[0];
            $placeholder = $infos[1];
            ?>

            <!-- Début  <?php echo $label; ?> -->
            <div class="mb-2 row">
                <label for="<?php echo $champ; ?>" class="col-sm-3 col-form-label"><?php echo $label; ?></label>
                <div class="col-sm-9">
                    <input
                        type="<?php echo ($champ_type_fichier) ? "file" : "text"; ?>"
                        name="<?php echo $champ; ?>"
                        id="<?php echo $champ; ?>"
                        class="
                    form-control 
                    <?php if (isset($erreurs[$champ])) : ?>
                    is-invalid
                        <?php endif; ?>"

                        aria-describedby="<?php echo $champ; ?>Aide"

                        <?php if (!$champ_type_fichier) : ?>
                        maxlength="100"
                        placeholder=" <?php echo $placeholder; ?>"
                        <?php endif; ?>
                        <?php if (!isset($erreurs) && isset($infos_participant) && !$champ_type_fichier) : ?>
                        value="<?php echo $infos_participant[$champ]; ?>"
                        <?php endif; ?>

                        <?php if (isset($erreurs) && !$champ_type_fichier) : ?>
                        value="<?php echo htmlspecialchars($_POST[$champ]); ?>"
                        <?php endif; ?>>

                    <?php if (isset($erreurs[$champ])) : ?>
                        <div id="<?php echo $champ; ?>Aide" class="form-text">
                            <small class="text-danger"><?php echo $erreurs[$champ][0]; ?></small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Fin  <?php echo $infos[0]; ?> -->

            <?php
            if ($counter == 3) {
                $counter = 0;
            }
            ?>
        <?php endforeach; ?>
    </fieldset>
    <!-- Fieldset : Informations bancaires fin -->
    <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $taille_admissible_fichiers_pdf; ?>">
<?php
}

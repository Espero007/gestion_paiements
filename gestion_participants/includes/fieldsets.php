<?php

// if(!isset($fieldsets_a_inclure)){
//     redirigerVersPageErreur(404, obtenirURLcourant());
// }

if (in_array('infos_generales', $elements_a_inclure)) {
?>
    <!-- Fieldset : Informations générales début -->
    <fieldset>
        <legend class="h6"><strong>Informations générales</strong></legend>
        <hr>

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
                        type="<?php echo ($champ != "date_naissance") ? "text" : "date"; ?>"

                        name="<?php echo $champ; ?>"
                        <?php if ($champ != "date_naissance") : ?>
                        maxlength="100"
                        <?php endif; ?>
                        id="<?php echo $champ; ?>"
                        class="
                    form-control 
                    <?php if (isset($erreurs[$champ])) : ?>
                    is-invalid
                    <?php endif; ?>"
                        aria-describedby="<?php echo $champ; ?>Aide"
                        placeholder="<?php echo $placeholder; ?>"
                        <?php if (!isset($erreurs) && isset($infos_participant)) : ?>
                        value="<?php echo $infos_participant[$champ]; ?>"
                        <?php endif; ?>
                        <?php if (isset($erreurs)) : ?>
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

        <?php endforeach; ?>
    </fieldset>
    <!-- Fieldset : Informations générales fin -->
<?php
}

if (in_array('infos_bancaires', $elements_a_inclure)) {
?>
    <!-- Fieldset : Informations bancaires début -->
    <fieldset class="
    <?php if (isset($page_ajout_participant)) {
        echo "mt-4";
    } ?>">
        <legend class="h6"><strong>Informations bancaires</strong></legend>
        <hr>

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

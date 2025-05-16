<!-- Fieldset : Informations bancaires début -->
<fieldset>
    <legend>
        <h5>Informations bancaires</h5>
    </legend>

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
                        <?php echo $erreurs[$champ][0]; ?>
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
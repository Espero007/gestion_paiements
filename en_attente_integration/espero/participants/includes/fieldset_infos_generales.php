<fieldset>
    <legend>
        <h5>Informations générales</h5>
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
                        <?php echo $erreurs[$champ][0]; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Fin  <?php echo $infos[0]; ?> -->

    <?php endforeach; ?>
</fieldset>
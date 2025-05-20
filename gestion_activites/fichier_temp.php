<?php
if (isset($recuperation_type_activite) && !$recuperation_type_activite) {

?>

<?php
} elseif (isset($recuperation_type_activite) && $recuperation_type_activite) {
    // Le type de l'activité a été récupéré et est valide
?>
    <p class="mt-2">Dîtes-nous en plus sur votre activité à présent.</p>
    <div class="row">
        <div class="col-12">
            <!-- Basic Card Example -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Formulaire d'ajout</h6>
                </div>
                <div class="card-body">

                    <!-- Formulaire -->

                    <form action="" method="post">

                        <fieldset>
                            <legend class="h6">Informations générales</legend>
                            <hr>
                            <div class="mb-2 row">
                                <label for="nom" class="col-sm-3 col-form-label">Nom</label>
                                <div class="col-sm-9">
                                    <input
                                        type="text"
                                        name="nom"
                                        maxlength="50"
                                        id="nom"
                                        class="form-control
                                                <?php if (isset($erreurs["nom"])) {
                                                    echo "is-invalid\" aria-describedby=\"nomAide";
                                                } ?>"
                                        placeholder="Entrez le nom de l'activité"
                                        <?php if (isset($erreurs)) {
                                            echo "value = \"" . $_POST["nom"] . "\"";
                                        } ?>>

                                    <?php if (isset($erreurs["nom"])) {
                                    ?>
                                        <div id="nomAide" class="form-text"><?php echo $erreurs["nom"][0] ?></div>
                                    <?php } ?>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary mt-5 mb-4" name="creer">Créer l'activité</button>
                        </fieldset>



                    </form>

                </div>
            </div>
        </div>
    </div>
<?php
}
?>
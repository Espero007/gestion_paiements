<ul class="dropdown-menu">
    <li>
        <a href="#" class="text-decoration-none">
            <?php if (isset($documents_choisis)) : ?>
                <form action="" method="post">
                    <?php foreach ($documents_choisis as $document) : ?>
                        <input type="hidden" name="documents[]" value="<?= $document ?>">
                    <?php endforeach; ?>
                    <button type="submit" name="generer_zip_selection" title="Générez et téléchargez un fichier compressé (zip) contenant tous les documents sélectionnés." class="dropdown-item custom-dropdown-item">Compresser et télécharger les documents sélectionnés</button>
                    <button type="submit" name="generer_zip_tous" title="Générez et téléchargez un fichier compressé (zip) contenant tous les documents à générer." class="dropdown-item custom-dropdown-item">Générer le fichier compressé contenant les 06 types de documents</button>
                </form>
            <?php else: ?>
                <button type="submit" name="generer_zip_tous" title="Générez et téléchargez un fichier compressé (zip) contenant tous les documents à générer." class="dropdown-item custom-dropdown-item" style="max-width : 50%">Générer le fichier compressé contenant les 06 types de documents</button>
            <?php endif; ?>
        </a>
    </li>

    <!-- <li>
        <a href="/gestion_activites/scripts_generation/document_fusionne.php?id=<?= chiffrer($id_activite) ?>" class="dropdown-item custom-dropdown-item" title="Générez et téléchargez un seul fichier contenant tous les documents sélectionnés." target="_blank">Générer un seul fichier</a>
    </li> -->
</ul>
<ul class="dropdown-menu">
    <li>
        <a href="#" class="text-decoration-none">
            <button type="submit" name="generer_zip" title="Générez et téléchargez un fichier compressé (zip) contenant tous les documents sélectionnés." class="dropdown-item custom-dropdown-item">Générer un fichier zip</button>
        </a>
    </li>

    <li>
        <a href="/gestion_activites/scripts_generation/document_fusionne.php?id=<?= chiffrer($id_activite) ?>" class="dropdown-item custom-dropdown-item" title="Générez et téléchargez un seul fichier contenant tous les documents sélectionnés." target="_blank">Générer un seul fichier</a>
    </li>
</ul>
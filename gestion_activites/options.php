<?php

/** Options disponibles
 * 1- Modifier les informations
 * 2- Associer des acteurs
 * 3- Editer l'entête des documents
 * 4- Générer les documents
 * 5- Supprimer activité
 */

?>

<ul class="dropdown-menu">
    <?php if ($titre_page == 'Liste des activités') : ?>
        <li>
            <a href="modifier_infos.php?id=<?= chiffrer($activite['id']) ?>" class="dropdown-item custom-dropdown-item">Modifier les informations</a>
        </li>
    <?php endif; ?>

    <li>
        <a href="/gestion_participants/liaison.php?id=<?= chiffrer($activite['id']) ?>&s=1" class="dropdown-item custom-dropdown-item"></i>Associer des acteurs</a>
    </li>

    <?php if ($titre_page == 'Liste des activités') : ?>
        <?php if ($generer_documents) : ?>
            <li>
                <a href="/gestion_activites/edition_en_ligne.php?id=<?= chiffrer($activite['id']) ?>" class="dropdown-item fs-6 custom-dropdown-item">Editer l'entête des documents</a>
            </li>
            <li>
                <a href="/gestion_activites/generation_documents.php?id=<?= chiffrer($activite['id']) ?>" class="dropdown-item custom-dropdown-item"></i>Générer les documents</a>
            </li>
        <?php endif; ?>
    <?php elseif ($titre_page == 'Gestion de l\'activité'): ?>
        <?php if (count($participants_associes) != 0) : ?>
            <li>
                <a href="/gestion_activites/edition_en_ligne.php?id=<?= chiffrer($activite['id']) ?>" class="dropdown-item fs-6 custom-dropdown-item">Editer l'entête des documents</a>

            </li>
            <li>
                <a href="/gestion_activites/generation_documents.php?id=<?= chiffrer($activite['id']) ?>" class="dropdown-item custom-dropdown-item"></i>Générer les documents</a>
            </li>
        <?php endif; ?>
    <?php endif; ?>

    <li>
        <hr class="dropdown-divider my-1">
    </li>
    <li>
        <a href="#" class="dropdown-item text-danger custom-dropdown-item del-btn" data-toggle="modal" data-target="#deletionModal" id="<?= chiffrer($activite['id']) ?>">Supprimer</a>
    </li>
</ul>
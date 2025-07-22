 <?php
    // Les options disponibles

    /**
     * 1- Modifier les informations
     * 2- Associer à une activité
     * 3- Ajouter des comptes bancaires
     * 4- Supprimer un compte bancaire
     * 5- Supprimer le participant
     */

    ?>

 <ul class="dropdown-menu">

     <?php if ($titre_page == 'Liste des acteurs') : ?>
         <li>
             <a href="/gestion_participants/modifier_informations.php?id=<?= chiffrer($participant['id_participant']) ?>" class="dropdown-item custom-dropdown-item">Modifier les informations</a>
         </li>

     <?php endif; ?>

     <li>
         <a href="/gestion_participants/liaison.php?id=<?= chiffrer($participant['id_participant']) ?>&s=0" class="dropdown-item custom-dropdown-item"></i>Associer à une activité</a>
     </li>
     <?php if (!quotaComptesAtteint($participant['id_participant'])) : ?>
         <li><a href="ajouter_comptes.php?id=<?= chiffrer($participant['id_participant']) ?>" class="dropdown-item custom-dropdown-item">Ajouter un compte bancaire</a></li>
     <?php endif; ?>
     <?php if ($titre_page == 'Liste des acteurs') : ?>
         <?php if ($participant['banque_count'] > 1): ?>
             <li>
                 <a href="/gestion_participants/supprimer_une_banque.php?id=<?= chiffrer($participant['id_participant']) ?>" class="dropdown-item custom-dropdown-item">Supprimer un compte bancaire</a>
             </li>
         <?php endif; ?>
     <?php elseif ($titre_page == 'Gestion de l\'acteur'): ?>
         <?php if (count($comptes) > 1): ?>
             <li>
                 <a href="supprimer_une_banque.php?id=<?= chiffrer($participant['id_participant']) ?>" class="dropdown-item custom-dropdown-item">Supprimer un compte bancaire</a>
             </li>
         <?php endif; ?>
     <?php endif; ?>

     <li>
         <hr class="dropdown-divider">
     </li>
     <li>
         <a href="#" class="dropdown-item text-danger custom-dropdown-item del-btn" id='<?= chiffrer($participant['id_participant']) ?>' data-toggle="modal" data-target="#deletionModal">Supprimer</a>
     </li>
 </ul>
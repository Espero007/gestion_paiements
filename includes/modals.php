<!-- Suppression Modal -->

<?php
$label = $section == 'Participants' ? 'ce participant' : ($section == 'Activités' ? 'cette activité' : '');
$lien_suppression = $section == 'Participants' ? '/gestion_participants/supprimer_participant.php?id=' : ($section == 'Activités' ? '/gestion_activites/supprimer_activite.php?id=' : '');

?>

<div class="modal fade" id="deletionModal" tabindex="-1" role="dialog" aria-labelledby="deletionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Êtes-vous sûr(e) de vouloir supprimer <?= $label ?> ?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">En appuyant sur "Supprimer" vous allez supprimer toutes les informations associées à <?= $label ?> sans aucune possibilité de faire marche arrière.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Annuler</button>
                <a class="btn btn-danger" href="#" id='deletionModalBtn'>Supprimer</a>
            </div>
        </div>
    </div>
</div>

<?php // Modal de rupture de liaison 
?>

<div class="modal fade" id="ruptureLiaison" tabindex="-1" role="dialog" aria-labelledby="ruptureLiaisonModal"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Êtes-vous sûr(e) de rompre cette liaison ?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">En appuyant sur "Rompre" vous allez rompre définitivement la liaison entre <?= $titre_page == 'Gestion de l\'acteur' ? 'cet acteur et cette activité' : ($titre_page == 'Gestion de l\'activité' ? 'cette activité et cet acteur' : '')  ?></div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Annuler</button>
                <a class="btn btn-danger" href="#" id='ruptureLiaisonBtn'>Rompre</a>
            </div>
        </div>
    </div>
</div>

<script>
    const deleteBtns = document.querySelectorAll('.del-btn'); // boutons de suppression des participants
    const deletionModalBtn = document.getElementById('deletionModalBtn'); // Bouton de suppression
    const ruptureLiaisonBtn = document.getElementById('ruptureLiaisonBtn'); // Bouton de rupture de la liaison
    const modals = document.querySelectorAll('.modal');

    deleteBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.id;

            modals.forEach(modal => {
                if (modal.id == 'deletionModal') {
                    deletionModalBtn.href = '<?= $lien_suppression ?>' + id;
                } else if (modal.id == 'ruptureLiaison') {
                    ruptureLiaisonBtn.href = '/gestion_participants/rompre_liaison.php?id=' + id;
                }
            })
        })
    })
</script>
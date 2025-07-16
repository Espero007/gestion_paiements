<!-- Suppression Modal -->

<?php
$label = $section == 'Participants' ? 'ce participant' : ($section == 'Activités' ? 'cette activité' : '');
$lien_suppression = $section == 'Participants' ? '/gestion_participants/supprimer_participant.php?id=' : ($section == 'Activités' ? '/gestion_activites/supprimer_activite.php?id=' : '')
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

<script>
    const deleteBtns = document.querySelectorAll('.del-btn'); // boutons de suppression des participants
    const deletionModalBtn = document.getElementById('deletionModalBtn');

    deleteBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const id = btn.id;
            deletionModalBtn.href = '<?= $lien_suppression ?>' + id;
        })
    })
</script>
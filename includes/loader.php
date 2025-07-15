<?php flush() ?>
<!-- Loader -->
<div id="loader">
    <div class="spinner"></div>
    <p class="mt-2">Chargement...</p>
</div>

<!-- Script JS associé -->

<script>
    document.getElementById('wrapper').classList.add('hide');

    window.addEventListener('load', function() {
        // Quand tout est chargé
        document.getElementById('loader').classList.add('hide');
        setTimeout(() => {
            document.getElementById('wrapper').classList.remove('hide');
            document.getElementById('wrapper').classList.add('show');
            document.getElementById('loader').style.display = "none";
            setTimeout(() => {
                document.getElementById('wrapper').classList.remove('show');
            }, 1000);
        }, 200);
    })
</script>
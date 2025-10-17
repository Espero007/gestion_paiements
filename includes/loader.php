<!-- Script JS associé -->

<script>
    const element = document.getElementById('wrapper');
    if (element) element.classList.add('hide');

    window.addEventListener('load', function() {
        // Quand tout est chargé
        if (element) {
            document.getElementById('loader').classList.add('hide');
            setTimeout(() => {

                element.classList.remove('hide');
                element.classList.add('show');
                document.getElementById('loader').style.display = "none";
                setTimeout(() => {
                    element.classList.remove('show');
                }, 1000);
            }, 200);
        }
    })
</script>
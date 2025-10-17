document.addEventListener('DOMContentLoaded', function() {
const typeActivite = document.getElementById('type_activite')?.value;
const container = document.getElementById('titres-container');
const addBtn = document.getElementById('add-titre');
const hiddenTitres = document.getElementById('titres_associes');
const hiddenIndems = document.getElementById('indemnite_forfaitaire');

function getValues() {
    const titres = Array.from(container.querySelectorAll('.titre-input')).map(i => i.value.trim());
    const indems = Array.from(container.querySelectorAll('.indem-input')).map(i => i.value.trim());
    return {
        titres,
        indems
    };
}

function syncHidden() {
    const {
        titres,
        indems
    } = getValues();
    hiddenTitres.value = titres.filter(t => t !== '').join(',');
    if (hiddenIndems) hiddenIndems.value = indems.join(',');
}


// Ajouter dynamiquement un titre
addBtn.addEventListener('click', () => {
    const div = document.createElement('div');
    div.className = 'titre-item mb-2 d-flex gap-2 align-items-center appear';
    div.innerHTML = `
    <input type="text" name="titres[]" class="form-control titre-input" placeholder="Titre">
    ${(typeActivite === '2' || typeActivite === '3') 
        ? ' <div class = "input-group" ><input type="number" step="0.01" name="indemnites[]" class="form-control indem-input" placeholder="Indemnité"><span class="input-group-text">FCFA</span> </div>' 
        : ''}
    <button type="button" class="btn btn-outline-danger remove-titre">Supprimer</button>
`;

    // On ajoute l'élément
    container.appendChild(div);

    // On retire la classe d'animation
    setTimeout(() => {
        document.querySelectorAll('div').forEach(div => {
            if (div.classList.contains('appear')) div.classList.remove('appear');
        });
    }, 400);
});

// Supprimer un titre
container.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-titre')) {
        const element = e.target.closest('.titre-item');
        element.classList.add('desappear');

        setTimeout(() => {
            element.remove();
        }, 400);

        syncHidden();
    }
});

// Synchroniser à chaque saisie
container.addEventListener('input', syncHidden);

// Vérification avant envoi
const form = document.querySelector('#activityForm');
form.addEventListener('submit', function(e) {
    syncHidden();
    const {
        titres,
        indems
    } = getValues();
    const hasTitre = titres.some(t => t !== '');
    const hasIndem = (typeActivite === '2' || typeActivite === '3') ?
        indems.some(i => i !== '') :
        true;

        /*
    if (!hasTitre || !hasIndem) {
        e.preventDefault();
        alert('Veuillez saisir au moins un titre (et une indemnité si nécessaire).');
    } */
});

//  Initial sync
syncHidden();
});

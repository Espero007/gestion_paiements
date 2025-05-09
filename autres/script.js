
const btnAjout = document.getElementById('ajout_numero')

function incrementerHiddenInput()
{
    const hiddenInput = document.getElementById('hiddenInput');
    let valeurHiddenInput = parseInt(hiddenInput.getAttribute('value'))
    valeurHiddenInput++
    hiddenInput.setAttribute('value', valeurHiddenInput.toString());
}

function obtenirValeurHiddenInput()
{
    const hiddenInput = document.getElementById('hiddenInput');
    const valeurHiddenInput = parseInt(hiddenInput.getAttribute('value'));
    return valeurHiddenInput;
}

function creerElementDemarquation(){
    const div_parent = document.createElement('div');
    div_parent.setAttribute('class', 'mb-2 row justify-content-end');

    const div_enfant = document.createElement('div');
    div_enfant.setAttribute('class', 'col-sm-10');

    const hr = document.createElement('hr');
    div_enfant.appendChild(hr);
    div_parent.appendChild(div_enfant);

    return div_parent;
}

function ajouterNumeroBancaire() {

    incrementerHiddenInput();

    // 1- Créer une div de class "groupe 1", l'insérer après le hr de délimitation et y insérer les informations relatives au groupe 1

    if (document.querySelector('#groupe_1') === null) {
        // Nous sommes à l'instant initial et il faut créer le premier groupe

        // 1.1 - Créer une div
        const wrapper_1 = document.createElement('div')
        wrapper_1.setAttribute('id', 'groupe_1')

        // 1.2 - Insérer le wrapper après le hr
        const hr = document.querySelector('#informations_bancaires hr')
        hr.after(wrapper_1)

        const div_s = document.querySelectorAll('#informations_bancaires div.form-group');

        div_s.forEach(div => {
            // 1.3 - Déplacer les éléments dans le wrapper

            wrapper_1.insertBefore(div, null);

            //2 - Actualiser les attributs "name" des éléments du groupe 1

            const label = div.querySelector('label');
            const input = div.querySelector('input');

            // Récupération des valeurs
            const labelForValue = label.getAttribute('for')
            const idValue = input.getAttribute('id')
            const nameValue = input.getAttribute('name');

            //Mise à jour des valeurs
            label.setAttribute('for', labelForValue+"_1")
            input.setAttribute('id', idValue+"_1")
            input.setAttribute('name', nameValue + "_1");
        });
    }
    
    // 4- Dupliquer le groupe 1
    const groupe_1 = document.getElementById('groupe_1');
    const nvGroupe = groupe_1.cloneNode(true);
    
    //console.log(nvGroupe);

    // 4- Effectuer les modifications pour différencier le nouveau groupe créé
    const valeurHiddenInput = obtenirValeurHiddenInput();

    // 4.1 - Modifier l'id de la div englobante
    nvGroupe.setAttribute('id', 'groupe_'+valeurHiddenInput.toString());

    const div_s = nvGroupe.querySelectorAll('.form-group')

    // 4-2 Modifier les autres valeurs intermédiaires
    div_s.forEach(div=>{
        const label = div.querySelector('label');
        const input = div.querySelector('input');

        // Récupération des valeurs
        let labelForValue = label.getAttribute('for');
        let inputIdValue = input.getAttribute('id');
        let inputNameValue = input.getAttribute('name');

        // Retraits des numéros de fin
        labelForValue = labelForValue.slice(0,-1);
        inputIdValue = inputIdValue.slice(0, -1);
        inputNameValue = inputNameValue.slice(0, -1);

        // Concaténation des nouveaux numéros
        label.setAttribute('for', labelForValue+valeurHiddenInput.toString());
        input.setAttribute('id', inputIdValue + valeurHiddenInput.toString());
        input.setAttribute('name', inputNameValue+valeurHiddenInput.toString());
    })

    // 5- Insérer le nouveau groupe après le dernier groupe présent
    
    const groupe_precedent = document.getElementById(`groupe_${valeurHiddenInput-1}`);
    const elementDemarquation = creerElementDemarquation();

    groupe_precedent.after(nvGroupe);
    nvGroupe.before(elementDemarquation);

    console.log(groupe_precedent);
    
    // const div_bouton_ajout = document.getElementById('div_bouton_ajout');

    // div_bouton_ajout.before(nvGroupe);
    
}


btnAjout.addEventListener("click", (event) => {
    event.preventDefault()
    ajouterNumeroBancaire()
})
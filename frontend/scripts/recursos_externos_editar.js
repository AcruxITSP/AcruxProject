const domInputSelectEspacio = document.getElementById("select-espacio");

/* Array con datos de espacios. Ejemplo */
const jsonStringEspacios = '[{"id_espacio": "1", "tipo": "Salón", "numero": "1"}, {"id_espacio": "2", "tipo": "Aula", "numero": "2"}, {"id_espacio": "3", "tipo": "Taller", "numero": ""}]';
const espacios = JSON.parse(jsonStringEspacios);

const urlParams = new URLSearchParams(window.location.search); //trae los parametros de la url
const id = urlParams.get("id"); // agarra el id de la url

/* Funciones */

function listaEspaciosOptions(espacios){
    domInputSelectEspacio.innerHTML = '';
    espacios.push({id_espacio: 0, numero: null, tipo: "Independiente del Espacio"});
    espacios.forEach(espacio => {
        const option = document.createElement("option");

        option.value = `${espacio.id_espacio}`;
        option.innerText = `${espacio.tipo} ${espacio.numero ?? ''}`;

        domInputSelectEspacio.appendChild(option);
    });
}

async function inicializar()
{
    let respuesta = await fetch(`../../../backend/recursos/crear.php`, {method:"GET"});
    respuesta = await respuesta.json();
    listaEspaciosOptions(respuesta.value.espacios);

    respuesta = await fetch(`../../../backend/recursos/editar_externo.php?id_recurso_base=${id}`, {method:"GET"});
    respuesta = await respuesta.json();

    listaEspaciosOptions(espacios); 
}

window.addEventListener('pageshow', async e => {
    inicializar();
});
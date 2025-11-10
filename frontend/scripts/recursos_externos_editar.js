const domInputSelectEspacio = document.getElementById("select-espacio");

/* Array con datos de espacios. Ejemplo */
const jsonStringEspacios = '[{"id_espacio": "1", "tipo": "Salón", "numero": "1"}, {"id_espacio": "2", "tipo": "Aula", "numero": "2"}, {"id_espacio": "3", "tipo": "Taller", "numero": ""}]';
const espacios = JSON.parse(jsonStringEspacios);

/* Funciones */

function listaEspaciosOptions(espacios){
    espacios.forEach(espacio => {
        const option = document.createElement("option");

        option.value = `${espacio.id_espacio}`;
        option.innerText = `${espacio.tipo} ${espacio.numero}`;

        domInputSelectEspacio.appendChild(option);
    });
}

listaEspaciosOptions(espacios); 
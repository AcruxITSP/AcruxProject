const domLabelOpcionesMaterias = document.getElementById("label-opcionesMaterias");
const domDivOpcionesMaterias = document.getElementById("opcionesMaterias");

/* Array de materias de ejemplo */
const jsonStringMaterias = '[{"id_materia": "1", "nombre": "Programacion"}, {"id_materia": "2", "nombre": "Ciberseguridad" }, {"id_materia": "3", "nombre": "Biologia"}, {"id_materia": "4", "nombre": "Fisica"}, {"id_materia": "5", "nombre": "Logica"}, {"id_materia": "6", "nombre": "utulab"}, {"id_materia": "7", "nombre": "Sistemas Operativos"}, {"id_materia": "8", "nombre": "Filosofia"}, {"id_materia": "9", "nombre": "Sociologia"}]';
const materias = JSON.parse(jsonStringMaterias);

/*Funciones */

domLabelOpcionesMaterias.addEventListener("click", () => {
    domDivOpcionesMaterias.classList.toggle("show");
});


function listaMateriasOptions(materias) {
    materias.forEach(materia => {
        const label = document.createElement("label");
        const input = document.createElement("input");

        const nodeMateria = document.createTextNode(`${materia.nombre}`);

        input.value = `${materia.id_materia}`;
        input.type = "checkbox";
        input.name = "id_materia[]"

        label.appendChild(input);
        label.appendChild(nodeMateria);

        domDivOpcionesMaterias.appendChild(label);
    });
}

listaMateriasOptions(materias);
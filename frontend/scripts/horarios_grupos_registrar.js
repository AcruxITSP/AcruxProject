const domInputSelectMateria= document.getElementById("select-materia");
const domInputSelectProfesor = document.getElementById("select-profesor");
const domInputSelectEspacio= document.getElementById("select-espacio");

/* Array de materias de ejemplo */
const jsonStringMaterias = '[{"id_materia": "1", "nombre": "Programacion"}, {"id_materia": "2", "nombre": "Ciberseguridad" }, {"id_materia": "3", "nombre": "Biologia"}, {"id_materia": "4", "nombre": "Fisica"}, {"id_materia": "5", "nombre": "Logica"}, {"id_materia": "6", "nombre": "utulab"}, {"id_materia": "7", "nombre": "Sistemas Operativos"}, {"id_materia": "8", "nombre": "Filosofia"}, {"id_materia": "9", "nombre": "Sociologia"}]';
const materias = JSON.parse(jsonStringMaterias);

/* Array con datos de profesores. Ejemplo */
const jsonStringProfesores = '[{"id_profesor": "1", "nombre": "Juan", "apellido": "Carlos"}, {"id_profesor": "2", "nombre": "Pancho", "apellido": "Gomez"}, {"id_profesor": "3", "nombre": "Enrico", "apellido": "Pucci"}]';
const profesores = JSON.parse(jsonStringProfesores);

/* Array con datos de espacios. Ejemplo */
const jsonStringEspacios = '[{"id_espacio": "1", "tipo": "Aula", "numero": "2"}, {"id_espacio": "2", "tipo": "Salón", "numero": "1"}, {"id_espacio": "3", "tipo": "Taller", "numero": ""}]';
const espacios = JSON.parse(jsonStringEspacios);

/* Funciones */

function listaMateriasOptions(materias){
    materias.forEach(materia => {
        const option = document.createElement("option");

        option.value = `${materia.id_materia}`;
        option.innerText = `${materia.nombre}`;

        domInputSelectMateria.appendChild(option);
    });
}

function listaProfesoresOptions(profesores){
    profesores.forEach(profesor => {
        const option = document.createElement("option");

        option.value = `${profesor.id_profesor}`;
        option.innerText = `${profesor.nombre} ${profesor.apellido}`;

        domInputSelectProfesor.appendChild(option);

        console.log("andubo creo");
        console.log("option");
    });
}

function listaEspaciosOptions(espacios){
    espacios.forEach(espacio => {
        const option = document.createElement("option");

        option.value = `${espacio.id_espacio}`;
        option.innerText = `${espacio.nombre}`;

        domInputSelectEspacio.appendChild(option);
    });
}

listaMateriasOptions(materias);
listaProfesoresOptions(profesores);
listaEspaciosOptions(espacios);

console.log("WAZAAAAA");
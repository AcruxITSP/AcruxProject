const domInputSelectProfesor = document.getElementById("select-profesores");
const domInputSelectCurso = document.getElementById("select-cursos");

/* Array con datos de profesores. Ejemplo */
const jsonStringProfesores = '[{"id_profesor": "1", "nombre": "Juan", "apellido": "Carlos"}, {"id_profesor": "2", "nombre": "Pancho", "apellido": "Gomez"}, {"id_profesor": "3", "nombre": "Enrico", "apellido": "Pucci"}]';
const profesores = JSON.parse(jsonStringProfesores);

/* Array con cursos. Ejemplo */
const jsonStringCursos = '[{"id_curso": "1", "nombre": "Informatica"}, {"id_curso": "2", "nombre": "Informatica Bilingue"}, {"id_curso": "3", "nombre": "Gastrobomia"}]';
const cursos = JSON.parse(jsonStringCursos);

/* Funciones */

function IListProfesoresOptions(profesores){
    profesores.forEach(profesor => {
        const option = document.createElement("option");

        option.value = `${profesor.id_profesor}`;
        option.innerText = `${profesor.nombre} ${profesor.apellido}`;

        domInputSelectProfesor.appendChild(option);
    });
}

function IListCursosOptions(cursos){
    cursos.forEach(curso => {
        const option = document.createElement("option");

        option.value = `${curso.id_curso}`;
        option.innerText = `${curso.nombre}`;

        domInputSelectCurso.appendChild(option);
    });
}

/* Llamar a las funciones */

IListProfesoresOptions(profesores);
IListCursosOptions(cursos);

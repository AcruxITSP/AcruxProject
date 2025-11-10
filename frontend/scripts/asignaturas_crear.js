const domLabelOpcionesProfesores = document.getElementById("label-opcionesProfesores");
const domDivOpcionesProfesores = document.getElementById("opcionesProfesores");

const domLabelOpcionesCursos = document.getElementById("label-opcionesCursos");
const domDivOpcionesCursos = document.getElementById("opcionesCursos");

/* Array con datos de profesores. Ejemplo */
const jsonStringProfesores = '[{"id_profesor": "1", "nombre": "Juan", "apellido": "Carlos"}, {"id_profesor": "2", "nombre": "Pancho", "apellido": "Gomez"}, {"id_profesor": "3", "nombre": "Enrico", "apellido": "Pucci"}]';
const profesores = JSON.parse(jsonStringProfesores);

/* Array con cursos. Ejemplo */
const jsonStringCursos = '[{"id_curso": "1", "nombre": "Informatica"}, {"id_curso": "2", "nombre": "Informatica Bilingue"}, {"id_curso": "3", "nombre": "Gastrobomia"}]';
const cursos = JSON.parse(jsonStringCursos);

/* Funciones */

domLabelOpcionesCursos.addEventListener("click", () => {
    domDivOpcionesCursos.classList.toggle("show");
    domDivOpcionesProfesores.classList.remove("show");
});

domLabelOpcionesProfesores.addEventListener("click", () => {
    domDivOpcionesProfesores.classList.toggle("show");
    domDivOpcionesCursos.classList.remove("show");
});

function listaProfesoresOptions(profesores){
    profesores.forEach(profesor => {
        const label = document.createElement("label");
        const input = document.createElement("input");

        const nodeProfesor = document.createTextNode(`${profesor.nombre} ${profesor.apellido}`);

        input.value = `${profesor.id_profesor}`;
        input.type = "checkbox";
        input.name = "id_profesor[]"

        label.appendChild(input);
        label.appendChild(nodeProfesor);

        domDivOpcionesProfesores.appendChild(label);
    });
}

function listaCursosOptions(cursos) {
    cursos.forEach(curso => {
        const label = document.createElement("label");
        const input = document.createElement("input");

        const nodeCurso = document.createTextNode(`${curso.nombre}`);

        input.value = `${curso.id_curso}`;
        input.type = "checkbox";
        input.name = "id_curso[]"

        label.appendChild(input);
        label.appendChild(nodeCurso);

        domDivOpcionesCursos.appendChild(label);
    });
}

/* Llamar a las funciones */

listaProfesoresOptions(profesores);
listaCursosOptions(cursos);

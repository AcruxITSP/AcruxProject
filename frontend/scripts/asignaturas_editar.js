const domLabelOpcionesProfesores = document.getElementById("label-opcionesProfesores");
const domDivOpcionesProfesores = document.getElementById("opcionesProfesores");

const domLabelOpcionesCursos = document.getElementById("label-opcionesCursos");
const domDivOpcionesCursos = document.getElementById("opcionesCursos");

const form = document.getElementById("form-editar-asignatura");

/* Array con datos de profesores. Ejemplo */
const jsonStringProfesores = '[{"id_profesor": "1", "nombre": "Juan", "apellido": "Carlos"}, {"id_profesor": "2", "nombre": "Pancho", "apellido": "Gomez"}, {"id_profesor": "3", "nombre": "Fabian", "apellido": "Sosa"}]';
const profesores = JSON.parse(jsonStringProfesores);

/* Array con cursos. Ejemplo */
const jsonStringCursos = '[{"id_curso": "1", "nombre": "Informatica"}, {"id_curso": "2", "nombre": "Informatica Bilingüe"}, {"id_curso": "3", "nombre": "Diseño Gráfico"}]';
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

function listaProfesoresOptions(profesores) {
    profesores.forEach(profesor => {
        const label = document.createElement("label");
        const input = document.createElement("input");

        const nodeProfesor = document.createTextNode(`${profesor.nombre} ${profesor.apellido}`);

        input.value = `${profesor.id_profesor}`;
        input.type = "checkbox";
        input.name = "id_profesores[]"

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
        input.name = "id_cursos[]"

        label.appendChild(input);
        label.appendChild(nodeCurso);

        domDivOpcionesCursos.appendChild(label);
    });
}

/* Llamar a las funciones para los campos del formulario */

listaProfesoresOptions(profesores);
listaCursosOptions(cursos);

/* Envio del formulario */

form.addEventListener("submit", async e => {
    e.preventDefault();
    const formData = new FormData(form);

    const urlParams = new URLSearchParams(window.location.search); //trae los parametros de la url
    const id = urlParams.get("id"); // agarra el id de la url

    formData.append("id_materia", id);

    let respuesta = await fetch(`../../../backend/asignaturas/editar.php`, { method: "POST", body: formData });
    respuesta = await respuesta.json();

    if (respuesta.ok) {
        await Swal.fire({
            title: "Espacio Creado",
            text: `El espacio ha sido creada exitosamente`,
            icon: "success"
        });
    }
    else {
        switch (respuesta.value) {
            case "NECESITA_LOGIN":
                Swal.fire({
                    title: "Login Requerido",
                    text: `Necesitas iniciar sesion para crear un espacio.`,
                    icon: "error"
                });
                break;
            default:
                Swal.fire({
                    title: "Error Desconocido",
                    text: `Un error desconocido ha ocurrido`,
                    icon: "error"
                });
        }
    }
});
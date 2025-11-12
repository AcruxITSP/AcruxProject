const domLabelOpcionesProfesores = document.getElementById("label-opcionesProfesores");
const domDivOpcionesProfesores = document.getElementById("opcionesProfesores");

const domLabelOpcionesCursos = document.getElementById("label-opcionesCursos");
const domDivOpcionesCursos = document.getElementById("opcionesCursos");

const form = document.getElementById("form-crear-asignatura");

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

/* Envio del formulario */

form.addEventListener("submit", async e => {
    e.preventDefault();
    const formData = new FormData(form);

    let respuesta = await fetch(`../../../backend/asignaturas/crear.php`, { method: "POST", body: formData });
    respuesta = await respuesta.json();

    if (respuesta.ok) {
        await Swal.fire({
            title: "Asignatura Creada",
            text: `La asignatura ha sido creada exitosamente`,
            icon: "success"
        });
    }
    else {
        switch (respuesta.value) {
            case "NECESITA_LOGIN":
                Swal.fire({
                    title: "Login Requerido",
                    text: `Necesitas iniciar sesion para crear una asignatura.`,
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

async function inicializar()
{
    let respuestaProfesores = await fetch(`../../../backend/usuarios/profesores.php`, {method:"GET"});
    respuestaProfesores = await respuestaProfesores.json();

    const profesores = respuestaProfesores.value;
    listaProfesoresOptions(profesores);

    let respuestaCursos = await fetch(`../../../backend/cursos/ver.php`, {method:"GET"});
    respuestaCursos = await respuestaCursos.json();

    const cursos = respuestaCursos.value;
    listaCursosOptions(cursos);
}

inicializar();
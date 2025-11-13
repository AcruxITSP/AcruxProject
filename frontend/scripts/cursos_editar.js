const domLabelOpcionesMaterias = document.getElementById("label-opcionesMaterias");
const domDivOpcionesMaterias = document.getElementById("opcionesMaterias");
const domNombre = document.getElementById("nombre");
const form = document.getElementById("form-editar-curso");

const urlParams = new URLSearchParams(window.location.search); //trae los parametros de la url
const id = urlParams.get("id"); // agarra el id de la url

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
        input.setAttribute('registerName', `${materia.nombre}`);
        input.type = "checkbox";
        input.name = "id_materias[]"

        label.appendChild(input);
        label.appendChild(nodeMateria);

        domDivOpcionesMaterias.appendChild(label);
    });
}

function mostrarValoresActuales(curso) {
    domNombre.value = curso.nombre;

    domLabelOpcionesMaterias.innerHTML = "";

    curso.materias.forEach(materia => {
        const inputMateria = document.querySelector(`input[registername="${materia.nombre}"]`);

        const p = document.createElement("p");
        p.innerText = `${materia.nombre}`;
        p.setAttribute('content', `${materia.nombre}`);
        domLabelOpcionesMaterias.appendChild(p);

        inputMateria.checked = true;
    });
}

/* Formulario enviado */

form.addEventListener("submit", async e => {
    e.preventDefault();
    const formData = new FormData(form);

    formData.append("id_curso", id);

    let respuesta = await fetch(`../../backend/cursos/editar.php`, { method: "POST", body: formData });
    respuesta = await respuesta.json();

    if (respuesta.ok) {
        await Swal.fire({
            title: "Curso Actualizado",
            text: `La información del curso fue actualizada exitosamente`,
            icon: "success"
        });
    }
    else {
        switch (respuesta.value) {
            case "NECESITA_LOGIN":
                Swal.fire({
                    title: "Login Requerido",
                    text: `Necesitas iniciar sesión para realizar esta acción.`,
                    icon: "error"
                });
                break;

            case "ID_CURSO_INVALIDA":
                Swal.fire({
                    title: "Espacio No Encontrado",
                    text: `No se ha seleccionado ningún espacio o la id no existe.`,
                    icon: "error"
                });
                break;
            case "NECESITA_ID_MATERIAS":
                Swal.fire({
                    title: "Debe Especificar Materias",
                    text: `Necesitas especificar las materias.`,
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
    respuesta = await fetch(`../../../backend/cursos/editar.php?id_curso=${id}`);
    respuesta = await respuesta.json();
    const estadoActualCurso = respuesta.value;

    listaMateriasOptions(estadoActualCurso.materias);
    addEvenListenersCheckboxes(domLabelOpcionesMaterias);

    // cargar los valores 
    mostrarValoresActuales(estadoActualCurso);
}

inicializar();
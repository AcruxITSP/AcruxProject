const domLabelOpcionesMaterias = document.getElementById("label-opcionesMaterias");
const domDivOpcionesMaterias = document.getElementById("opcionesMaterias");

const form = document.getElementById("form-editar-docente");

const urlParams = new URLSearchParams(window.location.search); //trae los parametros de la url
const id = urlParams.get("id"); // agarra el id de la url

/*Funciones */

domLabelOpcionesMaterias.addEventListener("click", () => {
    domDivOpcionesMaterias.classList.toggle("show");
});

function mostrarValoresActuales(profesor) {
    domNombre.value = profesor.nombre;

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

/* Envio del Formulario */

form.addEventListener("submit", async e => {
    e.preventDefault();
    const formData = new FormData(form);

    formData.append("id", id);


    Swal.fire({
        title: "Pagina en proceso",
        text: `La pagina aun no funciona`,
        icon: "error"
    });

    return;



    /* aun no hay un endpoint para editar la informacion de un docente */

    let respuesta = await fetch(`../../backend/usuarios/editar_profesor.php`, { method: "POST", body: formData });
    respuesta = await respuesta.json();

    if (respuesta.ok) {
        await Swal.fire({
            title: "Docente Actualizado",
            text: `La información del docente fue actualizada exitosamente`,
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

            case "ID_DOCENTE_INVALIDA":
                Swal.fire({
                    title: "Docente No Encontrado",
                    text: `No se ha seleccionado ningún docente o la id no existe.`,
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

async function inicializar() {
    let respuesta = await fetch(`../../../backend/asignaturas/ver.php`, { method: "GET" });
    respuesta = await respuesta.json();

    const materias = respuesta.value;

    listaMateriasOptions(materias);

    addEvenListenersCheckboxes(domLabelOpcionesMaterias);
}

inicializar();
const domInputSelectCurso = document.getElementById("select-curso");
const domInputSelectAdscrito = document.getElementById("select-adscrito");

const form = document.getElementById("form-editar-grupo");

/* Funciones */

function listaAdscritosOptions(adscritos){
    adscritos.forEach(adscrito => {
        const option = document.createElement("option");

        option.value = `${adscrito.id_adscrito}`;
        option.innerText = `${adscrito.nombre} ${adscrito.apellido}`;

        domInputSelectAdscrito.appendChild(option);
    });
}

function listaCursosOptions(cursos){
    cursos.forEach(curso => {
        const option = document.createElement("option");

        option.value = `${curso.id_curso}`;
        option.innerText = `${curso.nombre}`;

        domInputSelectCurso.appendChild(option);
    });
}

/* Formulario Enviado */

form.addEventListener("submit", async e => {
    e.preventDefault();
    const formData = new FormData(form);

    const urlParams = new URLSearchParams(window.location.search); //trae los parametros de la url
    const id = urlParams.get("id"); // agarra el id de la url

    formData.append("id_grupo", id);

    let respuesta = await fetch(`../../backend/grupos/editar.php`, { method: "POST", body: formData });
    respuesta = await respuesta.json();

    if (respuesta.ok) {
        await Swal.fire({
            title: "Grupo Actualizado",
            text: `La información del grupo fue actualizada exitosamente`,
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

            case "ID_GRUPO_INVALIDA":
                Swal.fire({
                    title: "Grupo No Encontrado",
                    text: `No se ha seleccionado ningún espacio o la id no existe.`,
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
    let respuestaCursos = await fetch(`../../../backend/cursos/ver.php`, {method:"GET"});
    respuestaCursos = await respuestaCursos.json();

    const cursos = respuestaCursos.value;
    listaCursosOptions(cursos);

    let respuestaAdscritos = await fetch(`../../../backend/usuarios/adscriptos.php`, {method:"GET"});
    respuestaAdscritos = await respuestaAdscritos.json();

    const adscritos = respuestaAdscritos.value;
    listaAdscritosOptions(adscritos);
}

inicializar();
const domInputSelectCurso = document.getElementById("select-curso");
const domInputSelectAdscrito = document.getElementById("select-adscrito");

const form = document.getElementById("form-editar-grupo");

/* Array con datos de adscritos. Ejemplo */
const jsonStringAdscritos = '[{"id_adscrito": "1", "nombre": "Juan", "apellido": "Carlos"}, {"id_adscrito": "2", "nombre": "Pancho", "apellido": "Gomez"}, {"id_adscrito": "3", "nombre": "Fabian", "apellido": "Sosa"}]';
const adscritos = JSON.parse(jsonStringAdscritos);

/* Array con cursos. Ejemplo */
const jsonStringCursos = '[{"id_curso": "1", "nombre": "Informatica"}, {"id_curso": "2", "nombre": "Informatica Bilingüe"}, {"id_curso": "3", "nombre": "Diseño Gráfico"}]';
const cursos = JSON.parse(jsonStringCursos);

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

listaAdscritosOptions(adscritos);
listaCursosOptions(cursos);

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
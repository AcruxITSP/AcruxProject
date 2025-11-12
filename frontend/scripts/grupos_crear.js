const domInputSelectCurso = document.getElementById("select-curso");
const domInputSelectAdscrito = document.getElementById("select-adscrito");

const form = document.getElementById("form-crear-grupo");

/* Registros de Adscritos de ejemplo */
const jsonStringAdscritos = '[{"id_adscrito": "1", "nombre": "Juan", "apellido": "Carlos"}, {"id_adscrito": "2", "nombre": "Pancho", "apellido": "Gomez"}, {"id_adscrito": "3", "nombre": "Fabian", "apellido": "Sosa"}]';
const adscritos = JSON.parse(jsonStringAdscritos);


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

/* Formularrio Enviado */

form.addEventListener("submit", async e => {
    e.preventDefault();
    const formData = new FormData(form);

    let respuesta = await fetch(`../../backend/grupos/crear.php`, {method:"POST", body: formData});
    respuesta = await respuesta.json();

    if(respuesta.ok)
    {
        await Swal.fire({
            title: "Grupo Creado",
            text: `El grupo ha sido creada exitosamente`,
            icon: "success"
        });
    }
    else
    {
        switch(respuesta.value)
        {
            case "NECESITA_LOGIN":
                Swal.fire({
                    title: "Login Requerido",
                    text: `Necesitas iniciar sesión para crear un grupo.`,
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

    // El endpoint no existe aun
    /*
    let respuestaAdscritos = await fetch(`../../../backend/usuarios/ver_adscripto.php`, {method:"GET"});
    respuestaAdscritos = await respuestaAdscritos.json();

    const adscritos = respuestaAdscritos.value;
    */
    listaAdscritosOptions(adscritos);
}

inicializar();
const domInputSelectCurso = document.getElementById("select-curso");
const domInputSelectAdscrito = document.getElementById("select-adscrito");

const form = document.getElementById("form-crear-grupo");

/* Array con datos de adscritos. Ejemplo */
const jsonStringAdscritos = '[{"id_adscrito": "1", "nombre": "Juan", "apellido": "Carlos"}, {"id_adscrito": "2", "nombre": "Pancho", "apellido": "Gomez"}, {"id_adscrito": "3", "nombre": "Fabian", "apellido": "Sosa"}]';
const adscritos = JSON.parse(jsonStringAdscritos);

/* Array con cursos. Ejemplo */
const jsonStringCursos = '[{"id_curso": "1", "nombre": "Informatica"}, {"id_curso": "2", "nombre": "Informatica Bilingüe"}, {"id_curso": "3", "nombre": "Diseño Gráfico"}]';
const cursos = JSON.parse(jsonStringCursos);

function IListAdscritosOptions(adscritos){
    adscritos.forEach(adscrito => {
        const option = document.createElement("option");

        option.value = `${adscrito.id_adscrito}`;
        option.innerText = `${adscrito.nombre} ${adscrito.apellido}`;

        domInputSelectAdscrito.appendChild(option);
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

IListAdscritosOptions(adscritos);
IListCursosOptions(cursos);

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
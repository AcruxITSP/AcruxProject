const domInputSelectMateria = document.getElementById("select-materia");
const domInputSelectProfesor = document.getElementById("select-profesor");
const domInputSelectEspacio = document.getElementById("select-espacio");

const form = document.getElementById("form-editar");

/* Array de materias de ejemplo */
const jsonStringMaterias = '[{"id_materia": "1", "nombre": "Programacion"}, {"id_materia": "2", "nombre": "Ciberseguridad" }, {"id_materia": "3", "nombre": "Biologia"}, {"id_materia": "4", "nombre": "Fisica"}, {"id_materia": "5", "nombre": "Logica"}, {"id_materia": "6", "nombre": "utulab"}, {"id_materia": "7", "nombre": "Sistemas Operativos"}, {"id_materia": "8", "nombre": "Filosofia"}, {"id_materia": "9", "nombre": "Sociologia"}]';
const materias = JSON.parse(jsonStringMaterias);

/* Array con datos de profesores. Ejemplo */
const jsonStringProfesores = '[{"id_profesor": "1", "nombre": "Juan", "apellido": "Carlos"}, {"id_profesor": "2", "nombre": "Pancho", "apellido": "Gomez"}, {"id_profesor": "3", "nombre": "Fabian", "apellido": "Sosa"}]';
const profesores = JSON.parse(jsonStringProfesores);

/* Array con datos de espacios. Ejemplo */
const jsonStringEspacios = '[{"id_espacio": "1", "tipo": "Salón", "numero": "1"}, {"id_espacio": "2", "tipo": "Aula", "numero": "2"}, {"id_espacio": "3", "tipo": "Taller", "numero": ""}]';
const espacios = JSON.parse(jsonStringEspacios);

/* Funciones */

function listaMateriasOptions(materias) {
    materias.forEach(materia => {
        const option = document.createElement("option");

        option.value = `${materia.id_materia}`;
        option.innerText = `${materia.nombre}`;

        domInputSelectMateria.appendChild(option);
    });
}

function listaProfesoresOptions(profesores) {
    profesores.forEach(profesor => {
        const option = document.createElement("option");

        option.value = `${profesor.id_profesor}`;
        option.innerText = `${profesor.nombre} ${profesor.apellido}`;

        domInputSelectProfesor.appendChild(option);
    });
}

function listaEspaciosOptions(espacios) {
    espacios.forEach(espacio => {
        const option = document.createElement("option");

        option.value = `${espacio.id_espacio}`;
        option.innerText = `${espacio.tipo} ${espacio.numero}`;

        domInputSelectEspacio.appendChild(option);
    });
}

listaMateriasOptions(materias);
listaProfesoresOptions(profesores);
listaEspaciosOptions(espacios);

/* Envio del formulario */

form.addEventListener("submit", async e => {
    e.preventDefault();
    const formData = new FormData(form);

    const urlParams = new URLSearchParams(window.location.search); //trae los parametros de la url
    const id = urlParams.get("id"); // agarra el id de la url

    formData.append("id", id);

    for (var pair of formData.entries()) {
        console.log(pair[0] + ', ' + pair[1]);
    }

    let respuesta = await fetch(`../../backend/horarios/grupos_editar_modulo.php`, { method: "POST", body: formData });
    respuesta = await respuesta.json();

    if (respuesta.ok) {
        await Swal.fire({
            title: "Módulo Actualizado",
            text: `La información del módulo fue actualizada exitosamente`,
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

            case "ID_MODULO_INVALIDA":
                Swal.fire({
                    title: "Módulo No Encontrado",
                    text: `No se ha seleccionado ningún módulo o la id no existe.`,
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
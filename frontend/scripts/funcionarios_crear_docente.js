const domLabelOpcionesMaterias = document.getElementById("label-opcionesMaterias");
const domDivOpcionesMaterias = document.getElementById("opcionesMaterias");

const form = document.getElementById("form-crear-docente");

/* Array de materias de ejemplo */
const jsonStringMaterias = '[{"id_materia": "1", "nombre": "Programacion"}, {"id_materia": "2", "nombre": "Ciberseguridad" }, {"id_materia": "3", "nombre": "Biologia"}, {"id_materia": "4", "nombre": "Fisica"}, {"id_materia": "5", "nombre": "Logica"}, {"id_materia": "6", "nombre": "utulab"}, {"id_materia": "7", "nombre": "Sistemas Operativos"}, {"id_materia": "8", "nombre": "Filosofia"}, {"id_materia": "9", "nombre": "Sociologia"}]';
const materias = JSON.parse(jsonStringMaterias);

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

/* Formulario enviado */

form.addEventListener("submit", async e => {
    e.preventDefault();
    const formData = new FormData(form);

    let respuesta = await fetch(`../../backend/usuarios/crear_profesor.php`, {method:"POST", body: formData});
    respuesta = await respuesta.json();

    if(respuesta.ok)
    {
        await Swal.fire({
            title: "Docente Registrado",
            text: `El docente ha sido registrado exitosamente`,
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
                    text: `Necesitas iniciar sesión para registrar un docente.`,
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
    let respuesta = await fetch(`../../../backend/asignaturas/ver.php`, {method:"GET"});
    respuesta = await respuesta.json();

    const materias = respuesta.value;
    
    listaMateriasOptions(materias);

    addEvenListenersCheckboxes(domLabelOpcionesMaterias);
}

inicializar();
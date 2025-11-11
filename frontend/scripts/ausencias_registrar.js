const domLabelOpcionesIntervalos = document.getElementById("label-opcionesIntervalos");
const domDivOpcionesIntervalos = document.getElementById("opcionesIntervalos");

const form = document.getElementById("form-registrar-ausencia");

/* Array de periodos de ejemplo */
const jsonStringPeriodos = '[{"id_periodo": "1", "entrada": "10:55", "salida": "11:05"}, {"id_periodo": "2", "entrada": "11:10", "salida": "11:55"}, {"id_periodo": "3", "entrada": "12:00", "salida": "12:45"}, {"id_periodo": "4", "entrada": "10:55", "salida": "11:05"}, {"id_periodo": "5", "entrada": "11:10", "salida": "11:55"}, {"id_periodo": "6", "entrada": "12:00", "salida": "12:45"}, {"id_periodo": "7", "entrada": "10:55", "salida": "11:05"}, {"id_periodo": "8", "entrada": "11:10", "salida": "11:55"}, {"id_periodo": "9", "entrada": "12:00", "salida": "12:45"}]';
const periodos = JSON.parse(jsonStringPeriodos);

/*Funciones */

domLabelOpcionesIntervalos.addEventListener("click", () => {
    domDivOpcionesIntervalos.classList.toggle("show");
});


function listaPeriodosOptions(periodos) {
    periodos.forEach(periodo => {
        const label = document.createElement("label");
        const input = document.createElement("input");

        const nodePeriodo = document.createTextNode(`${periodo.entrada} - ${periodo.salida}`);

        input.value = `${periodo.id_periodo}`;
        input.type = "checkbox";
        input.name = "id_periodo[]"

        label.appendChild(input);
        label.appendChild(nodePeriodo);

        domDivOpcionesIntervalos.appendChild(label);
    });
}

listaPeriodosOptions(periodos);

/* Envio del formulario */

form.addEventListener("submit", async e => {
    e.preventDefault();
    const formData = new FormData(form);

    let respuesta = await fetch(`../../backend/ausencias/my.php`, { method: "POST", body: formData });
    respuesta = await respuesta.json();

    if (respuesta.ok) {
        await Swal.fire({
            title: "Ausencia Registrada",
            text: `La ausencia fue informada con éxito`,
            icon: "success"
        });
    }
    else {
        switch (respuesta.value) {
            case "NECESITA_LOGIN":
                Swal.fire({
                    title: "Login Requerido",
                    text: `Necesitas iniciar sesion para relizar esta acción.`,
                    icon: "error"
                });
                break;

            case "ID_ESPACIO_INVALIDA":
                Swal.fire({
                    title: "Espacio No Encontrado",
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
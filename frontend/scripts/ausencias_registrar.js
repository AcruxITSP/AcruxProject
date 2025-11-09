const domLabelOpcionesIntervalos = document.getElementById("label-opcionesIntervalos");
const domDivOpcionesIntervalos = document.getElementById("opcionesIntervalos");

/* Array de periodos de ejemplo */
const jsonStringPeriodos = '[{"id_periodo": "1", "entrada": "10:55", "salida": "11:05"}, {"id_periodo": "2", "entrada": "11:10", "salida": "11:55"}, {"id_periodo": "3", "entrada": "12:00", "salida": "12:45"}, {"id_periodo": "4", "entrada": "10:55", "salida": "11:05"}, {"id_periodo": "5", "entrada": "11:10", "salida": "11:55"}, {"id_periodo": "6", "entrada": "12:00", "salida": "12:45"}, {"id_periodo": "7", "entrada": "10:55", "salida": "11:05"}, {"id_periodo": "8", "entrada": "11:10", "salida": "11:55"}, {"id_periodo": "9", "entrada": "12:00", "salida": "12:45"}]';
const periodos = JSON.parse(jsonStringPeriodos);

/*Funciones */

domLabelOpcionesIntervalos.addEventListener("click", () => {
    domDivOpcionesIntervalos.classList.toggle("show");
});


function IListPeriodosOptions(periodos) {
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

IListPeriodosOptions(periodos);
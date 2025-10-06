import { client_hora_fetchAll } from "./cliente.js";

document.addEventListener('DOMContentLoaded', async e => {
    const regexPaginaVer = /paginas\/planillaHorario\/ver\.php/;
    const regexPaginaRegister = /paginas\/planillaHorario\/registrar\.php/;
    const regexPaginaEditar = /paginas\/planillaHorario\/editar\.php/;
    const ubicacionActual = String(window.location.href);

    if (regexPaginaVer.test(ubicacionActual)) {
        mostrarPlanilla();
    } else if (regexPaginaRegister.test(ubicacionActual)) {
        waitFormSubmit();
    } else if (regexPaginaEditar.test(ubicacionActual)) {
        // Pagina para editar la informacion de las materias
    } else {
        // Pagina no identificada
    }
});

async function mostrarPlanilla() {
    const tpl = document.getElementById("tpl");
    const domErrorMsg = document.getElementById("errorMsg");

    const registros = await client_hora_fetchAll();

    if (registros == null) {
        domErrorMsg.innerText = "Aun no se ha creado una planilla de horario";
        return;
    }

    const clone = tpl.content.cloneNode(true);
    const cloneTable = clone.querySelector("table");
    const cloneTableFirstRow = clone.querySelector("tr");

    const domTabla = document.getElementById("tablaHorario");
    const fragment = document.createDocumentFragment();
    fragment.appendChild(cloneTable);
    let dias = [];
    let intervalos = [];

    for (let i = 0; i < registros.length; i++) {
        if (!(dias.includes(registros[i].dia))) {
            dias.push(registros[i].dia);
        }

        if (!(intervalos.includes(registros[i].intervalo))) {
            intervalos.push(registros[i].intervalo);
        }
    }

    for (let i = 0; i < dias.length; i++) {
        const th = document.createElement("th");
        th.textContent = dias[i];

        cloneTableFirstRow.appendChild(th);
    }

    fragment.appendChild(cloneTableFirstRow);

    for (let i = 0; i < intervalos.length; i++) {
        const tr = document.createElement("tr");
        const td = document.createElement("td");

        td.textContent = intervalos[i];
        tr.appendChild(td);

        for (let i = 0; i < dias.length; i++) {
            const emptyTr = document.createElement("td");
            tr.appendChild(emptyTr);
        }

        fragment.appendChild(tr);
    }

    domTabla.appendChild(fragment);
}
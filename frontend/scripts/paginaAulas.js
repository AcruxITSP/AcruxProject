import { client_aulas_fetchAll } from "./cliente.js";

document.addEventListener('DOMContentLoaded', async e => {
    const regexPaginaVer = /paginas\/aulas\/ver\.php/;
    const regexPaginaRegister = /paginas\/aulas\/registrar\.php/;
    const regexPaginaEditar = /paginas\/aulas\/editar\.php/;
    const ubicacionActual = String(window.location.href);

    if (regexPaginaVer.test(ubicacionActual)) {
        listarRegistrosAulas();
    } else if (regexPaginaRegister.test(ubicacionActual)) {
        waitFormSubmit();
    } else if (regexPaginaEditar.test(ubicacionActual)) {
        // Pagina para editar la informacion de las materias
    } else {
        // Pagina no identificada
    }
});

async function listarRegistrosAulas() {
    const tpl = document.getElementById("tpl");
    const domErrorMsg = document.getElementById("errorMsg");
    
    const registros = await client_aulas_fetchAll();

    if (registros == null) {
        domErrorMsg.innerText = "No hay ningun aula registrada";
        return;
    }

    const clone = tpl.content.cloneNode(true);
    const cloneTable = clone.querySelector("table");
    const domTabla = document.getElementById("tablaAulas");

    for (let i = 0; i < registros.length; i++) {
        const nodeCodigo = document.createTextNode(registros[i].codigo);
        const nodePiso = document.createTextNode(registros[i].piso);
        const nodeProposito = document.createTextNode(registros[i].proposito);
        const nodeCapacidad = document.createTextNode(registros[i].cantidadSillas);

        const trNode = document.createElement("tr");

        const tdNode1 = document.createElement("td");
        const tdNode2 = document.createElement("td");
        const tdNode3 = document.createElement("td");
        const tdNode4 = document.createElement("td");

        tdNode1.appendChild(nodeCodigo);
        tdNode2.appendChild(nodePiso);
        tdNode3.appendChild(nodeProposito);
        tdNode4.appendChild(nodeCapacidad);

        trNode.appendChild(tdNode1);
        trNode.appendChild(tdNode2);
        trNode.appendChild(tdNode3);
        trNode.appendChild(tdNode4);

        cloneTable.appendChild(trNode);

        domTabla.appendChild(clone);
    }
}
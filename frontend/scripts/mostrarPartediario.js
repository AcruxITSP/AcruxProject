import { client_parteDiario_fetchAll } from "./cliente.js";

document.addEventListener('DOMContentLoaded', async e => {
    mostrarRegistrosPartediario();
});

async function mostrarRegistrosPartediario() {
    const tpl = document.getElementById("tpl");
    const domErrorMsg = document.getElementById("errorMsg");
    
    const registros = await client_parteDiario_fetchAll();

    if (registros == null) {
        domErrorMsg.innerText = "No hay ningun registro en el Parte Diario";
        return;
    }

    const clone = tpl.content.cloneNode(true);
    const cloneTable = clone.querySelector("table");
    const domTabla = document.getElementById("tablaRegistros");

    for (let i = 0; i < registros.length; i++) {
        let fecha_hora = registros[i].fechaHora.split(" ");

        const nodeFecha = document.createTextNode(fecha_hora[0]);
        const nodeHora = document.createTextNode(fecha_hora[1]);
        const nodeAccion = document.createTextNode(registros[i].accion);

        const trNode = document.createElement("tr");

        const tdNode1 = document.createElement("td");
        const tdNode2 = document.createElement("td");
        const tdNode3 = document.createElement("td");

        tdNode1.appendChild(nodeFecha);
        tdNode2.appendChild(nodeHora);
        tdNode3.appendChild(nodeAccion);

        trNode.appendChild(tdNode1);
        trNode.appendChild(tdNode2);
        trNode.appendChild(tdNode3);

        cloneTable.appendChild(trNode);

        domTabla.appendChild(clone);
    }
}
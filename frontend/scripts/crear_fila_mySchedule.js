const btn_crear = document.getElementById("btn-agregar-fila");
const tabla_horas = document.querySelector("tbody.tabla-body");
const tpl_fila = document.getElementById("tpl-fila");

btn_crear.addEventListener("click", () => {
    crearFila("16:10 - 16:55", "Salon 3", "2° BC");
});

function crearFila(intervalo, espacio, grupo) {
    const clon = tpl_fila.content.cloneNode(true);

    clon.querySelector("td.hora").textContent = intervalo;
    clon.querySelector("td.espacio").textContent = espacio;
    clon.querySelector("td.grupo").textContent = grupo;

    tabla_horas.appendChild(clon);
}
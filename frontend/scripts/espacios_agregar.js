const lista_espacios = document.getElementById("lista-espacios");
const tpl_targeta = document.getElementById("tpl-targeta-espacio");
const btn_agregar_espacio = document.getElementById("btn-agregar-espacio");

btn_agregar_espacio.addEventListener("click", () => {
    // El parametro de color se puede quitar
    agregarEspacio("Salon3", "Libre", "green")
});

function agregarEspacio(nombre, estado, color) {
    const targeta_espacio = tpl_targeta.content.cloneNode(true);

    targeta_espacio.querySelector(".espacio-info h1").textContent = nombre;
    targeta_espacio.querySelector(".espacio-info p").textContent = estado;
    targeta_espacio.querySelector(".espacio-info p").style.color = color;

    lista_espacios.appendChild(targeta_espacio);
}
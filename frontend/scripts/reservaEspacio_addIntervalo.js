const imagen_espacio = document.getElementById("btn-testeo");
const tpl_targeta = document.getElementById("tpl-targeta-hora-espacio");
const lista_horas = document.getElementById("lista-horas-espacio");

imagen_espacio.addEventListener("click", () => {
    addTargeta("Ocupado", "12:00 - 12:45", "F. Fagundez - 1MD");
});

function addTargeta(estado, intervalo, reservante) {
    const clon = tpl_targeta.content.cloneNode(true);

    clon.querySelector(".estado").textContent = estado;
    clon.querySelector(".intervalo").textContent = intervalo;
    clon.querySelector(".reservante").textContent = reservante;

    lista_horas.appendChild(clon);
}
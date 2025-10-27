const btn_crear = document.getElementById("btn-agregar-grupo");
const tpl_targeta = document.getElementById("tpl-targeta-grupo");
const lista_grupos = document.getElementById("lista-targetas-grupos");

btn_crear.addEventListener("click", () => {
    crearGrupo("2BC", "Pancho Mendez", "Robótica");
});

function crearGrupo(codigo, adscripta, curso){
    const clon = tpl_targeta.content.cloneNode(true);

    clon.querySelector("h1.nombre-grupo").textContent = codigo;
    clon.querySelector("p.nombre-adscripta").textContent = adscripta;
    clon.querySelector("p.nombre-curso").textContent = curso;

    lista_grupos.appendChild(clon);
}
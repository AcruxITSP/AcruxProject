const scroll_list = document.getElementById("scrollable-list-intervalos");
const tpl_targeta = document.getElementById("tpl-targeta-intervalo");
const btn_add_hora = document.getElementById("btn-add-hora");

btn_add_hora.addEventListener("click", () => {
    clonarTargeta("11:10 - 11:55");
});

function clonarTargeta(intervalo) {
    const clon_targeta = tpl_targeta.content.cloneNode(true);
    clon_targeta.querySelector("div p").textContent = intervalo;
    scroll_list.appendChild(clon_targeta);
}
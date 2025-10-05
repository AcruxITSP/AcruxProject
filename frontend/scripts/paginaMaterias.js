import { client_materias_fetchAll, client_materias_register } from "./cliente.js";

document.addEventListener('DOMContentLoaded', async e => {
    const regexPaginaVer = /paginas\/materias\/ver\.php/;
    const regexPaginaRegister = /paginas\/materias\/registrar\.php/;
    const regexPaginaEditar = /paginas\/materias\/editar\.php/;
    const ubicacionActual = String(window.location.href);

    if (regexPaginaVer.test(ubicacionActual)) {
        showListMaterias();
    } else if (regexPaginaRegister.test(ubicacionActual)) {
        waitFormSubmit();
    } else if (regexPaginaEditar.test(ubicacionActual)) {
        // Pagina para editar la informacion de las materias
    } else {
        // Pagina no identificada
    }
});

async function showListMaterias() {
    const domErrorMsg = document.getElementById("errorMsg");

    const registros = await client_materias_fetchAll();

    if (registros == null) {
        domErrorMsg.innerText = "No hay ninguna materia registrada";
        return;
    }

    const domListaMaterias = document.getElementById("listaMaterias");
    const tpl_materias = document.getElementById("tpl-lista-materias");

    const fragment = document.createDocumentFragment();

    for (let i = 0; i < registros.length; i++) {
        const clonListaMaterias = tpl_materias.cloneNode(true);

        fragment.appendChild(clonListaMaterias);

        const li_element = document.createElement("li");
        li_element.textContent = registros[i].nombre;

        fragment.appendChild(li_element);

        domListaMaterias.appendChild(fragment);
    }

}

async function waitFormSubmit(){
    const dom_formMaterias = document.getElementById("form-register-materias");

    dom_formMaterias.addEventListener("submit", async e => {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);

        const respuesta = await client_materias_register(formData.nombre);

        if (respuesta === true){
            console.log("Materia registrada exitosamente");
        } else {
            console.log("ERROR: " + respuesta);
        }
    });
}
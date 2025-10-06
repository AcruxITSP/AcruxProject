import { client_cursos_fetchAll } from "./cliente.js";

const btn_addCurso = document.getElementById("agregarCurso");

document.addEventListener('DOMContentLoaded', async e => {
    mostrarRegistrosCursos();
});

btn_addCurso.addEventListener('change', async e => {
    if (e.target.checked) {
        showFormCursos();
    } else {
        hideFormCursos();
    }
});


async function mostrarRegistrosCursos() {
    const domErrorMsg = document.getElementById("errorMsg");

    const registros = await client_cursos_fetchAll();

    if (registros == null) {
        domErrorMsg.innerText = "No hay ningun curso registrado";
        return;
    }

    const domListaCursos = document.getElementById("listaCursos");
    const tpl_cursos = document.getElementById("tpl-lista-cursos");
    const tpl_form_materias = document.getElementById("tpl-form-materias");

    const fragment = document.createDocumentFragment();

    for (let i = 0; i < registros.length; i++) {
        const clonFormMaterias = tpl_form_materias.content.cloneNode(true);
        const domTplList = tpl_cursos.content.cloneNode(true);

        fragment.appendChild(domTplList);

        const arrayMaterias = registros[i].materias;

        fragment.querySelector("h3").textContent = registros[i].nombre;

        for (let i = 0; i < arrayMaterias.length; i++) {
            const materia = arrayMaterias[i];
            const li = document.createElement("li");
            li.textContent = materia;

            fragment.querySelector(".lista-materias").appendChild(li);
        }

        const li = document.createElement("li");
        li.appendChild(clonFormMaterias);
        fragment.querySelector(".lista-materias").appendChild(li);


        domListaCursos.appendChild(fragment);
    }
}

async function showFormCursos() {
    const tpl_form_cursos = document.getElementById("tpl-form-cursos");
    const form_div = document.getElementById("form-cursos");

    const clonForm = tpl_form_cursos.content.cloneNode(true);
    form_div.appendChild(clonForm);
}

async function hideFormCursos() {
    const form_div = document.getElementById("form-cursos");

    form_div.innerHTML = null;
}
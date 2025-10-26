const btn_crear = document.getElementById("crear-con-template");
const tpl_targeta_curso = document.getElementById("tpl-targeta-curso");
const cursos_terciarios = document.querySelector(".div-cursos-terciarios");
const cursos_educacion_media = document.querySelector(".div-cursos-educacion-media");

// Array con materias de ejemplo
let materias = ["Matematicas", "Diseño Web", "Programacón", "Física"];

btn_crear.addEventListener("click", () => {
    crearCurso("Diseño gráfico", "medio", materias);
});

function crearCurso(nombre, nivel, materias) {
    const clon = tpl_targeta_curso.content.cloneNode(true);

    clon.querySelector("p.nombre-curso").textContent = nombre;
    const lista_materias = clon.querySelector("ul.curso-materias");

    for (let i = 0; i < materias.length; i++){
        const li = document.createElement("li");
        li.textContent = materias[i];
        lista_materias.appendChild(li);
    }

    switch(nivel) {
        case "terciario":
            cursos_terciarios.appendChild(clon);
            break;
        
        case "medio":
            cursos_educacion_media.appendChild(clon);
            break;
        
        default:
            console.log("No existe una seccion para cursos de nivel " + nivel);
    }
}
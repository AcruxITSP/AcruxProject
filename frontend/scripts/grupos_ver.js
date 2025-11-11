const domTemplateTarjetaGrupo = document.getElementById("tpl-targeta-grupo");
const domDivGrupos = document.getElementById("lista-targetas-grupos");

// Objeto de usuario (adscripta a cargo del grupo) de ejemplo
const jsonStringUsuario = '{"ci": "44444444", "nombre": "Juan", "apellido": "Hernandez", "contrasena": "admin", "email": "juancarlos@gmail.com", "cargo": "Profesor"}';
const usuario = JSON.parse(jsonStringUsuario);

// Objetos de grupo y curso de ejemplo
const jsonStringGrupo = '{"grado": "3", "nombre": "MD"}';
const grupo = JSON.parse(jsonStringGrupo);

const jsonStringCurso = '{"nombre": "Informatica Bilingüe"}';
const curso = JSON.parse(jsonStringCurso);

/* Funciones */

function uiCrearTarjetaGrupo(grupo, curso, infoAdscrito){
    const domDivTarjeta = domTemplateTarjetaGrupo.content.cloneNode(true).children[0];

    const domNombreGrupo = domDivTarjeta.querySelector("[name='nombre-grupo']");
    const domNombreAdscrito = domDivTarjeta.querySelector("[name='nombre-adscripta']");
    const domNombreCurso = domDivTarjeta.querySelector("[name='nombre-curso']");

    domNombreGrupo.innerText = `${grupo.grado}°${grupo.nombre}`;
    domNombreAdscrito.innerText = `${infoAdscrito.nombre} ${infoAdscrito.apellido}`;
    domNombreCurso.innerText = `${curso.nombre}`;
    
    domDivGrupos.appendChild(domDivTarjeta);
}

uiCrearTarjetaGrupo(grupo, curso, usuario);
const domTemplateTarjetaGrupo = document.getElementById("tpl-targeta-grupo");
const domDivGrupos = document.getElementById("lista-targetas-grupos");

// Objeto de usuario (adscripta a cargo del grupo) de ejemplo
const jsonStringUsuario = '{"ci": "44444444", "nombre": "Juan", "apellido": "Hernandez", "contrasena": "admin", "email": "juancarlos@gmail.com", "cargo": "Profesor"}';
const usuario = JSON.parse(jsonStringUsuario);

// Objetos de grupo y curso de ejemplo
const jsonStringGrupo = '{"id_grupo": 1, "grado": "3", "nombre": "MD"}';
const grupo = JSON.parse(jsonStringGrupo);

const jsonStringCurso = '{"id_curso": 1, "nombre": "Informática Bilingue"}';
const curso = JSON.parse(jsonStringCurso);

/* Funciones */

function uiCrearTarjetaGrupo(grupo, curso, infoAdscrito){
    const domDivTarjeta = domTemplateTarjetaGrupo.content.cloneNode(true).children[0];

    const domNombreGrupo = domDivTarjeta.querySelector("[name='nombre-grupo']");
    const domNombreAdscrito = domDivTarjeta.querySelector("[name='nombre-adscripta']");
    const domNombreCurso = domDivTarjeta.querySelector("[name='nombre-curso']");
    const domButtonBorrar = domDivTarjeta.querySelector("[name='borrar']");
    const domButtonEditar = domDivTarjeta.querySelector("[name='editar']");

    domNombreGrupo.innerText = `${grupo.grado}°${grupo.nombre}`;
    if(infoAdscrito) domNombreAdscrito.innerText = `${infoAdscrito.nombre} ${infoAdscrito.apellido}`;
    if(curso) domNombreCurso.innerText = `${curso.nombre}`;

    if(domButtonEditar) domButtonEditar.onclick = () => editarGrupo(grupo.id_grupo);
    if(domButtonBorrar) domButtonBorrar.onclick = () => borrarGrupoAsync(grupo.id_grupo);
    
    domDivGrupos.appendChild(domDivTarjeta);
}

function editarGrupo(idGrupo)
{
    location.href = `editar.php?id=${idGrupo}`;
}

async function borrarGrupoAsync(idGrupo)
{
    const resultadoDeAdvertencia = await Swal.fire({
        title: "Borrar Grupo",
        text: "",
        icon: "info",

        showCancelButton: true,
        cancelButtonText: "Cancelar",
        confirmButtonText: "Borrar"
    });

    if(!resultadoDeAdvertencia.isConfirmed) return;

    let formData = new FormData();
    formData.append("id", idGrupo)
    let respuesta = await fetch('../../backend/grupos/borrar.php', {method: "POST", body: formData});
    respuesta = await respuesta.json();
    
    if(respuesta.ok)
    {
        await Swal.fire({
            title: "Grupo Eliminado!",
            text: `El grupo ha sido eliminado exitosamente.`,
            icon: "success"
        });
        location.reload();
    }
    else
    {
        switch(respuesta.value)
        {
            case "NECESITA_LOGIN":
                Swal.fire({
                    title: "Login Requerido",
                    text: `Necesitas iniciar sesion para eliminar un grupo.`,
                    icon: "error"
                });
                break;
            default:
                Swal.fire({
                    title: "Error Desconocido",
                    text: `Un error desconocido ha ocurrido`,
                    icon: "error"
                });
        }
    }
}

document.addEventListener('DOMContentLoaded', async e =>{
    let respuesta = await fetch("../../backend/grupos/ver.php");
    respuesta = await respuesta.json();

    if(!respuesta.ok)
    {
        {
            switch(respuesta.value)
            {
                case "NECESITA_LOGIN":
                    Swal.fire({
                        title: "Login Requerido",
                        text: `Necesitas iniciar sesion para eliminar un grupo.`,
                        icon: "error"
                    });
                    break;
                default:
                    Swal.fire({
                        title: "Error Desconocido",
                        text: `Un error desconocido ha ocurrido`,
                        icon: "error"
                    });
            }
        }

        return;
    }

    const grupos = respuesta.value;
    grupos.forEach(grupo => {

        uiCrearTarjetaGrupo(grupo, grupo.curso, grupo.adscrito);
    });
});
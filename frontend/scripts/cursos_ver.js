const domTemplateCurso = document.getElementById("tpl-targeta-curso");
const domCursoContainer = document.getElementById("curso-container");

function uiCrearCurso(idCurso, nombreCurso, nombreMaterias)
{
    const domDivCurso = domTemplateCurso.content.cloneNode(true);
    const domNombreCurso = domDivCurso.querySelector("[name='nombre-curso']");
    const domMaterias = domDivCurso.querySelector("[name='materias']");
    const domButtonBorrar = domDivCurso.querySelector("[name='button-borrar']");
    const domButtonEditar = domDivCurso.querySelector("[name='button-editar']");

    domNombreCurso.innerText = nombreCurso;

    domMaterias.innerHTML = '';
    nombreMaterias.forEach(nombreMateria => {
        domMaterias.innerHTML += `<li>${nombreMateria}</li>`;
    });

    if(domButtonBorrar) domButtonBorrar.onclick = () => borrarRecursoAsync(idCurso);
    if(domButtonEditar) domButtonEditar.onclick = () => editarRecurso(idCurso);

    return domDivCurso;
} 

function uiAgregarCurso(idCurso, nombreCurso, nombreMaterias)
{
    const domDivCurso = uiCrearCurso(idCurso, nombreCurso, nombreMaterias);
    domCursoContainer.appendChild(domDivCurso);
}

async function borrarRecursoAsync(idCurso)
{
    const resultadoDeAdvertencia = await Swal.fire({
        title: "Borrar Curso",
        text: `Se borraran los grupos relacionados.`,
        icon: "info",

        showCancelButton: true,
        cancelButtonText: "Cancelar",
        confirmButtonText: "Borrar"
    });

    if(!resultadoDeAdvertencia.isConfirmed) return;

    let formData = new FormData();
    formData.append("id", idCurso)
    let respuesta = await fetch('../../backend/cursos/borrar.php', {method: "POST", body: formData});
    respuesta = await respuesta.json();
    
    if(respuesta.ok)
    {
        await Swal.fire({
            title: "Curso Eliminado!",
            text: `El curso ha sido eliminado exitosamente.`,
            icon: "success"
        });
        inicializar();
    }
    else
    {
        switch(respuesta.value)
        {
            case "NECESITA_LOGIN_ADSCRIPTO":
                Swal.fire({
                    title: "Login Requerido",
                    text: `Necesitas iniciar sesion como adscripto para eliminar un curso.`,
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

async function inicializar()
{
    domCursoContainer.innerHTML = '';
    let response = await fetch("../../backend/cursos/ver.php");
    response = await response.json();
    const cursos = response.value;

    cursos.forEach(curso => {
        const materiasDelCurso = curso.materias.map(materia => materia.nombre);
        uiAgregarCurso(curso.id_curso, curso.nombre, materiasDelCurso);
    });
    
}

inicializar();
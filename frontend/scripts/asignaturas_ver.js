const domTemplateAsignatura = document.getElementById("tpl-targeta-asignatura");
const domAsignaturaContainer = document.getElementById("asignatura-container");

function uiCrearAsignatura(idAsignatura, nombre, cursos, docentes)
{
    const domDivAsignatura = domTemplateAsignatura.content.cloneNode(true);
    const domNombre = domDivAsignatura.querySelector("[name='nombre']");
    const domListaCursos = domDivAsignatura.querySelector("[name='li-cursos']");
    const domListaDocentes = domDivAsignatura.querySelector("[name='li-docentes']");
    const domButtonBorrar = domDivAsignatura.querySelector("[name='button-borrar']");
    const domButtonEditar = domDivAsignatura.querySelector("[name='button-editar']");

    domNombre.innerText = nombre;
    cursos.forEach(curso => {
        domListaCursos.innerHTML += `<li>${curso}</li>`;
    });

    docentes.forEach(docente => {
        domListaDocentes.innerHTML += `<li>${docente}</li>`;
    });

    if(domButtonBorrar) domButtonBorrar.onclick = async () => { await borrarAsignaturaAsync(idAsignatura) };

    return domDivAsignatura;
}

function uiAgregarAsignatura(idAsignatura, nombre, cursos, docentes)
{
    const domDivAsignatura = uiCrearAsignatura(idAsignatura, nombre, cursos, docentes);
    domAsignaturaContainer.appendChild(domDivAsignatura);
}

async function borrarAsignaturaAsync(id)
{
    const resultadoDeAdvertencia = await Swal.fire({
        title: "Borrar Asignatura",
        text: "Los horarios relacionados tambien seran eliminados",
        icon: "info",

        showCancelButton: true,
        cancelButtonText: "Cancelar",
        confirmButtonText: "Borrar"
    });

    if(!resultadoDeAdvertencia.isConfirmed) return;

    let formData = new FormData();
    formData.append("id", id)
    let respuesta = await fetch('../../backend/asignaturas/borrar.php', {method: "POST", body: formData});
    respuesta = await respuesta.json();
    
    if(respuesta.ok)
    {
        await Swal.fire({
            title: "Asignatura Eliminada!",
            text: `La asignatura ha sido eliminado exitosamente.`,
            icon: "success"
        });
        inicializar();
    }
    else
    {
        switch(respuesta.value)
        {
            case "NECESITA_LOGIN":
                Swal.fire({
                    title: "Login Requerido",
                    text: `Necesitas iniciar sesion para eliminar un recurso.`,
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
    domAsignaturaContainer.innerHTML = '';
    let response = await fetch("../../backend/asignaturas/ver.php");
    response = await response.json();

    const asignaturas = response.value;
    asignaturas.forEach(asignatura => {
        const nombresDocentes = asignatura.docentes.map(p => `${p.nombre_profesor} ${p.apellido_profesor}`);
        const nombresCursos = asignatura.cursos.map(p => p.nombre_curso);
        const idAsignatura = asignatura.id_materia;
        uiAgregarAsignatura(idAsignatura, asignatura.nombre, nombresCursos, nombresDocentes);
    });
}

document.addEventListener('DOMContentLoaded', async e => {
    await inicializar();
})
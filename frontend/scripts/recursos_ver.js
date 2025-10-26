const domDivRecursosExternos = document.getElementById("div-recursos-externos");
const domDivRecursosInternos = document.getElementById("div-recursos-internos");
const domTemplateDivRecursoExterno = document.getElementById("template-div-recurso-externo");
const domTemplateDivRecursoInterno = document.getElementById("template-div-recurso-interno");
const domTemplateDivEspacioDeRecursoInterno = document.getElementById("template-recurso-interno-espacio");
/////////////////////////////////////////////
function uiCrearDivRecursoExterno(recurso, todosLosEspacios)
{
    let numeroDelRecurso = null;
    let tipoEspacio = null; 
    let codigoEspacio = null;

    if(recurso.id_espacio != null)
    {
        numeroDelRecurso = todosLosEspacios[recurso.id_espacio];
        tipoEspacio = numeroDelRecurso.tipo;
        codigoEspacio = numeroDelRecurso.numero;
    }

    const domDivRecursoExterno = domTemplateDivRecursoExterno.content.cloneNode(true);
    const domId = domDivRecursoExterno.querySelector('[name="id"]');
    const domImagen = domDivRecursoExterno.querySelector("[name='imagen']");
    const domTipo = domDivRecursoExterno.querySelector("[name='tipo']");
    const domEspacio = domDivRecursoExterno.querySelector("[name='aula']");
    const domLibre = domDivRecursoExterno.querySelector("[name='libre']");
    const domOcupado = domDivRecursoExterno.querySelector("[name='ocupado']");
    const domBorrar = domDivRecursoExterno.querySelector("[name='borrar']");
    const domEditar = domDivRecursoExterno.querySelector("[name='editar']");
    const domReservar = domDivRecursoExterno.querySelector("[name='reservar']");

    domId.innerText = recurso.id_recurso;
    //domImagen = recurso.id_recurso;
    domTipo.innerText = recurso.tipo;
    domEspacio.innerText = numeroDelRecurso == null ? `Independiente del espacio.` : `${tipoEspacio} ${codigoEspacio ?? ''}`;
    domLibre.innerText = `Libre: ${recurso.cantidad_libre}`;
    domOcupado.innerText = `Ocupado: ${recurso.cantidad_ocupado}`;
    if(domBorrar) domBorrar.onclick = () => {borrarRecursoAsync(recurso.id_recurso, 'externo');};
    if(domEditar) domEditar.onclick = () => {irAPaginaEditarExterno(recurso.id_recurso); };
    if(domReservar) domReservar.onclick = () => {location.href=`externos/reservar.php?id=${recurso.id_recurso}`;};
    return domDivRecursoExterno;
}

function uiAgregarDivRecursoExterno(recurso, todosLosEspacios)
{
    const divRecurso = uiCrearDivRecursoExterno(recurso, todosLosEspacios);
    domDivRecursosExternos.appendChild(divRecurso);
}
//////////////////////////////////////////////////////////////
function uiCrearDivRecursoInterno(recurso, todosLosEspacios)
{
    const domDivRecursoInterno = domTemplateDivRecursoInterno.content.cloneNode(true);
    const domId = domDivRecursoInterno.querySelector('[name="id"]');
    const domImagen = domDivRecursoInterno.querySelector("[name='imagen']");
    const domEspacios = domDivRecursoInterno.querySelector("[name='espacios']");
    const domTipo = domDivRecursoInterno.querySelector("[name='tipo']");
    const domBorrar = domDivRecursoInterno.querySelector("[name='borrar']");
    const domEditar = domDivRecursoInterno.querySelector("[name='editar']");

    domId.innerText = recurso.id_recurso;
    //domImagen = recurso.id_recurso;
    domTipo.innerText = recurso.tipo;
    if(domBorrar) domBorrar.onclick = () => {borrarRecursoAsync(recurso.id_recurso, 'interno');};
    if(domEditar) domEditar.onclick = () => {irAPaginaEditarInterno(recurso.id_recurso); };

    const cantidadEnEspacios = recurso.cantidad_en_espacios;
    cantidadEnEspacios.forEach(cantidadEnEspacio => {
        const espacio =  todosLosEspacios[cantidadEnEspacio.id_espacio];
        const estaDisponible = espacio.libre
        const cantidad =  cantidadEnEspacio.cantidad;
        const domEspacio = uiCrearDivEspacioDeRecursoInterno(espacio.id_espacio, espacio.tipo, espacio.numero, cantidad, estaDisponible);
        domEspacios.appendChild(domEspacio);
    });

    return domDivRecursoInterno;
}

function uiAgregarDivRecursoInterno(recurso, todosLosEspacios)
{
    const divRecurso = uiCrearDivRecursoInterno(recurso, todosLosEspacios);
    domDivRecursosInternos.appendChild(divRecurso);
}

function uiCrearDivEspacioDeRecursoInterno(idEspacio, tipoEspacio, numeroEspacio, cantidadDelRecurso, estaDisponible)
{
    const divEspacioDeRecursoInterno = domTemplateDivEspacioDeRecursoInterno.content.cloneNode(true);
    const domEspacio = divEspacioDeRecursoInterno.querySelector('[name="espacio"]');
    const domCantidad = divEspacioDeRecursoInterno.querySelector('[name="cantidad"]');
    const domDisponibilidad = divEspacioDeRecursoInterno.querySelector('[name="disponibilidad"]');
    const domReservar = divEspacioDeRecursoInterno.querySelector('[name="reservar"]');

    domEspacio.innerText = `En ${tipoEspacio} ${numeroEspacio ?? ''}`;
    domCantidad.innerText = `Hay ${cantidadDelRecurso}`;
    domDisponibilidad.innerText = estaDisponible ? 'Salon Libre' : 'Salon Ocupado';
    if(domReservar) domReservar.href = `../espacios/reservar.php?id=${idEspacio}`;

    return divEspacioDeRecursoInterno;

}
////////////////////////////////////////////////////////////

function irAPaginaEditarExterno(idRecurso)
{
    Swal.fire({
        title: "En mantenimiento",
        icon: "info"
    });
    return;
    
    location.href=`externos/editar.php?id=${idRecurso}`;
}

function irAPaginaEditarInterno(idRecurso)
{
    Swal.fire({
        title: "En mantenimiento",
        icon: "info"
    });
    return;

    location.href=`internos/editar.php?id=${idRecurso}`;
}

async function borrarRecursoAsync(id, localidad)
{
    const resultadoDeAdvertencia = await Swal.fire({
        title: "Borrar Recurso",
        text: localidad == "externo" ?
            "Todas las reservas relacionadas seran removidas." :
            "Esta accion no se puede deshacer.",
        icon: "info",

        showCancelButton: true,
        cancelButtonText: "Cancelar",
        confirmButtonText: "Borrar"
    });

    if(!resultadoDeAdvertencia.isConfirmed) return;

    let formData = new FormData();
    formData.append("id", id)
    let respuesta = await fetch('/backend/recursos/borrar.php', {method: "POST", body: formData});
    respuesta = await respuesta.json();
    
    if(respuesta.ok)
    {
        await Swal.fire({
            title: "Recurso Eliminado!",
            text: `El recurso ha sido eliminado exitosamente.`,
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

document.addEventListener('DOMContentLoaded', async e => {
    let formData = new FormData();
    let respuesta = await fetch('/backend/recursos/ver.php', {method: "POST", body: formData});
    respuesta = await respuesta.json();
    if(!respuesta.ok)
    {
        switch(respuesta.value)
        {
            case 'NO_HAY_PERIODOS':
                await Swal.fire({
                    title: "Periodos",
                    text: `No hay periodos registrados en el sistema, registrelos.`,
                    icon: "error"
                });
                location.reload(); //TODO: Ir a login o generador de periodos dependiendo del rol.
                break;
            default:
                Swal.fire({
                    title: "Error Desconocido",
                    text: `Un error desconocido ha ocurrido`,
                    icon: "error"
                });
        }
    }

    let recursosInternos = respuesta.value.recursos_internos;
    let recursosExternos = respuesta.value.recursos_externos;
    let todosLosEspacios = respuesta.value.espacios_por_id;

    recursosExternos.forEach(recurso => {
        uiAgregarDivRecursoExterno(recurso, todosLosEspacios);
    });

    recursosInternos.forEach(recurso => {
        uiAgregarDivRecursoInterno(recurso, todosLosEspacios);
    });
});

const domTemplateDivReservaEspacio = document.getElementById("template-div-reserva-espacio");
const domTemplateDivReservaRecurso = document.getElementById("template-div-reserva-recurso");
const domTemplateDivPeriodo = document.getElementById("template-div-periodo");
const domReservasContainer = document.getElementById("reservas-container");

let todosLosPeriodosPorId = {};
let todosLosPeriodos = [];

function uiCrearDivPeriodo(inicio, final, estaReservado)
{
    const domDivPeriodo = domTemplateDivPeriodo.content.firstElementChild.cloneNode(true);
    const domInicio = domDivPeriodo.querySelector("[name='inicio']");
    const domFinal = domDivPeriodo.querySelector("[name='final']");

    domInicio.innerText = inicio;
    domFinal.innerText = final;
    if(estaReservado) domDivPeriodo.classList.add("reservado");

    return domDivPeriodo;
}

/// RESERVA ESPACIOS ///

function uiCrearDivReservaEspacio(idReservaEspacio, espacio, idPeriodosDeReserva)
{
    const domDivReservaEspacio = domTemplateDivReservaEspacio.content.cloneNode(true);
    const domImage = domDivReservaEspacio.querySelector("[name='image']");
    const domEspacio = domDivReservaEspacio.querySelector("[name='espacio']");
    const domDivPeriodos = domDivReservaEspacio.querySelector("[name='div-periodos']");
    const domButtonBorrar = domDivReservaEspacio.querySelector("[name='button-borrar']");

    domEspacio.innerText = espacio;
    todosLosPeriodos.forEach(periodo => {
        const estaPeriodoEnReserva = idPeriodosDeReserva.includes(periodo.id_periodo);
        const entrada = periodo.entrada.substring(0, 5);
        const salida = periodo.salida.substring(0, 5);
        const domPeriodo = uiCrearDivPeriodo(entrada, salida, estaPeriodoEnReserva);
        domDivPeriodos.append(domPeriodo);
    });
    domButtonBorrar.onclick = () => borrarReservaEspacioAsync(idReservaEspacio);

    return domDivReservaEspacio;
}

function uiAgregarDivReservaEspacio(idReservaEspacio, espacio, idPeriodosDeReserva)
{
    const divReservaEspacio = uiCrearDivReservaEspacio(idReservaEspacio, espacio, idPeriodosDeReserva);
    domReservasContainer.append(divReservaEspacio);
}

async function borrarReservaEspacioAsync(idReservaEspacio)
{
    let formData = new FormData();
    formData.append("id_reserva_espacio", idReservaEspacio);
    let respuesta = await fetch('../../../backend/reservas/borrar_reserva_espacio.php', {method: 'POST', body: formData});
    respuesta = await respuesta.json();
    
    if(respuesta.ok)
    {
        await Swal.fire({
            title: "Reserva Eliminada",
            text: `La reserva ha sido eliminada exitosamente`,
            icon: "success"
        });
        await inicializarAsync();
    }
    else
    {
        switch(respuesta.value)
        {
            case "NECESITA_LOGIN":
                Swal.fire({
                    title: "Login Requerido",
                    text: `Necesitas iniciar sesion para reservar un espacio.`,
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

/// RESERVA RECURSOS ///

function uiCrearDivReservaRecurso(idReservaRecurso, tipoRecurso, cantidad, espacio, idPeriodosDeReserva)
{
    const domDivReservaRecurso = domTemplateDivReservaRecurso.content.cloneNode(true);
    const domImage = domDivReservaRecurso.querySelector("[name='image']");
    const domEspacio = domDivReservaRecurso.querySelector("[name='espacio']");
    const domTipo = domDivReservaRecurso.querySelector("[name='tipo']");
    const domCantidad = domDivReservaRecurso.querySelector("[name='cantidad']");
    const domDivPeriodos = domDivReservaRecurso.querySelector("[name='div-periodos']");
    const domButtonBorrar = domDivReservaRecurso.querySelector("[name='button-borrar']");

    domEspacio.innerText = espacio;
    domTipo.innerText = tipoRecurso;
    domCantidad.innerText = `Cantidad: ${cantidad}`;
    todosLosPeriodos.forEach(periodo => {
        const estaPeriodoEnReserva = idPeriodosDeReserva.includes(periodo.id_periodo);
        const entrada = periodo.entrada.substring(0, 5);
        const salida = periodo.salida.substring(0, 5);
        const domPeriodo = uiCrearDivPeriodo(entrada, salida, estaPeriodoEnReserva);
        domDivPeriodos.append(domPeriodo);
    });
    domButtonBorrar.onclick = () => borrarReservaRecursoAsync(idReservaRecurso);

    return domDivReservaRecurso;
}

function uiAgregarDivReservaRecurso(idReservaRecurso, tipoRecurso, cantidad, espacio, idPeriodosDeReserva)
{
    const divReservaRecurso = uiCrearDivReservaRecurso(idReservaRecurso, tipoRecurso, cantidad, espacio, idPeriodosDeReserva);
    domReservasContainer.append(divReservaRecurso);
}

async function borrarReservaRecursoAsync(idReservaRecurso)
{
    let formData = new FormData();
    formData.append("id_reserva_recurso", idReservaRecurso);
    let respuesta = await fetch('../../backend/reservas/borrar_reserva_recurso.php', {method: 'POST', body: formData});
    respuesta = await respuesta.json();

    if(respuesta.ok)
    {
        await Swal.fire({
            title: "Reserva Eliminada",
            text: `La reserva ha sido eliminada exitosamente`,
            icon: "success"
        });
        await inicializarAsync();
    }
    else
    {
        switch(respuesta.value)
        {
            case "NECESITA_LOGIN":
                Swal.fire({
                    title: "Login Requerido",
                    text: `Necesitas iniciar sesion para reservar un espacio.`,
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

//

async function inicializarAsync()
{
    let respuesta = await fetch('../../backend/reservas/my.php');
    respuesta = await respuesta.json();

    todosLosPeriodosPorId = respuesta.value.periodos_por_id;
    todosLosPeriodos = Object.values(todosLosPeriodosPorId);
    const reservasEspacios = respuesta.value.reservas_espacios;
    const reservasRecursos = respuesta.value.reservas_recursos;

    domReservasContainer.innerHTML = '';
    reservasEspacios.forEach(reservaEspacio => {
        let nombreEspacio = reservaEspacio.tipo_espacio;
        if(reservaEspacio.numero_espacio) nombreEspacio += ` ${reservaEspacio.numero_espacio}`;

        uiAgregarDivReservaEspacio(reservaEspacio.id_reserva, nombreEspacio, reservaEspacio.id_periodos);
    });

    reservasRecursos.forEach(reservaRecurso => {
        let nombreEspacio = reservaRecurso.tipo_espacio;
        if(nombreEspacio == undefined) nombreEspacio = "";

        if(reservaRecurso.numero_espacio) nombreEspacio += ` ${reservaRecurso.numero_espacio}`;

        uiAgregarDivReservaRecurso(reservaRecurso.id_reserva, reservaRecurso.tipo_recurso, reservaRecurso.cantidad_reservado, nombreEspacio, reservaRecurso.id_periodos);
    });

}

document.addEventListener('DOMContentLoaded', async e => {
    await inicializarAsync();

    if (domReservasContainer.innerText=="") {
        const h3 = document.createElement("h3");
        h3.innerText = "Actualmente no tiene ninguna reserva";
        domReservasContainer.appendChild(h3);
    } 
})
const domDivCheckboxesIntervalos = document.getElementById("checkboxes-intervalos");
const domTemplateCheckboxIntervalo = document.getElementById("template-checkbox-intervalo");
const domInputCantidadAReservar = document.getElementById("cantidad-a-reservar");
const domTipo = document.getElementById("tipo");
const domEspacio = document.getElementById("espacio");
const domForm = document.getElementById("form");

let intervalos = [];
let recurso = null;
let espacio = null;
let cantidadesPorNumeroIntervalo = [];

function uiCrearCheckboxIntervalo(intervalo, cantidadEnIntervalo)
{
    const domCheckboxIntervalo = domTemplateCheckboxIntervalo.content.cloneNode(true);
    const domText = domCheckboxIntervalo.querySelector('[name="text"]');
    const domCheckbox = domCheckboxIntervalo.querySelector("input");
    let cantidadAReservar = domInputCantidadAReservar.value;
    if(cantidadAReservar == '') cantidadAReservar = 1;

    // Asegurate que aunque el usuario ingrese un numero menor a 0, los horarios se muestren como si fueras a reservar 1.
    cantidadAReservar = Math.max(1, cantidadAReservar); 
    
    domText.innerText = `${intervalo.inicio} - ${intervalo.final}`;
    domCheckbox.value = intervalo.numero_intervalo;
    domCheckbox.disabled = cantidadAReservar > cantidadEnIntervalo;
    return domCheckboxIntervalo;
}

function uiAgregarCheckboxIntervalo(intervalo, cantidadEnIntervalo)
{
    const checkboxIntervalo = uiCrearCheckboxIntervalo(intervalo, cantidadEnIntervalo);
    domDivCheckboxesIntervalos.appendChild(checkboxIntervalo);
}

function uiEstablecerCheckboxesIntervalos()
{
    domDivCheckboxesIntervalos.innerHTML = '';
    intervalos.forEach(intervalo => {
        const cantidadEnIntervalo = cantidadesPorNumeroIntervalo[intervalo.numero_intervalo];
        uiAgregarCheckboxIntervalo(intervalo, cantidadEnIntervalo);
    });
}

domInputCantidadAReservar.addEventListener('input', async e => {
    uiEstablecerCheckboxesIntervalos();
});

document.addEventListener('DOMContentLoaded', async e => {
    const urlParams = new URLSearchParams(window.location.search);
    const idRecurso = urlParams.get("id");

    let respuesta = await fetch(`/backend/recursos/externos/reservar.php?id_recurso=${idRecurso}`);
    respuesta = await respuesta.json();

    intervalos = respuesta.value.intervalos;
    recurso = respuesta.value.recurso;
    espacio = recurso.espacio;
    cantidadesPorNumeroIntervalo = recurso.cantidades_por_numero_intervalo;
    uiEstablecerCheckboxesIntervalos();
    
    domTipo.innerText = recurso.tipo;
    if(espacio != null) domEspacio.innerText = `${espacio.tipo} ${espacio.numero ?? ''}`;
});

domForm.addEventListener('submit', async e => {
    e.preventDefault();
    const urlParams = new URLSearchParams(window.location.search);
    const idRecurso = urlParams.get("id");

    const formData = new FormData(domForm);
    formData.append("id_recurso", idRecurso);

    let respuesta = await fetch(`/backend/recursos/externos/reservar.php`, {method:"POST", body: formData});
    respuesta = await respuesta.json();

    if(respuesta.ok)
    {
        await Swal.fire({
            title: "Reserva Creada",
            text: `La reserva ha sido creada exitosamente`,
            icon: "success"
        });
        location.reload();
    }
    else
    {
        switch(respuesta.value)
        {
            case "HORAS_NO_ESPECIFICADAS":
                Swal.fire({
                    title: "Horas No Especificadas",
                    text: `No has especificado las horas en las cuales reservara el recurso.`,
                    icon: "error"
                });
                break;
            case "CANTIDAD_NO_ESPECIFICADA":
                Swal.fire({
                    title: "Cantidad No Especificada",
                    text: `Debe especificar la cantidad a reservar.`,
                    icon: "error"
                });
                break;
            case "NECESITA_LOGIN":
                Swal.fire({
                    title: "Login Requerido",
                    text: `Necesitas iniciar sesion para reservar un recurso.`,
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
})
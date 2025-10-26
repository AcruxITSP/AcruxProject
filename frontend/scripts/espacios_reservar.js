const domDivCheckboxesIntervalos = document.getElementById("checkboxes-intervalos");
const domTemplateCheckboxIntervalo = document.getElementById("template-checkbox-intervalo");
const domEspacio = document.getElementById("espacio");
const domForm = document.getElementById("form-reservar-espacio");

let espacio = null;
let intervalos = [];
let estado_por_numero_periodos = [];

function uiCrearCheckboxIntervalo(intervalo, estado)
{
    const domCheckboxIntervalo = domTemplateCheckboxIntervalo.content.cloneNode(true);
    const domText = domCheckboxIntervalo.querySelector('[name="text"]');
    const domCheckbox = domCheckboxIntervalo.querySelector("input");

    const estaLibre = estado.estado == 'libre' || estado.estado == 'ausente';
    let reservante = null;
    let grupo = null;
    if(!estaLibre)
    {
        reservante = estado.reservante;
        grupo = reservante.grupo; // puede ser undefined
    }
    let stringReservante = "";
    
    if(!estaLibre)
    {
        stringReservante = `- ${reservante.nombre} ${reservante.apellido}`;
        if(grupo) stringReservante += ` - ${reservante.grupo}`;
    }

    domText.innerText = `${estado.estado} - ${intervalo.entrada.substring(0, 5)} - ${intervalo.salida.substring(0, 5)}${stringReservante}`;

    domCheckbox.value = intervalo.numero;
    domCheckbox.disabled = !estaLibre;
    return domCheckboxIntervalo;
}

function uiAgregarCheckboxIntervalo(intervalo, estado)
{
    const checkboxIntervalo = uiCrearCheckboxIntervalo(intervalo, estado);
    domDivCheckboxesIntervalos.appendChild(checkboxIntervalo);
}

function uiEstablecerCheckboxesIntervalos()
{
    domDivCheckboxesIntervalos.innerHTML = '';
    intervalos.forEach(intervalo => {
        const estado = estado_por_numero_periodos[intervalo.numero];
        uiAgregarCheckboxIntervalo(intervalo, estado);
    });
}

document.addEventListener('DOMContentLoaded', async e => {
    const urlParams = new URLSearchParams(window.location.search);
    const idEspacio = urlParams.get("id");

    let respuesta = await fetch(`/backend/espacios/reservar.php?id_espacio=${idEspacio}`);
    respuesta = await respuesta.json();

    espacio = respuesta.value.espacio;
    intervalos = respuesta.value.periodos;
    estado_por_numero_periodos = respuesta.value.estado_por_numero_periodos;
    uiEstablecerCheckboxesIntervalos();
    
    domEspacio.innerText = `${espacio.tipo} ${espacio.numero ?? ''}`;
    if(espacio != null) domEspacio.innerText = `${espacio.tipo} ${espacio.numero ?? ''}`;
});

domForm.addEventListener('submit', async e => {
    e.preventDefault();
    const urlParams = new URLSearchParams(window.location.search);
    const idEspacio = urlParams.get("id");

    const formData = new FormData(domForm);
    formData.append("id_espacio", idEspacio);

    let respuesta = await fetch(`/backend/espacios/reservar.php`, {method:"POST", body: formData});
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
                    text: `No has especificado las horas en las cuales reservara el espacio.`,
                    icon: "error"
                });
                break;
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
})
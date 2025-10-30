const domTemplateDivPorTipo = document.getElementById("template-div-por-tipo");
const domTemplateTarjeta = document.getElementById("template-tarjeta");
const domDivPorTipoContainer = document.getElementById("main-ver-salones");

const divsPorTipoEspacio = {};

function uiCrearDivPorTipo(tipo)
{
    // Si ya existe uno, devuelvelo para evitar crear otro
    if(divsPorTipoEspacio[tipo]) return divsPorTipoEspacio[tipo];

    const domDivPorTipo = domTemplateDivPorTipo.content.cloneNode(true).children[0];
    const domTipo = domDivPorTipo.querySelector("[name='tipo']");
    domTipo.innerText = tipo;

    // Guardamos el div para este tipo de espacio y lo retornamos
    divsPorTipoEspacio[tipo] = domDivPorTipo;
    return domDivPorTipo;
}

function uiAgregarDivPorTipo(tipo)
{
    // Si ya existe uno, devuelvelo para evitar crear otro
    if(divsPorTipoEspacio[tipo]) return divsPorTipoEspacio[tipo];

    const domDivPorTipo = uiCrearDivPorTipo(tipo);
    domDivPorTipoContainer.appendChild(domDivPorTipo);
    return domDivPorTipo;
}

function uiCrearTarjeta(espacio)
{
    const domDivTarjeta = domTemplateTarjeta.content.cloneNode(true).children[0];
    const domNombre = domDivTarjeta.querySelector("[name='nombre']");
    const domEstado = domDivTarjeta.querySelector("[name='estado']");
    const domBorrar = domDivTarjeta.querySelector("[name='borrar']");
    const domEditar = domDivTarjeta.querySelector("[name='editar']");

    domNombre.innerText = `${espacio.tipo} ${espacio.numero ?? ''}`;

    const disponibilidad = espacio.disponibilidad;
    domEstado.innerText = disponibilidad.estado; // Libre, ausente, etc...

    if(disponibilidad.reservante)
    {
        const reservante = disponibilidad.reservante;
        domEstado.innerText += ` - ${reservante.nombre} ${reservante.apellido}`;

        if(reservante.grupo)
        {
            domEstado.innerText += ` - ${reservante.grupo}`;
        }
    }

    return domDivTarjeta;
}

function uiAgregarTarjeta(espacio)
{
    const domDivTarjeta = uiCrearTarjeta(espacio);
    const domDivPorTipo = uiAgregarDivPorTipo(espacio.tipo);
    const domContenedorSalones = domDivPorTipo.querySelector("[name='contenedor-salones']");
    domContenedorSalones.appendChild(domDivTarjeta);
}

async function inicializar()
{
    let respuesta = await fetch("../../backend/espacios/ver.php");
    respuesta = await respuesta.json();

    const espacios = respuesta.value;
    espacios.forEach(espacio => {
        uiAgregarTarjeta(espacio);
    });
}

inicializar();
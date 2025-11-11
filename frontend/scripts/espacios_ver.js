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
    const domCapacidad = domDivTarjeta.querySelector("[name='capacidad'] span");
    const domUbicacion = domDivTarjeta.querySelector("[name='ubicacion'] span");
    const domBorrar = domDivTarjeta.querySelector("[name='borrar']");
    const domEditar = domDivTarjeta.querySelector("[name='editar']");

    domCapacidad.innerText = `${espacio.capacidad}`;
    domUbicacion.innerText = `${espacio.ubicacion}`;

    domNombre.innerText = `${espacio.tipo} ${espacio.numero ?? ''}`;
    domBorrar.onclick = () => borrarEspacioAsync(espacio.id_espacio);
    domEditar.onclick = () => editarEspacio(espacio.id_espacio);

    const disponibilidad = espacio.disponibilidad;
    domEstado.innerText = disponibilidad.estado; // Libre, ausente, etc...

    if(disponibilidad.reservante)
    {
        const reservante = disponibilidad.reservante;
        domEstado.innerText += ` - ${reservante.nombre} ${reservante.apellido}`;
        domEstado.classList.add("ocupado");

        if(reservante.grupo)
        {
            domEstado.innerText += ` - ${reservante.grupo}`;
        }
    } else if (disponibilidad.estado.toLowerCase() == "libre"){
        domEstado.innerText = "Libre";
        domEstado.classList.add("libre");
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

function editarEspacio(idEspacio)
{
    location.href = `editar.php?id=${idEspacio}`;
}

async function borrarEspacioAsync(idEspacio)
{
    const resultadoDeAdvertencia = await Swal.fire({
        title: "Borrar Espacio",
        text: "",
        icon: "info",

        showCancelButton: true,
        cancelButtonText: "Cancelar",
        confirmButtonText: "Borrar"
    });

    if(!resultadoDeAdvertencia.isConfirmed) return;

    let formData = new FormData();
    formData.append("id", idEspacio)
    let respuesta = await fetch('../../backend/espacios/borrar.php', {method: "POST", body: formData});
    respuesta = await respuesta.json();
    
    if(respuesta.ok)
    {
        await Swal.fire({
            title: "Espacio Eliminado!",
            text: `El espacio ha sido eliminado exitosamente.`,
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
                    text: `Necesitas iniciar sesion para eliminar un espacio.`,
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
    let respuesta = await fetch("../../backend/espacios/ver.php");
    respuesta = await respuesta.json();

    const espacios = respuesta.value;
    espacios.forEach(espacio => {
        uiAgregarTarjeta(espacio);
    });
}

inicializar();
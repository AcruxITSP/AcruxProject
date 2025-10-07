const reservaRegistrarForm = document.getElementById("reserva-registrar-form");
const aulaSelect = document.getElementById("id_aula");
const horaInicioSelect = document.getElementById("id_hora_inicio");
const horaFinalSelect = document.getElementById("id_hora_final");

reservaRegistrarForm.addEventListener('submit', async event => {
    event.preventDefault();
    const formData = new FormData(event.target);
    let response = await Reservas.registerWith(formData);
    response = await response.json();

    if(!response.ok)
    {
            Swal.fire({
                icon: "error",
                title: "Registrar",
                text: "Ha ocurrido un error.",
            });
        return;
    }
    else
    {
        Swal.fire({
            title: "Registro",
            text: "La reserva se registro correctamente.",
            icon: "success"
        });
    }
});

function crearOpcion(id, valor)
{
    let option = document.getElementById("template_option").content.cloneNode(true);
    option.querySelector("option").value = id;
    option.querySelector("option").innerText = valor;
    return option;
}

async function cargarAulas()
{
    let response = await Aulas.get();
    response = await response.json();
    let aulas = response.value;
    aulas.forEach(aula => {
        let option = crearOpcion(aula.Id_aula, aula.Codigo);
        aulaSelect.appendChild(option);
    });
}

async function cargarHorasIniciales()
{
    let response = await Horas.getToday();
    response = await response.json();
    let horas = response.value;
    horas.forEach(hora => {
        let valor = `${hora.Nombre} - ${hora.Entrada}`;
        let option = crearOpcion(hora.Id_hora, valor);
        horaInicioSelect.appendChild(option);
    });
}

async function cargarHorasFinales()
{
    let response = await Horas.getToday();
    response = await response.json();
    let horas = response.value;
    horas.forEach(hora => {
        let valor = `${hora.Nombre} - ${hora.Salida}`;
        let option = crearOpcion(hora.Id_hora, valor);
        horaFinalSelect.appendChild(option);
    });
}
cargarAulas();
cargarHorasIniciales();
cargarHorasFinales();
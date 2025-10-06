const ingresarAulasForm = document.getElementById("ingresarAulas-Form");
const registrosTable = document.getElementById("table-registros");

async function getAulas()
{
    let response = await Aulas.get();
    response = await response.json();

    if(!response.ok)
    {
            Swal.fire({
                icon: "error",
                title: "Registrar",
                text: "Ha ocurrido un error.",
            });
        return [];
    }
    else
    {
        return response.value;
    }
}

function createRegistroTemplate(aula)
{
    const template = document.getElementById("template-registro").content.cloneNode(true);
    template.querySelector("[name='codigo']").innerText = aula.Codigo;
    template.querySelector("[name='piso']").innerText = aula.Piso;
    template.querySelector("[name='proposito']").innerText = aula.Proposito;
    template.querySelector("[name='capacidad']").innerText = aula.CantidadSillas;
    template.querySelector("[name='editar']").href = "editar.php?id=" + aula.Id_aula;
    return template;
}

document.addEventListener('DOMContentLoaded', async e => {
    const aulas = await getAulas();
    aulas.forEach(aula => {
        let registro = createRegistroTemplate(aula);
        registrosTable.appendChild(registro);
    });
});
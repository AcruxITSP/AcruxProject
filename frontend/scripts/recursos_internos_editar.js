const domForm = document.getElementById("form-editar-recurso-interno");
const domTipo = domForm.querySelector("[name='tipo']");

const domDatalistTipoRecursos = document.getElementById("datalist-tipo-recursos");
const domIListEspaciosYCantidades = document.getElementById("ilist-espacios-y-cantidades");

let espacios = [];

const urlParams = new URLSearchParams(window.location.search); //trae los parametros de la url
const id = urlParams.get("id"); // agarra el id de la url

// Codigo que se encarga de generar una lista de inputs que contienen:
// Select para el espacio
// Number para la cantidad de este recurso para el espacio seleccionado
function IListEspacioCantidadSuplier(name)  // El nombre es requerido por la "firma" de la funcion, pero no lo uso aqui
{
    let selectEspacioHtml = `<select name="id_espacios[]">`;
    espacios.forEach(espacio => {
        selectEspacioHtml += `<option value="${espacio.id_espacio}">${espacio.tipo} ${espacio.numero ?? ''}</option>`;
    });
    selectEspacioHtml += `</select>`

    let inputCantidadHtml = `<input type="number" name="cantidades[]" placeholder="cantidad" min="1" required>`;

    return createElementFromString(
        `
        <div class="espacio-cantidad-div">
            ${selectEspacioHtml}
            ${inputCantidadHtml}
        </div>
        `
    );
}

async function inicializar()
{
    // trae los tipos y espacios (reutilizamos el GET de crear para el de editar)
    let respuesta = await fetch('../../../backend/recursos/crear.php', {method:"GET"});
    respuesta = await respuesta.json();
    const tiposRecursos = respuesta.value.tipos_recursos;
    espacios = respuesta.value.espacios;
    attachSmartDatalist(domTipo, tiposRecursos);

    // Codigo que se encarga de generar una lista de inputs que contienen:
    // Select para el espacio
    // Number para la cantidad de este recurso para el espacio seleccionado
    generateIList(domIListEspaciosYCantidades);

    // Traemos los datos actuales del recurso
    respuesta = await fetch(`../../../backend/recursos/editar_interno.php?id_recurso_base=${id}`);
    respuesta = await respuesta.json();
    const estadoActualRecurso = respuesta.value;
    domTipo.value = estadoActualRecurso.tipo;

    // Agregar los espacios en el formulario
    const addEspacioButton = domIListEspaciosYCantidades.querySelector('button');
    const inputContainer = domIListEspaciosYCantidades.querySelector('[name="input-container"]');
    estadoActualRecurso.espacios.forEach(espacio => {
        addEspacioButton.click();
        const inputEspacioAgregado = inputContainer.lastChild;
        const selectEspacio = inputEspacioAgregado.querySelector('[name="id_espacios[]"]');
        const inputCantidades = inputEspacioAgregado.querySelector('[name="cantidades[]"]');
        selectEspacio.value = espacio.id_espacio;
        inputCantidades.value = espacio.cantidad;
    });
    
}

document.addEventListener('DOMContentLoaded', async e => {
    await inicializar();
});

domForm.addEventListener('submit', async e => {
    e.preventDefault();
    const formData = new FormData(domForm);
    const tipo = domTipo.value;

    formData.append("id_recurso_base", id);

    let respuesta = await fetch('../../../backend/recursos/editar_interno.php', {method:"POST", body: formData});
    respuesta = await respuesta.json();

    if(respuesta.ok)
    {
        Swal.fire({
            title: "Recurso Creado!",
            text: `El recurso ${tipo} ha sido creado exitosamente.`,
            icon: "success"
        });
    }
    else
    {
        switch(respuesta.value)
        {
            case "TIPO_EN_USO":
                Swal.fire({
                    title: "Tipo de Recurso en Uso",
                    text: `Ya hay recursos del tipo ${tipo} registrados.`,
                    icon: "error"
                });
                break;
            case "TIPO_QUIZAS_EN_USO":
                Swal.fire({
                    title: "Error de coneccion",
                    text: `No se pudo revisar si ya hay recursos del tipo ${tipo} registrados.`,
                    icon: "error"
                });
                break;
            case "ESPACIO_REQUERIDO":
                Swal.fire({
                    title: "Espacio Requerido",
                    text: `Es necesario especificar el espacio al que pertenece el recurso interno.`,
                    icon: "error"
                });
                break;
            case "LOCALIDAD_NO_ESPECIFICADA":
            case "LOCALIDAD_INVALIDA":
                Swal.fire({
                    title: "Localidad Invalida",
                    text: `Se debe especificar si el recurso es interno o externo.`,
                    icon: "error"
                });
                break;
            case "TIPO_NO_ESPECIFICADO":
                Swal.fire({
                    title: "Tipo No Especificado",
                    text: `Se debe especificar el tipo de recurso.`,
                    icon: "error"
                });
                break;
            case "CANTIDAD_NO_ESPECIFICADA":
                Swal.fire({
                    title: "Cantidad No Especificada",
                    text: `Se debe especificar la cantidad del recurso a registrar.`,
                    icon: "error"
                });
                break;
            case "ESPACIO_NO_ESPECIFICADO":
                Swal.fire({
                    title: "Espacio No Especificado",
                    text: `Se debe especificar el espacio al que pertenece el recurso interno.`,
                    icon: "error"
                });
                break;
            case "CANTIDAD_INVALIDA":
                Swal.fire({
                    title: "Cantidad Invalida",
                    text: `Debe especificar un valor mayor a cero.`,
                    icon: "error"
                });
                break;
            case "ESPACIO_NO_EXISTE":
                Swal.fire({
                    title: "Espacio Inexistente",
                    text: `El espacio seleccionado no existe.`,
                    icon: "error"
                });
                break;
            case "ESPACIOS_NO_ESPECIFICADOS":
                Swal.fire({
                    title: "Espacios No Especificados",
                    text: `Debe especificar los espacios a los cuales pertenece el recurso a crear.`,
                    icon: "error"
                });
                break;
            case "CANTIDADES_NO_ESPECIFICADAS":
                Swal.fire({
                    title: "Cantidades No Especificadas",
                    text: `Debe especificar la cantidad del recurso por cada espacio seleccionado.`,
                    icon: "error"
                });
                break;
            case "ESPACIO_YA_ESPECIFICADO":
                Swal.fire({
                    title: "Espacio Repetido",
                    text: `Se ha ingresado un espacio mas de 2 veces durante el registro de este recurso.`,
                    icon: "error"
                });
                break;
            case "NECESITA_LOGIN":
                Swal.fire({
                    title: "Login Requerido",
                    text: `Necesitas iniciar sesion para crear un recurso.`,
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

    console.log(respuesta);
});
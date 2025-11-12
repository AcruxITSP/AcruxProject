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
        await Swal.fire({
            title: "Recurso Editado!",
            text: `El recurso ${tipo} ha sido editado exitosamente.`,
            icon: "success"
        });
        history.back();
    }
    else
    {
        switch(respuesta.value)
        {
            // --- ERRORES COMUNES ---
            case "NECESITA_LOGIN":
                Swal.fire({
                    title: "Login Requerido",
                    text: "Necesitas iniciar sesión para acceder a esta función.",
                    icon: "error"
                });
                break;

            case "FALTA_ID_RECURSO":
                Swal.fire({
                    title: "Falta información",
                    text: "No se especificó el recurso a modificar o consultar.",
                    icon: "error"
                });
                break;

            // --- ERRORES POST ---
            case "FALTA_TIPO":
                Swal.fire({
                    title: "Tipo no especificado",
                    text: "Debes indicar el tipo del recurso.",
                    icon: "error"
                });
                break;

            case "ESPACIO_YA_ESPECIFICADO":
                Swal.fire({
                    title: "Espacio ya especificado",
                    text: "Has indicado mas de una vez un mismo espacio.",
                    icon: "error"
                });
                break;

            case "FALTA_ID_ESPACIOS":
                Swal.fire({
                    title: "Espacios no especificados",
                    text: "Debes seleccionar al menos un espacio asociado al recurso.",
                    icon: "error"
                });
                break;

            case "FALTA_CANTIDADES_EN_ESPACIOS":
                Swal.fire({
                    title: "Cantidades faltantes",
                    text: "Debes indicar la cantidad correspondiente para cada espacio.",
                    icon: "error"
                });
                break;

            case "NOMBRE_VACIO":
                Swal.fire({
                    title: "Nombre vacío",
                    text: "El nombre o tipo del recurso no puede estar vacío.",
                    icon: "error"
                });
                break;

            case "TIPO_RECURSO_DUPLICADO":
                Swal.fire({
                    title: "Tipo duplicado",
                    text: "Ya existe un recurso con este tipo. Elige otro nombre.",
                    icon: "error"
                });
                break;

            case "ESPACIO_NO_EXISTE":
                Swal.fire({
                    title: "Espacio inexistente",
                    text: "Alguno de los espacios seleccionados no existe o fue eliminado.",
                    icon: "error"
                });
                break;

            // --- ERRORES GET ---
            case "RECURSO_NO_EXISTE":
                Swal.fire({
                    title: "Recurso no encontrado",
                    text: "El recurso solicitado no existe en el sistema.",
                    icon: "error"
                });
                break;

            case "RECURSO_INTERNO_NO_EXISTE":
                Swal.fire({
                    title: "Error interno",
                    text: "El recurso interno asociado no fue encontrado.",
                    icon: "error"
                });
                break;

            // --- ERROR GENÉRICO ---
            default:
                Swal.fire({
                    title: "Error desconocido",
                    text: "Ha ocurrido un error inesperado. Intenta nuevamente.",
                    icon: "error"
                });
                break;
        }
    }

    console.log(respuesta);
});
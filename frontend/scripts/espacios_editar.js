const form = document.getElementById("formulario-editar-espacio");
const domTipo = document.getElementById("tipo");
const domNumero = document.getElementById("numero");
const domCapacidad = document.getElementById("capacidad");
const domUbicacion = document.getElementById("ubicacion");

const urlParams = new URLSearchParams(window.location.search); //trae los parametros de la url
const id = urlParams.get("id"); // agarra el id de la url

form.addEventListener("submit", async e => {
    e.preventDefault();
    const formData = new FormData(form);
    formData.append("id", id);

    let respuesta = await fetch(`../../backend/espacios/editar.php`, { method: "POST", body: formData });
    respuesta = await respuesta.json();

    if (respuesta.ok) {
        await Swal.fire({
            title: "Espacio Actualizado",
            text: `La información del espacio fue actualizada exitosamente`,
            icon: "success"
        });
    }
    else {
        switch (respuesta.value) {
            case "NECESITA_LOGIN":
                Swal.fire({
                    title: "Login Requerido",
                    text: `Necesitas iniciar sesión para realizar esta acción.`,
                    icon: "error"
                });
                break;

            case "ID_ESPACIO_INVALIDA":
                Swal.fire({
                    title: "Espacio No Encontrado",
                    text: `No se ha seleccionado ningún espacio o la id no existe.`,
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
});

async function inicializar()
{
    let respuesta = await fetch(`../../backend/espacios/editar.php?id=${id}`);
    respuesta = await respuesta.json();
    const value = respuesta.value;

    domTipo.value = value.tipo;
    domNumero.value = value.numero;
    domCapacidad.value = value.capacidad;
    domUbicacion.value = value.ubicacion;
}

inicializar();
const form = document.getElementById("form-crear-espacio");

form.addEventListener("submit", async e => {
    e.preventDefault();
    const formData = new FormData(form);

    let respuesta = await fetch(`../../backend/espacios/crear.php`, {method:"POST", body: formData});
    respuesta = await respuesta.json();

    if(respuesta.ok)
    {
        await Swal.fire({
            title: "Espacio Creado",
            text: `El espacio ha sido creada exitosamente`,
            icon: "success"
        });
    }
    else
    {
        switch(respuesta.value)
        {
            case "NECESITA_LOGIN":
                Swal.fire({
                    title: "Login Requerido",
                    text: `Necesitas iniciar sesion para crear un espacio.`,
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
const ingresarAulasForm = document.getElementById("ingresarAulas-Form");

ingresarAulasForm.addEventListener('submit', async event => {
    event.preventDefault();
    const formData = new FormData(event.target);
    let response = await Aulas.registerWith(formData);
    response = await response.json();

    if(!response.ok)
    {
        if(response.value.type == "AULA_DUPLICATE_CODIGO")
            Swal.fire({
                icon: "error",
                title: "Registrar",
                text: `Ya existe el aula con el codigo ${response.value.data.value}.`,
            });
        else
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
            text: "El aula se registro correctamente.",
            icon: "success"
        });
    }

    response = await response;

});
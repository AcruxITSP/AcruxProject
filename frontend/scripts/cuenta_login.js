const formLogin = document.getElementById("form-login");

formLogin.addEventListener('submit', async e => {
    e.preventDefault();
    const formData = new FormData(formLogin);
    let respuesta = await fetch('/backend/cuenta/login.php', {method:"POST", body: formData});
    respuesta = await respuesta.json();

    let session = respuesta.value.session;

    if(respuesta.ok)
    {
        Swal.fire({
            title: "Login Exitoso",
            text: `Has iniciado sesion como ${session.username} (${session.rol}).`,
            icon: "success",
        }).then(result => 
        {
            let a = document.createElement(`a`)
            a.href = "../general/index.php";
            a.click();
        });
    }
    else
    {
        switch(respuesta.value)
        {
            case "CI_NO_INGRESADA":
                Swal.fire({
                    title: "CI No Ingresada",
                    text: `Debe ingresar su cedula de identidad.`,
                    icon: "error"
                });
                break;
            case "CI_NO_INGRESADA":
                Swal.fire({
                    title: "Contrasena No Ingresada",
                    text: `Debe ingresar su contrasena.`,
                    icon: "error"
                });
                break;
            case "USUARIO_NO_ENCONTRADO":
                Swal.fire({
                    title: "Usuario No Encontrado",
                    text: `No se encontro el usuario.`,
                    icon: "error"
                });
                break;
            case "CONTRASENA_INVALIDA":
                Swal.fire({
                    title: "Contrasena Invalida",
                    text: `La contrasena no es correcta.`,
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
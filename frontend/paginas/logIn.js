const loginForm = document.getElementById("logInForm");

loginForm.addEventListener('submit', async event => {
    event.preventDefault();
    const formData = new FormData(event.target);
    let response = await Users.loginWith(formData);
    response = await response.json();

    if(!response.ok)
    {
        if(response.value.type == "PERSONA_NOT_FOUND")
        Swal.fire({
            icon: "error",
            title: "Login",
            text: "No se encontro el usuario.",
        });

        if(response.value == "LOGIN_INVALID_PASSWORD")
        Swal.fire({
            icon: "error",
            title: "Login",
            text: "La contrasena es incorrecta.",
        });
        return;
    }
    else
    {
        location.replace("index.php");
    }
});
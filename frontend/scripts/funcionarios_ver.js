const domTemplateTarjetaFuncionario = document.getElementById("tpl-tarjeta-funcionario");
const domDivContenedorFuncionarios = document.querySelector(".contenedor-funcionarios");

// Objeto de usuario de ejemplo (solo se ncesita el nombre, y la imagen si es que la implementamos)
const jsonStringUsuario = '{"ci": "44444444", "nombre": "Juan", "apellido": "Hernandez", "contrasena": "admin", "email": "juancarlos@gmail.com", "cargo": "Docente"}';
const usuario = JSON.parse(jsonStringUsuario);

function uiCrearTarjetaFuncionario(usuario){
    const domDivTarjeta = domTemplateTarjetaFuncionario.content.cloneNode(true).children[0];

    const domNombre = domDivTarjeta.querySelector("[name='nombre-funcionario']");
    const domCargo = domDivTarjeta.querySelector("[name='cargo']");
    const domCI = domDivTarjeta.querySelector("[name='CI']");
    const domEMail = domDivTarjeta.querySelector("[name='EMail']");

    const domEditar = domDivTarjeta.querySelector("[name='editar']");
    const domBorrar = domDivTarjeta.querySelector("[name='borrar']");

    domNombre.innerText = `${usuario.nombre} ${usuario.apellido}`;
    domCargo.innerText = `${usuario.cargo}`;
    if(domCI) domCI.innerText = `Cédula: ${usuario.ci}`;
    if(domEMail) domEMail.innerText = `E-Mail: ${usuario.email}`;

    if(domEditar) domEditar.onclick = () => {
        if(usuario.cargo == "Docente") editarDocente(usuario.id_docente);
        else editarAdscripto(usuario.id_adscripto);
    };
    
    domDivContenedorFuncionarios.appendChild(domDivTarjeta);
}

function editarDocente(idDocente)
{
    location.href = `editar_docente.php?id=${idDocente}`;
}

uiCrearTarjetaFuncionario(usuario);
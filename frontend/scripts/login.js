const domDni = document.getElementById("DNI");
const domPassword = document.getElementById("password");
const domForm = document.getElementById("logInForm");

const passwordMinLen = 10;

// Regexs
const regexDni = RegExp("^[0-9]{8}$");
const regexPasswordLower = RegExp("[a-z]");
const regexPasswordUpper = RegExp("[A-Z]");
const regexPasswordNumber = RegExp("[0-9]");

// Muestra el mensaje de error.
function hideError()
{
	// Nada
}

// Muestra un mensaje de error conteniendo `msg`
function showError(msg)
{
	// temporal
	alert(msg);
}

// Valida un valor `dni` segun el criterio de un DNI.
// En caso de problemas, muestra un mensaje de error.
// Retorna: Boolean, indicando si la validacion fue exitosa.
function validateDni(dni)
{
	if(!regexDni.test(dni))
	{
		showError("El DNI solo debe contener numeros.");
		return false;
	}
	return true;
}

// Valida un valor `password` segun el criterio de una contrasena.
// En caso de problemas, muestra un mensaje de error.
// Retorna: Boolean, indicando si la validacion fue exitosa.
function validatePassword(password)
{
	if(password == undefined || password == null)
	{
		showError("Debe ingresar una contrasena.");
		return false;
	}

	if(password.length < passwordMinLen)
	{
		showError(`La contrasena debe contener al menos ${passwordMinLen} caracteres.`);
		return false;
	}

	if(!regexPasswordLower.test(password))
	{
		showError("La contrasena tiene que tener minisculas.");
		return false;
	}
	if(!regexPasswordUpper.test(password))
	{
		showError("La contrasena tiene que tener mayusculas.");
		return false;
	}
	if(!regexPasswordNumber.test(password))
	{
		showError("La contrasena tiene que tener digitos.");
		return false;
	}

	return true;
}


domForm.addEventListener("submit", (e) => {
	let dni = domDni.value;
	let password = domPassword.value;

	e.preventDefault();
	if(!validateDni(dni)) return;
	if(!validatePassword(password)) return;

	console.log("si, andubo");

	domForm.submit();
});

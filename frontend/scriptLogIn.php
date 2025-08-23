<?php
/* Aca comienza el codigo*/
include 'globalFunctions.php';

$conn = iniciarConexion();

// Toma el valor de los campos en el formulario. NO utiliza el "id" del input, sino su atributo de "name"
$DNI = $_POST["DNI"];
$password = $_POST["password"];

//Buscar en la tabla Funcionarios

// La funcion "prepare()" permite insertar "variables" en una query de SQL
// Esto para mejorar la seguridad, para prevenir injection SQL
// Estas variables se representan con "?"
$query = $conn->prepare("SELECT * FROM Funcionario WHERE DNI = ? AND contrasena = ? ");

// Para asignarle los valores, se usa la funcion "bind_param()"
// Primero se indica el tipo de dato de cada variable, en este caso usamos "ss", ya que ambas son Strings
// Luego, se indica la variable de la que se va a tomar el valor.
// Nota: Todo se debe separar con comas (,) y debe estar puesto en orden (de izquierda a derecha)
$query->bind_param("ss", $DNI, $password);

$query->execute(); // Ejecuta la consulta

$registroFun = $query->get_result(); // El resultado de la consulta se guarda en la variable "$registroFun"

if ($registroFun->num_rows == 1){ // Si la consulta no devuelve un registro, se envia un mensaje de error
  $row = $registroFun->fetch_assoc();
  header("Location: " . "index.php"); // Redirige a la pagina index
}

//Buscar en tabla Estudiante

$query = $conn->prepare("SELECT * FROM Estudiante WHERE DNI = ? AND contrasena = ? ");
$query->bind_param("ss", $DNI, $password);
$query->execute(); // Ejecuta la consulta

$registroEs = $query->get_result(); // El resultado de la consulta se guarda en la variable "$registroFun"

if ($registroEs->num_rows == 1){ // Si la consulta no devuelve un registro, se envia un mensaje de error
  $row = $registroEs->fetch_assoc();
  
  //session permite guardar la informacion del usuario en una variable global, 
  //la cual esta almacenada en el servidor
  session_start();
  // Guardar el username, compuesto por el nombre y apellido de la persona
  $_SESSION["username"] = $row["Nombre"] . " " . $row['Apellido'];
  
  header("Location: " . "index.php"); // Redirige a la pagina index
}

echo ("ERROR: Contraseña o usuario incorrecto");

$conn->close(); // Cierra la conexion con la base de datos
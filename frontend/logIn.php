<?php
// Guardar la informacion de la base de datos en distintas variables (no necesariamente deben llamarse asi)
$servername = "localhost";
$username = "root"; // Nombre de usuario por defecto en phpMyAdmin
$password = ""; // Contrasena por defecto
$dbname = "db_acrux";

// Crear una conexion con la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Revisar si no hubo algun error en la conexion
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

/* Aca comienza el codigo*/

// Toma el valor de los campos en el formulario. NO utiliza el "id" del input, sino su atributo de "name"
$DNI = $_POST["DNI"];
$password = $_POST["password"];

// La funcion "prepare()" permite insertar "variables" en una query de SQL
// Esto para mejorar la seguridad, para prevenir injection SQL
// Estas variables se representan con "?"
$query = $conn->prepare("SELECT * FROM Estudiante WHERE DNI = ? AND contrasena = ? ");

// Para asignarle los valores, se usa la funcion "bind_param()"
// Primero se indica el tipo de dato de cada variable, en este caso usamos "ss", ya que ambas son Strings
// Luego, se indica la variable de la que se va a tomar el valor.
// Nota: Todo se debe separar con comas (,) y debe estar puesto en orden (de izquierda a derecha)
$query->bind_param("ss", $DNI, $password);

$query->execute(); // Ejecuta la consulta

$registro = $query->get_result(); // El resultado de la consulta se guarda en la variable "$registro"

if ($registro->num_rows == 0){ // Si la consulta no devuelve un registro, se envia un mensaje de error
  echo "No hubo coincidencias";
} else {
  // La funcion "fetch_assoc()" permite seleccionar el primer registro (linea) del resultado
  // Nota: Cada vez que se usa, se mueve al siguiente registro. Si no encuentra registros, devuelve un error 
  $row = $registro->fetch_assoc();

  header("Location: " . "index.php"); // Redirige a la pagina index
}

$conn->close(); // Cierra la conexion con la base de datos
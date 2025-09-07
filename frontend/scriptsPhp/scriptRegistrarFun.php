<?php
// Aca comienza el codigo
require 'globalFunctions.php';
$conn = iniciarConexion();

$DNI = $_POST["DNI"];
$nombre = $_POST["nombre"];
$apellido = $_POST["apellido"];
$email = $_POST["email"];
$contrasena = $_POST["contrasena"];

if (empty($email)){
    $email == NULL;
}

if (empty($contrasena)) {
    $contrasena = $DNI;
}

if (buscarRegistro("Funcionario", "DNI", $DNI) == true) {
    echo "ERROR: Ya hay un funcionario registrado con el DNI: $DNI <br><br>";
    echo "¿Desea modificarlo?";

    /*
        if respuesta == si {
            cambiar a pagina de modificacion de funcionarios
        } else {
            salir  
        }
    */

    exit("<br><br>Operacion cancelada");
}

$query = $conn->prepare("INSERT INTO Funcionario (nombre, apellido, DNI, email, contrasena) VALUES (?, ?, ?, ?, ?);");
$query->bind_param("sssss", $nombre, $apellido, $DNI, $email, $contrasena);
$query->execute();

echo "Funcionario registrado";

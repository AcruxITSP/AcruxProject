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

// Aca comienza el codigo
include 'globalFunctions.php';

if (hayRegistro("Dia")) {
    $conn->close();
    exit("ERROR: Ya hay un horario semanal registrado");
    // Se le debe dar la opcion al usuario de sobrescribir los registros ya existentes
}

// Reinicia los valores de las claves primarias para que empiecen a contar desde 1
resetAutoIncrement("Dia");

foreach ($_POST as $dia => $seleccionado){

    if ($seleccionado === "true"){
        insertDia($dia);
    }

    header("Location: " . "crearIntervalos.html"); 
}

relacionarHorario("Dia", "Horario", "Intervalo");

function insertDia($dia)
{
    global $conn;

    $query = $conn->prepare("INSERT INTO Dia (Nombre) VALUES (?);");
    $query->bind_param("s", $dia);
    $query->execute();
}
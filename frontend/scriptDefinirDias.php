<?php
// Aca comienza el codigo
include 'globalFunctions.php';

$conn = iniciarConexion();

if (hayRegistros("Dia")) {
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
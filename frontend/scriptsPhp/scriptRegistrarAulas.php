<?php
require 'globalFunctions.php';

$conn = iniciarConexion();

$codigo = $_POST["codigo"];
$piso = $_POST["piso"];
$proposito = $_POST["proposito"];
$cantidadSillas = $_POST["cantidadSillas"];

if (buscarRegistro("Aula", "Codigo", $codigo) == true) {
    echo "ERROR: Ya hay un aula registrada con el código: $codigo <br><br>";
    echo "¿Desea modificarla?";

    /*
        if respuesta == si {
            cambiar a pagina de modificacion de funcionarios
        } else {
            salir  
        }
    */

    exit("<br><br>Operacion cancelada");
}

insertAula($codigo, $piso, $proposito, $cantidadSillas);
echo "Aula registrada";

function insertAula($codigo, $piso, $proposito, $cantidadSillas)
{
    global $conn;

    $query = $conn->prepare("INSERT INTO Aula (Codigo, Piso, Proposito, CantidadSillas) VALUES (?, ?, ?, ?);");
    $query->bind_param("sssi", $codigo, $piso, $proposito, $cantidadSillas);
    $query->execute();
}


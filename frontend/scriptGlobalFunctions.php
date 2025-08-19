<?php

// Comprobar si hay registros en una tabla especifica
function hayRegistro($tabla)
{
    $cantidadRegistros = contarRegistros($tabla);

    // Comprueba si ya hay un horario registrado
    if ($cantidadRegistros !== 0) {
        return true;
    } else {
        return false;
    }
}

function contarRegistros($tabla)
{
    global $conn;

    // Cuenta los registros en la tabla
    $query = $conn->prepare("SELECT COUNT(Id_$tabla) AS resultado FROM $tabla;");
    $query->execute();

    // La query devuelve un resultado numerico, que se guarda en la variable $registro
    $registro = $query->get_result();
    $count = $registro->fetch_assoc();
    $cantidadRegistros = (int) $count['resultado'];

    return $cantidadRegistros;
}

function relacionarHorario($tabla1, $tablaForan, $tabla2)
{
    global $conn;
    // Verificar que existan registros en ambas tablas
    if (!(hayRegistro($tabla1) || hayRegistro($tabla2))) {
        exit();
    }

    // Si la tabla con ambas claves ya tiene registros, se eliminan
    if (hayRegistro($tablaForan)) {
        eliminarRegistros($tablaForan);
    }

    resetAutoIncrement($tablaForan);

    // Se cuenta cuantos registros tiene cada tabla
    $registrosT1 = contarRegistros($tabla1);
    $registrosT2 = contarRegistros($tabla2);

    // Cada registro de la primera tabla se relaciona con cada registro de la segunda
    for ($i = 1; $i <= $registrosT1; $i++) {

        for ($j = 1; $j <= $registrosT2; $j++) {

            $query = $conn->prepare("INSERT INTO $tablaForan (Id_$tabla1, Id_$tabla2) VALUES (?, ?);");
            $query->bind_param("ii", $i, $j); // "i" -> numero entero
            $query->execute();
        }
    }
}

function eliminarRegistros($tabla)
{
    global $conn;

    // Elimina todos los registros de una tabla especifica
    $query = $conn->prepare("DELETE FROM $tabla;");
    $query->execute();
}

function resetAutoIncrement($tabla)
{
    global $conn;
    // Reinicia la propiedad AUTO_INCREMENT para que vuelva a empezar de 1
    $query = $conn->prepare("ALTER TABLE $tabla AUTO_INCREMENT = 1;");
    $query->execute();
}

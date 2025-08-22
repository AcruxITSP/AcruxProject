<?php
function iniciarConexion()
{
    $servername = "localhost";
    $username = "root"; // Nombre de usuario por defecto en phpMyAdmin
    $password = ""; // Contrasena por defecto
    $dbname = "db_acrux";

    // Crear una conexion con la base de datos
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}
function buscarRegistro($tabla, $columna, $valor)
{
    global $conn;

    // Identificar el tipo de dato para la funcion bind_param()
    switch (gettype($valor)) {
        case "integer":
            $tipoValor = "i";
            break;

        case "string":
            $tipoValor = "s";
            break;
    }

    // Ejecuta la query
    $query = $conn->prepare("SELECT * FROM $tabla WHERE $columna = ?");
    $query->bind_param("$tipoValor", $valor);
    $query->execute();

    // El resultado de la consulta se guarda en la variable "$registroFun"
    $registroFun = $query->get_result();

    // Si la consulta no devuelve un registro, se envia un mensaje de error
    if ($registroFun->num_rows == 0) {
        return false;
    } else {
        return true;
    }
}

// Comprobar si hay registros en una tabla especifica
function hayRegistros($tabla)
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
    if (!(hayRegistros($tabla1) || hayRegistros($tabla2))) {
        exit();
    }

    // Si la tabla con ambas claves ya tiene registros, se eliminan
    if (hayRegistros($tablaForan)) {
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

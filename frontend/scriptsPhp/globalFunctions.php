<?php
// @collapse

$conn = iniciarConexion();

// Crear un nuevo objeto de tipo mysqli y retornarlo
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

// Traer todos los registros de una tabla cualquiera y ordenarlos segun un valor especifico
// El resultado se guardara en la variable "$registros" para luego ser retornada
// Nota: Se ordenan de mayor a menor

function fetchAllRegisters($tabla, $atributoOrdenador)
{
    global $conn;

    $query = $conn->prepare("SELECT * FROM $tabla ORDER BY $atributoOrdenador");
    $query->execute();

    $registros = $query->get_result();

    return $registros;
}

// Buscar toda la informacion de los funcionarios que esten registrados en la tabla de un cierto rol
// El resultado se guardara en la variable "$registros" para luego ser retornada
// Nota: No se puede usar "Funcionario" como argumento

function fetchRole($rol)
{
    global $conn;

    if (strtolower($rol) == "Funcionario") {
        echo "ERROR: No se puede usar 'fetchRole(Funcionario)'. En su lugar se debe usar 'fetchFuncionarios()'";
    }

    $query = $conn->prepare("SELECT * FROM funcionario INNER JOIN $rol ON funcionario.Id_funcionario = $rol.Id_funcionario;");
    $query->execute();

    $registros = $query->get_result();

    return $registros;
}

// Buscar toda la informacion de todos los funcionarios registrados
// El resultado se guardara en la variable "$registros" para luego ser retornada
function fetchFuncionarios()
{
    global $conn;

    $query = $conn->prepare("SELECT * FROM funcionario;");
    $query->execute();

    $registros = $query->get_result();

    return $registros;
}

// Buscar toda la informacion de todos los estudiantes registrados
// El resultado se guardara en la variable "$registros" para luego ser retornada
function fetchEstudiante()
{
    global $conn;

    $query = $conn->prepare("SELECT * FROM Estudiante;");
    $query->execute();

    $registros = $query->get_result();

    return $registros;
}

// "listarUsuario(nombreTabla)" crea un elemento "tabla" de HTMl, en la que se muestran los registros de 
// cada persona registrada en la tabla indicada, mostrando su cedula, nombre, apellido y una columna que
// permite seleccionarlas a traves de un input de tipo checkbox

function listarUsuario($tabla)
{
    $tabla = strtolower($tabla);

    if ($tabla === "estudiante") {
        $registros = fetchEstudiante();
        $identificador = "Id_estudiante";
    } else if ($tabla === "funcionario") {
        $registros = fetchFuncionarios();
        $identificador = "Id_funcionario";
    } else {
        $registros = fetchRole($tabla);
        $identificador = "Id_funcionario";
    }

?>
    <table>
        <tr>
            <th>Cédula</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Seleccionado</th>
        </tr>
    <?php

    for ($i = 1; $i <= $registros->num_rows; $i++) {
        $row = $registros->fetch_assoc();
        echo "<tr>";
        echo "<td class='DNI'><label for='fun$i'>" . $row['DNI'] . "</label></td>";
        echo "<td><label for='fun$i'>" . $row['Nombre'] . "</label></td>";
        echo "<td><label for='fun$i'>" . $row['Apellido'] . "</label></td>";
        echo "<td><input type='checkbox' id='fun$i' value=" . $row[$identificador] . "></td>";
        echo "</tr>";
    }

    echo "</table>";
}

function verificarInicioSesion()
{
    // Comprobar que la sesión esté iniciada
    // Si la variable "username" no tiene valor, redirecciona al usuario a index.php
    if (!isset($_SESSION["username"])) {
        header("Location: " . "../frontend/logIn.php");
    }
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
function tieneRegistros($tabla)
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

function cerrarSesion()
{
    session_start();
    session_unset();
    session_destroy();
}

// Esta funcion es mas especifica, es para crear todos los registros de la tabla "Hora"
// Toma los Id de cada registro en la tabla "Intervalo" y los relaciona con cada registro en la tabla "Dia"
function relacionarHorario()
{
    global $conn;
    // Verificar que existan registros en ambas tablas
    if (!(tieneRegistros("dia") || tieneRegistros("intervalo"))) {
        exit();
    }

    // Si la tabla "Hora", con ambas claves, ya tiene registros, los elimina
    if (tieneRegistros("Hora")) {
        eliminarRegistros("Hora");
    }

    // Hacer que las Id de "Hora" empiecen a contar desde 1
    resetAutoIncrement("Hora");

    // Se cuenta cuantos registros tiene cada tabla
    $registrosT1 = contarRegistros("dia");
    $registrosT2 = contarRegistros("intervalo");

    // Cada registro de la primera tabla se relaciona con cada registro de la segunda
    for ($i = 1; $i <= $registrosT1; $i++) {

        for ($j = 1; $j <= $registrosT2; $j++) {

            $query = $conn->prepare("INSERT INTO Hora (Id_dia, Id_intervalo) VALUES (?, ?);");
            $query->bind_param("ii", $i, $j); // "i" -> numero entero
            $query->execute();
        }
    }
}


/*Solucionar esto es demasiado complejo 😬
// Funcion para insertar un registro en cualquier tabla
// "$insert" es un array asociativo que guarda el nombre de cada columna en la tabla junto a su valor correspondiente
// Ej: "Nombre" => "Juan"

function insertInto($tabla, $insert)
{
    global $conn;
    // Se crean variables que contienen un string vacio
    // Esto permite que la query se ajuste a cualquier cantidad de columnas
    $columns = "";
    $values = "";
    $tipoValor = "";
    $bindValues = "";

    foreach ($insert as $column => $value) {
        $columns .= $column . ", ";
        $values .= $value . ", ";
        $bindValues .= "?, ";

        // Por ahora solo estamos usando valores de tipo entero y varchar, pero se puede expandir para
        // abarcar mas tipos de datos
        switch (gettype($value)) {
            case "integer":
                $tipoValor .= "i";
                break;

            case "string":
                $tipoValor .= "s";
                break;
        }
    }

    // Quitar las ", " sobrantes al final cada string
    $columns = substr($columns, 0, -2);
    $values = substr($values, 0, -2);
    $bindValues = substr($bindValues, 0, -2);

    $query = $conn->prepare("INSERT INTO $tabla ($columns) VALUES ($bindValues);");
    // Aca el problema: Cuando se pasa la variable $values, lo toma como un unico valor
    // Es necesario utilizar punteros y la funcion "call_user_func_array()"... cosa que no se
    // Si funcionara, la funcion bind_param separaria el string de values y 
    // lo tomaria como: $value1, $value2, $value3... 
    $query->bind_param($tipoValor, $values);
    $query->execute();
}
*/
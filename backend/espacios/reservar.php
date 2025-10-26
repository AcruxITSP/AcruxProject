<?php
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';
require_once dirname(__FILE__).'/../util/timing.php';
//require_once dirname(__FILE__).'/../util/reserva_recursos.php';
require_once dirname(__FILE__).'/../util/espacios.php';

// - HORAS_NO_ESPECIFICADAS
// - ESPACIO_NO_ESPECIFICADO
// - NECESITA_LOGIN
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if(!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");
    $idUsuario = $_SESSION['id_usuario'];

    // si no se han seleccionado horas a reservar, error.
    if(!isset($_POST['horas'])) Respuestas::enviarError("HORAS_NO_ESPECIFICADAS");
    if(!isset($_POST['id_espacio'])) Respuestas::enviarError("ESPACIO_NO_ESPECIFICADO");


    // La id del espacio
    $idEspacio = $_POST['id_espacio'];
    // El numero de los periodos por los cuales el recurso sera reservado
    // Ejemplo: [1, 3, 4] -> 7:00-7:45, 8:40-9:25 y 10:20-11:05
    $numerosPeriodos = $_POST['horas'];

    // Conectar
    $con = connectDb();
    $con->begin_transaction();

    // Trae los periodos por los cuales se reservara el recurso

    // genera un string -> ?, ?, ? dependiendo de la cantidad de numeros
    $placeholders = implode(', ', array_fill(0, count(value: $numerosPeriodos), '?'));
    // genera un string -> iii dependiendo de la cantidad de numeros
    $types = implode(array_fill(0, count($numerosPeriodos), 'i'));
    // Ahora si, traemos los periodos seleccionados
    $sql = "SELECT * FROM Periodo WHERE numero IN ($placeholders)";
    $result = SQL::valueQuery($con, $sql, $types, ...$numerosPeriodos);
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    $periodos = [];
    while($periodo = $result->fetch_assoc())
    {
        $periodos[] = $periodo;
    }

    // Busca cual es la hora final de la reserva
    $horaFinal = $periodos[count($periodos)-1]['salida'];

    // Insertamos la reserva
    $fechaActual = obtenerFechaActual();
    $sql = "INSERT INTO ReservaEspacio (id_usuario, fecha, id_espacio, hora_final)
            VALUES (?, ?, ?, ?)";
    $result = SQL::actionQuery($con, $sql, "isis", $idUsuario, $fechaActual, $idEspacio, $horaFinal);
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    $idReserva = $con->insert_id;

    // Insertamos los periodos de la reserva

    // Extraemos las id de los periodos a partir de los numeros de los periodos
    foreach ($periodos as $periodo)
    {
        $idPeriodo = $periodo['id_periodo'];
        $sql = "INSERT INTO PeriodoReservaEspacio (id_reserva, id_periodo)
                VALUES (?, ?)";
        $result = SQL::actionQuery($con, $sql, "ii", $idReserva, $idPeriodo);
        if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    }

    Respuestas::enviarOk(null, $con);
}


if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $idEspacio = (int)($_GET['id_espacio'] ?? 0);

    $datos = [
        "espacio" => null,
        "periodos" => [],
        "estado_por_numero_periodos" => []
    ];

    $con = connectDb();
    
    $sql = "SELECT * FROM espacio WHERE id_espacio = ?";
    $result = SQL::valueQuery($con, $sql, "i", $idEspacio);
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    $espacio = $result->fetch_assoc();
    $datos['espacio'] = $espacio;

    $sql = "SELECT * FROM periodo";
    $result = SQL::valueQuery($con, $sql, "");
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    while($periodo = $result->fetch_assoc())
    {
        $datos['periodos'][] = $periodo;
    }

    foreach($datos['periodos'] as $periodo)
    {
        $numeroPeriodo = $periodo['numero'];
        $datos['estado_por_numero_periodos'][$numeroPeriodo] = datosDisponibilidadDeEspacioHoyEn($con, $idEspacio, $numeroPeriodo);
    }

    Respuestas::enviarOk($datos, $con);
}

?>
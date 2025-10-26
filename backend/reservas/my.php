<?php
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';
require_once dirname(__FILE__).'/../util/strings.php';
require_once dirname(__FILE__).'/../util/timing.php';

// PUEDE TIRAR LOS CODIGOS DE ERROR:
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if(!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");
    $con = connectDb();

    $idUsuario = $_SESSION['id_usuario'];
    $reservasRecursos = obtenerDatosReservasRecursosHoy($con, $idUsuario);
    $reservasEspacios = obtenerDatosReservasEspaciosHoy($con, $idUsuario);
    $periodosPorId = obtenerPeriodosPorId($con);
    Respuestas::enviarOk([
        "reservas_recursos" => $reservasRecursos,
        "reservas_espacios" => $reservasEspacios,
        "periodos_por_id" => $periodosPorId
    ], $con);
}

function obtenerPeriodosPorId($con)
{
    $sql = "SELECT * FROM periodo";
    $result = SQL::valueQuery($con, $sql, "");
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    $periodosPorId = [];
    while($periodo = $result->fetch_assoc())
    {
        $periodosPorId[$periodo['id_periodo']] = $periodo;
    }

    return $periodosPorId;
}

function obtenerDatosReservasRecursosHoy($con, $idUsuario)
{
    $fechaActual = obtenerFechaActual();
    $respuesta = [];

    // Obtener reservas de recursos realizadas por el usuario actual HOY.
    $sqlReserva = "SELECT
                reservarecurso.id_reserva,
                recursoexterno.id_recurso_externo,
                recursoexterno.id_espacio,
                recurso.id_recurso,
                reservarecurso.cantidad AS cantidad_reservado,
                recurso.tipo AS tipo_recurso,
                espacio.tipo AS tipo_espacio,
                espacio.numero AS numero_espacio,
                usuario.id_usuario,
                usuario.nombre as nombre_usuario,
                usuario.apellido as apellido_usuario
                
            FROM reservarecurso
            JOIN recursoexterno ON reservarecurso.id_recurso_externo = recursoexterno.id_recurso_externo
            JOIN recurso ON recursoexterno.id_recurso = recurso.id_recurso
            JOIN usuario ON reservarecurso.id_usuario = usuario.id_usuario
            LEFT JOIN espacio ON recursoexterno.id_espacio = espacio.id_espacio
            WHERE reservarecurso.fecha = ?
            AND usuario.id_usuario = ?";
    $resultReserva = SQL::valueQuery($con, $sqlReserva, "si", $fechaActual, $idUsuario);
    if($resultReserva instanceof ErrorDB) Respuestas::enviarError($resultReserva, $con);
    
    while($datosReserva = $resultReserva->fetch_assoc())
    {
        $respuesta[] = $datosReserva;
        $respuesta[count($respuesta)-1]['id_periodos'] = []; // aca se van a guardar los
        $idReserva = $datosReserva['id_reserva'];

        // Obtener las id de periodos que contiene X reserva
        $sqlPeriodos = "SELECT periodoreservarecurso.id_periodo
                FROM periodoreservarecurso, reservarecurso, periodo
                WHERE periodoreservarecurso.id_reserva = reservarecurso.id_reserva
                AND periodoreservarecurso.id_periodo = periodo.id_periodo
                AND reservarecurso.id_reserva = ?";

        $resultPeriodos = SQL::valueQuery($con, $sqlPeriodos, "i", $idReserva);
        if($resultPeriodos instanceof ErrorDB) Respuestas::enviarError($resultPeriodos, $con);
        while($periodo = $resultPeriodos->fetch_assoc())
        {
            // guardar los periodos de la reserva
            $respuesta[count($respuesta)-1]['id_periodos'][] = $periodo['id_periodo'];
        }
    }

    return $respuesta;
}

function obtenerDatosReservasEspaciosHoy($con, $idUsuario)
{
    $fechaActual = obtenerFechaActual();
    $respuesta = [];

    // Obtener reservas de espacios realizadas por el usuario indicado HOY.
    $sqlReserva = "SELECT
            reservaespacio.id_reserva,
            espacio.id_espacio,
            espacio.tipo as tipo_espacio,
            espacio.numero as numero_espacio,
            usuario.id_usuario,
            usuario.nombre as nombre_usuario,
            usuario.apellido as apellido_usuaruo
            FROM reservaespacio, espacio, usuario
            WHERE reservaespacio.id_espacio = espacio.id_espacio
            AND reservaespacio.id_usuario = usuario.id_usuario
            AND reservaespacio.fecha = ?
            AND reservaespacio.id_usuario = ?";
    $resultReserva = SQL::valueQuery($con, $sqlReserva, "si", $fechaActual, $idUsuario);
    if($resultReserva instanceof ErrorDB) Respuestas::enviarError($resultReserva, $con);
    
    while($datosReserva = $resultReserva->fetch_assoc())
    {
        $respuesta[] = $datosReserva;
        $respuesta[count($respuesta)-1]['id_periodos'] = []; // aca se van a guardar los
        $idReserva = $datosReserva['id_reserva'];

        // Obtener las id de periodos que contiene X reserva
        $sqlPeriodos = "SELECT periodoreservaespacio.id_periodo
                FROM periodoreservaespacio, reservaespacio, periodo
                WHERE periodoreservaespacio.id_reserva = reservaespacio.id_reserva
                AND periodoreservaespacio.id_periodo = periodo.id_periodo
                AND reservaespacio.id_reserva = ?";

        $resultPeriodos = SQL::valueQuery($con, $sqlPeriodos, "i", $idReserva);
        if($resultPeriodos instanceof ErrorDB) Respuestas::enviarError($resultPeriodos, $con);
        while($periodo = $resultPeriodos->fetch_assoc())
        {
            // guardar los periodos de la reserva
            $respuesta[count($respuesta)-1]['id_periodos'][] = $periodo['id_periodo'];
        }
    }

    return $respuesta;
}
?>
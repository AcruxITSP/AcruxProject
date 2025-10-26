<?php
require_once dirname(__FILE__) ."/../util/periodos.php";

/**
 * Devuelve la cantidad libre de un recurso en un intervalo y fecha dados.
 *
 * Lógica:
 *  - busca id_periodo por numero de intervalo
 *  - suma SUM(ReservaRecurso.cantidad) para ese recurso, fecha y periodo
 *  - cantidad_libre = Recurso.cantidad_total - reservado
 *
 * @param mysqli $con
 * @param int $idRecurso
 * @param int $numeroIntervalo
 * @param string|null $fecha formato 'YYYY-MM-DD' (por defecto hoy)
 * @return int cantidad libre (no negativa)
 */
function obtenerCantidadLibrePorPeriodo($con, $idRecurso, $numeroIntervalo, $fecha = null)
{
    $fecha = $fecha ?? obtenerFechaActual();

    // 1) obtener cantidad total
    $sql = "SELECT *
            FROM Recurso, RecursoExterno
            WHERE Recurso.id_recurso = ? 
            AND Recurso.id_recurso = RecursoExterno.id_recurso
            LIMIT 1";
    $res = SQL::valueQuery($con, $sql, "i", $idRecurso);
    if($res instanceof ErrorDB) Respuestas::enviarError($res);
    $row = $res->fetch_assoc();
    $cantidadTotal = (int)$row['cantidad_total'];


    // 1) obtener id_periodo
    $sql = "SELECT id_periodo FROM Periodo WHERE numero = ? LIMIT 1";
    $res = SQL::valueQuery($con, $sql, "i", $numeroIntervalo);
    if($res instanceof ErrorDB) Respuestas::enviarError($res);
    $row = $res->fetch_assoc();
    $id_periodo = (int)$row['id_periodo'];

    // 2) calcular reservado
    $sql = 
        "SELECT 
            Rex.cantidad_total AS cantidad_total,
            COALESCE(SUM(Res.cantidad), 0) AS reservado
        FROM Recurso R
        LEFT JOIN RecursoExterno Rex 
            ON Rex.id_recurso = R.id_recurso
        LEFT JOIN ReservaRecurso Res 
            ON Res.id_recurso_externo = Rex.id_recurso_externo
            AND Res.fecha = ?
        WHERE R.id_recurso = ?
          AND Res.id_reserva IN (
                SELECT PRR.id_reserva
                FROM PeriodoReservaRecurso PRR
                WHERE PRR.id_periodo = ?
          )
        GROUP BY Rex.cantidad_total
        LIMIT 1
    ";

    $result = SQL::valueQuery($con, $sql, "sii", $fecha, $idRecurso, $id_periodo);
    if($result instanceof ErrorDB) Respuestas::enviarError($res);
    $r = $result->fetch_assoc();
    if(!$r) $reservado = 0;
    else $reservado = (int)$r['reservado'];

    $libre = $cantidadTotal - $reservado;
    return $libre < 0 ? 0 : $libre;
}

/**
 * Devuelve la cantidad libre de un recurso en un intervalo y fecha dados.
 *
 * Lógica:
 *  - busca id_periodo por numero de intervalo
 *  - suma SUM(ReservaRecurso.cantidad) para ese recurso, fecha y periodo
 *  - cantidad_libre = Recurso.cantidad_total - reservado
 *
 * @param mysqli $con
 * @param int $idRecurso
 * @param int $numeroIntervalo
 * @param string|null $fecha formato 'YYYY-MM-DD' (por defecto hoy)
 * @return int cantidad libre (no negativa)
 */
function obtenerCantidadLibrePorAhora($con, $idRecurso)
{
    $fecha = $fecha ?? obtenerFechaActual();
    $hora = obtenerHoraActual();
    $periodo = obtenerPeriodoCercanoOFuturo($con);
    $numeroIntervalo = $periodo['numero'];

    // 1) obtener cantidad total
    $sql = "SELECT *
            FROM Recurso, RecursoExterno
            WHERE Recurso.id_recurso = ? 
            AND Recurso.id_recurso = RecursoExterno.id_recurso
            LIMIT 1";
    $res = SQL::valueQuery($con, $sql, "i", $idRecurso);
    if($res instanceof ErrorDB) Respuestas::enviarError($res);
    $row = $res->fetch_assoc();
    $cantidadTotal = (int)$row['cantidad_total'];


    // 1) obtener id_periodo
    $sql = "SELECT id_periodo FROM Periodo WHERE numero = ? LIMIT 1";
    $res = SQL::valueQuery($con, $sql, "i", $numeroIntervalo);
    if($res instanceof ErrorDB) Respuestas::enviarError($res);;
    $row = $res->fetch_assoc();
    $id_periodo = (int)$row['id_periodo'];

    // 2) calcular reservado
    $sql = 
        "SELECT 
            Rex.cantidad_total AS cantidad_total,
            COALESCE(SUM(Res.cantidad), 0) AS reservado
        FROM Recurso R
        LEFT JOIN RecursoExterno Rex 
            ON Rex.id_recurso = R.id_recurso
        LEFT JOIN ReservaRecurso Res 
            ON Res.id_recurso_externo = Rex.id_recurso_externo
            AND Res.fecha = ?
            AND Res.hora_final > ?
        WHERE R.id_recurso = ?
          AND Res.id_reserva IN (
                SELECT PRR.id_reserva
                FROM PeriodoReservaRecurso PRR
                WHERE PRR.id_periodo = ?
          )
        GROUP BY Rex.cantidad_total
        LIMIT 1
    ";

    $result = SQL::valueQuery($con, $sql, "ssii", $fecha, $hora, $idRecurso, $id_periodo);
    if($result instanceof ErrorDB) Respuestas::enviarError($res);
    $r = $result->fetch_assoc();
    if(!$r) $reservado = 0;
    else $reservado = (int)$r['reservado'];

    $libre = $cantidadTotal - $reservado;
    return $libre < 0 ? 0 : $libre;
}
?>
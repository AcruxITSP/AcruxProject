<?php
require_once dirname(__FILE__) ."/../util/timing.php";
require_once dirname(__FILE__) ."/../other/time.php";

/**
 * Segun el tiempo actual:
 * - Intenta obtener el periodo actual,
 * - Si el tiempo actual esta entre 2 periodos, obten el periodo futuro
 * - Si no hay periodos proximos, obten el ultimo periodo
 * 
 * Puede tirar los errores:
 * - NO_HAY_PERIODOS
 * @param mysqli $con
 * @return array|bool|null
 */

function obtenerPeriodoCercanoOFuturo(mysqli $con): array
{
    $horaActual = obtenerHoraActual();
    $fechaActual = obtenerFechaActual();

    // Intentar obtener el periodo actual
    $sql = "
        SELECT *
        FROM Periodo
        WHERE ? BETWEEN entrada AND salida
        LIMIT 1;
    ";
    $resultPeriodoCercano = SQL::valueQuery($con, $sql, "s", $horaActual);

    if ($resultPeriodoCercano instanceof ErrorDB)
        Respuestas::enviarError($resultPeriodoCercano);

    $periodoCercano = $resultPeriodoCercano->fetch_assoc();

    // Si no hay ningún período actual, obtener el siguiente periodo
    if (!$periodoCercano) {
        $sql = "
            SELECT *
            FROM Periodo
            WHERE salida > ?
            ORDER BY salida ASC
            LIMIT 1;
        ";
        $resultPeriodoCercano = SQL::valueQuery($con, $sql, "s", $horaActual);
        
        if ($resultPeriodoCercano instanceof ErrorDB)
            Respuestas::enviarError($resultPeriodoCercano);

        $periodoCercano = $resultPeriodoCercano->fetch_assoc();
    }

    // Si no hay ningún período siguiente, obtener el ultimo periodo
    if (!$periodoCercano) {
        $sql = "SELECT *
                FROM Periodo
                ORDER BY salida DESC
                LIMIT 1
        ";
        $resultPeriodoCercano = SQL::valueQuery($con, $sql, "");
        
        if ($resultPeriodoCercano instanceof ErrorDB)
            Respuestas::enviarError($resultPeriodoCercano);

        $periodoCercano = $resultPeriodoCercano->fetch_assoc();
    }

    if($periodoCercano == null) Respuestas::enviarError("NO_HAY_PERIODOS", $con);
    return $periodoCercano;

}

?>
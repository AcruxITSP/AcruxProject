<?php
require_once dirname(__FILE__) ."/../other/sql.php";
require_once dirname(__FILE__) ."/../other/respuestas.php";
require_once dirname(__FILE__) ."/../util/timing.php";
require_once dirname(__FILE__) ."/../other/time.php";

function profeEstaAusente($con, $idProfesor, $fecha, $numeroPeriodo)
{
    $sql = "SELECT 
                CASE 
                    WHEN EXISTS (
                        SELECT 1
                        FROM Ausencia a
                        JOIN Ausencia_IntervaloAusencia aia ON a.id_ausencia = aia.id_ausencia
                        JOIN IntervaloAusencia ia ON ia.id_intervalo_ausencia = aia.id_intervalo_ausencia
                        JOIN Periodo pi ON pi.id_periodo = ia.id_periodo_inicio
                        JOIN Periodo pf ON pf.id_periodo = ia.id_periodo_final
                        WHERE a.id_profesor = ?
                        AND ia.fecha = ?
                        AND ? BETWEEN pi.numero AND pf.numero
                    )
                    THEN 'AUSENTE'
                    ELSE 'PRESENTE'
                END AS estado;
            ";

    $result = SQL::valueQuery($con, $sql, "isi", $idProfesor, $fecha, $numeroPeriodo);

    if ($result instanceof ErrorDB) {
        Respuestas::enviarError($result, $con);
    }

    $estaAusente = $result->fetch_assoc()['estado'] === 'AUSENTE';
    $result->close();
    return $estaAusente;
}

function profeEstaLibreParaAsignacionHorario($con, $idProfesor, $idDia, $numeroPeriodo)
{
    $sql = "SELECT 
                CASE 
                    WHEN COUNT(*) > 0 THEN 'OCUPADO'
                    ELSE 'LIBRE'
                END AS disponibilidad
            FROM Modulo m
            INNER JOIN Clase c ON m.id_clase = c.id_clase
            INNER JOIN Hora h ON m.id_hora = h.id_hora
            INNER JOIN Periodo p ON h.id_periodo = p.id_periodo
            WHERE 
                c.id_profesor = ?
                AND h.id_dia = ?
                AND p.numero = ?;
";

    $result = SQL::valueQuery($con, $sql, "iii", $idProfesor, $idDia, $numeroPeriodo);

    if ($result instanceof ErrorDB) {
        Respuestas::enviarError($result, $con);
    }

    $estaLibre = $result->fetch_assoc()['disponibilidad'] === 'LIBRE';
    $result->close();
    return $estaLibre;
}

?>
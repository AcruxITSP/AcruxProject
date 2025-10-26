<?php
require_once dirname(__FILE__) ."/../other/sql.php";
require_once dirname(__FILE__) ."/../other/respuestas.php";
require_once dirname(__FILE__) ."/../util/timing.php";
require_once dirname(__FILE__) ."/../other/time.php";

function espacioEstaLibreParaAsignacionHorario($con, $idEspacio, $idDia, $numeroPeriodo)
{
    $sql = "SELECT
                CASE
                    WHEN NOT EXISTS (
                        SELECT 1
                        FROM Modulo m
                        JOIN Hora h ON m.id_hora = h.id_hora
                        WHERE m.id_espacio = ?
                        AND h.id_dia = ?
                        AND h.id_periodo = (SELECT id_periodo FROM periodo WHERE numero = ?)
                    ) THEN 'LIBRE'
                    ELSE 'OCUPADO'
                END AS estado_espacio";
    $result = SQL::valueQuery($con, $sql, "iii", $idEspacio, $idDia, $numeroPeriodo);

    if ($result instanceof ErrorDB) {
        Respuestas::enviarError($result, $con);
    }

    $estaLibre = $result->fetch_assoc()['estado_espacio'] === 'LIBRE';
    $result->close();
    return $estaLibre;
}

function espacioEstaLibreEn($con, $idEspacio, $numeroPeriodo = null, $idDia = null , $fecha= null)
{
    $fecha ??= obtenerFechaActual();              // Ej: '2025-10-17'
    $idDia ??= obtenerNumeroDiaActual();          // Ej: 5 (Viernes)

    
    if($numeroPeriodo == null) $periodo = obtenerPeriodoCercanoOFuturo($con); // Ej: ['id_periodo' => 2]
    else
    {
        // Obtener el periodo segun su numero indicado.
        $sql = "SELECT * FROM periodo WHERE numero = ? LIMIT 1";
        $result = SQL::valueQuery($con ,$sql, "i", $numeroPeriodo);
        if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
        $periodo = $result->fetch_assoc();
    }

    if (!$periodo) return false;

    $sql = "
        SELECT
            CASE
                WHEN (
                    NOT EXISTS (
                        SELECT 1
                        FROM Modulo m
                        JOIN Hora h ON m.id_hora = h.id_hora
                        JOIN Clase c ON m.id_clase = c.id_clase
                        LEFT JOIN Profesor p ON c.id_profesor = p.id_profesor
                        LEFT JOIN Ausencia a ON a.id_profesor = p.id_profesor
                        LEFT JOIN Ausencia_IntervaloAusencia ai ON ai.id_ausencia = a.id_ausencia
                        LEFT JOIN IntervaloAusencia ia ON ia.id_intervalo_ausencia = ai.id_intervalo_ausencia
                        WHERE m.id_espacio = ?
                          AND h.id_dia = ?
                          AND h.id_periodo = ?
                          AND (
                                ia.id_intervalo_ausencia IS NULL
                                OR ia.fecha != ?
                                OR ? < ia.id_periodo_inicio
                                OR ? > ia.id_periodo_final
                              )
                    )
                    AND
                    NOT EXISTS (
                        SELECT 1
                        FROM ReservaEspacio re
                        JOIN PeriodoReservaEspacio pre ON re.id_reserva = pre.id_reserva
                        WHERE re.id_espacio = ?
                          AND re.fecha = ?
                          AND pre.id_periodo = ?
                    )
                ) THEN 'LIBRE'
                ELSE 'OCUPADO'
            END AS estado_espacio
    ";

    // Parámetros: m.id_espacio, h.id_dia, h.id_periodo, ia.fecha, id_periodo_actual, id_periodo_actual, re.id_espacio, re.fecha, pre.id_periodo
    $result = SQL::valueQuery($con, $sql, "iiisiiisi",
        $idEspacio,
        $idDia,
        $periodo['id_periodo'],
        $fecha,
        $periodo['id_periodo'],
        $periodo['id_periodo'],
        $idEspacio,
        $fecha,
        $periodo['id_periodo']
    );

    if ($result instanceof ErrorDB) {
        Respuestas::enviarError($result, $con);
    }

    $estaLibre = $result->fetch_assoc()['estado_espacio'] === 'LIBRE';
    $result->close();
    return $estaLibre;
}

function espacioEstaLibreAhora($con, $idEspacio)
{
    $fechaActual = obtenerFechaActual();              // Ej: '2025-10-17'
    $idDiaActual = obtenerNumeroDiaActual();          // Ej: 5 (Viernes)
    $periodoActual = obtenerPeriodoCercanoOFuturo($con); // Ej: ['id_periodo' => 2]

    if (!$periodoActual) return false;

    $sql = "
        SELECT
            CASE
                WHEN (
                    NOT EXISTS (
                        SELECT 1
                        FROM Modulo m
                        JOIN Hora h ON m.id_hora = h.id_hora
                        JOIN Clase c ON m.id_clase = c.id_clase
                        LEFT JOIN Profesor p ON c.id_profesor = p.id_profesor
                        LEFT JOIN Ausencia a ON a.id_profesor = p.id_profesor
                        LEFT JOIN Ausencia_IntervaloAusencia ai ON ai.id_ausencia = a.id_ausencia
                        LEFT JOIN IntervaloAusencia ia ON ia.id_intervalo_ausencia = ai.id_intervalo_ausencia
                        WHERE m.id_espacio = ?
                          AND h.id_dia = ?
                          AND h.id_periodo = ?
                          AND (
                                ia.id_intervalo_ausencia IS NULL
                                OR ia.fecha != ?
                                OR ? < ia.id_periodo_inicio
                                OR ? > ia.id_periodo_final
                              )
                    )
                    AND
                    NOT EXISTS (
                        SELECT 1
                        FROM ReservaEspacio re
                        JOIN PeriodoReservaEspacio pre ON re.id_reserva = pre.id_reserva
                        WHERE re.id_espacio = ?
                          AND re.fecha = ?
                          AND pre.id_periodo = ?
                    )
                ) THEN 'LIBRE'
                ELSE 'OCUPADO'
            END AS estado_espacio
    ";

    // Parámetros: m.id_espacio, h.id_dia, h.id_periodo, ia.fecha, id_periodo_actual, id_periodo_actual, re.id_espacio, re.fecha, pre.id_periodo
    $result = SQL::valueQuery($con, $sql, "iiisiiisi",
        $idEspacio,
        $idDiaActual,
        $periodoActual['id_periodo'],
        $fechaActual,
        $periodoActual['id_periodo'],
        $periodoActual['id_periodo'],
        $idEspacio,
        $fechaActual,
        $periodoActual['id_periodo']
    );

    if ($result instanceof ErrorDB) {
        Respuestas::enviarError($result, $con);
    }

    $estaLibre = $result->fetch_assoc()['estado_espacio'] === 'LIBRE';
    $result->close();
    return $estaLibre;
}

function datosDisponibilidadDeEspacioHoyEn($con, $idEspacio, $numeroPeriodo = null)
{
    $fechaActual = obtenerFechaActual();
    $idDiaActual = obtenerNumeroDiaActual();

    if($numeroPeriodo == null)
    {
        $periodoActual = obtenerPeriodoCercanoOFuturo($con);
        $numeroPeriodoActual = $periodoActual['numero'];
        $idPeriodoActual = $periodoActual['id_periodo'];
    }
    else
    {
        $numeroPeriodoActual = $numeroPeriodo;
        $sql = "SELECT * FROM periodo WHERE numero = ?";
        $result = SQL::valueQuery($con, $sql, "i", $numeroPeriodo);
        if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
        $periodoActual = $result->fetch_assoc();
        $idPeriodoActual = $periodoActual['id_periodo'];
    }

    $sql = "SELECT
            COUNT(re.id_reserva) as cantidad_reservas,
            periodo.id_periodo, periodo.entrada, periodo.salida, periodo.numero as numero_periodo,
            usuario.nombre, usuario.apellido, usuario.id_usuario
            FROM `reservaespacio` as re, `periodoreservaespacio` as pre, `usuario`, `espacio`, `periodo`
            WHERE re.id_reserva = pre.id_reserva
            AND re.id_usuario = usuario.id_usuario
            AND re.id_espacio = espacio.id_espacio
            AND pre.id_periodo = periodo.id_periodo
            AND re.fecha = ?
            AND periodo.numero = ?
            AND re.id_espacio = ?";
    $result = SQL::valueQuery($con, $sql, "sii", $fechaActual, $numeroPeriodoActual, $idEspacio);
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    $row = $result->fetch_assoc();
    $cantidadReservas = (int)($row['cantidad_reservas']);
    if($cantidadReservas > 0)
    {
        return [
            "estado" => "reservado",
            "reservante" => [
                "id_usuario" => $row['id_usuario'],
                "nombre" => $row['nombre'],
                "apellido" => $row['apellido']
            ]
        ];
    }

    $sql = "SELECT
                CONCAT(g.grado, ' ', g.nombre) AS grupo,
                p.id_profesor,
                u.id_usuario,
                u.nombre,
                u.apellido,
                CASE
                    WHEN ia.id_intervalo_ausencia IS NOT NULL THEN 1
                    ELSE 0
                END AS esta_ausente
            FROM Modulo m
            JOIN Hora h              ON m.id_hora = h.id_hora
            JOIN Clase c             ON m.id_clase = c.id_clase
            JOIN Espacio e           ON m.id_espacio = e.id_espacio
            JOIN Grupo g             ON m.id_grupo = g.id_grupo
            JOIN Periodo pe          ON pe.id_periodo = h.id_periodo
            JOIN Profesor p          ON c.id_profesor = p.id_profesor
            JOIN Usuario u           ON p.id_usuario = u.id_usuario

            -- Relacionar ausencias con LEFT JOIN (para saber si coincide)
            LEFT JOIN Ausencia a 
                ON a.id_profesor = p.id_profesor
            LEFT JOIN Ausencia_IntervaloAusencia aia 
                ON aia.id_ausencia = a.id_ausencia
            LEFT JOIN IntervaloAusencia ia 
                ON aia.id_intervalo_ausencia = ia.id_intervalo_ausencia
                AND ia.fecha = ?
                AND ia.id_periodo_inicio <= ?
                AND ia.id_periodo_final >= ?

            WHERE h.id_periodo = ?
            AND h.id_dia = ?
            AND e.id_espacio = ?";
    $result = SQL::valueQuery($con, $sql, "siiiii", $fechaActual, $idPeriodoActual, $idPeriodoActual, $idPeriodoActual, $idDiaActual, $idEspacio);
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    $row = $result->fetch_assoc();

    // Esta libre
    if(!$row) return ["estado" => "libre"];

    $estaAusente = (int)($row['esta_ausente']) == 1;
    return [
        "estado" => $estaAusente ? "ausente" : "ocupado",
        "reservante" => [
            "id_usuario" => $row['id_usuario'],
            "nombre" => $row['nombre'],
            "apellido" => $row['apellido'],
            "grupo" => $row['grupo']
        ]
    ];
}

?>
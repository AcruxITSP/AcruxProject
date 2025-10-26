<?php
// ============================================================================
// INCLUSIÓN DE DEPENDENCIAS
// ============================================================================
// Se incluyen archivos con funciones auxiliares para conexión a la base de datos,
// ejecución de consultas SQL, manejo de tiempo, respuestas formateadas y errores.
require_once dirname(__FILE__).'/../other/connection.php';     // Contiene la función connectDb() para conectar a la base de datos
require_once dirname(__FILE__).'/../other/sql.php';             // Funciones estáticas SQL::actionQuery y SQL::valueQuery para ejecutar consultas seguras
require_once dirname(__FILE__).'/../other/time.php';            // Herramientas para convertir y manipular tiempos
require_once dirname(__FILE__).'/../other/respuestas.php';      // Maneja respuestas en formato estándar (éxito o error)
require_once dirname(__FILE__).'/../other/db_errors.php';       // Contiene la clase ErrorDB para capturar errores SQL
require_once dirname(__FILE__).'/../util/timing.php';           // Herramientas para medir tiempos de ejecución (opcional)

// ============================================================================
// FUNCIÓN: regenerarPeriodosYHoras
// ============================================================================
// Borra todos los periodos y los vuelve a crear desde cero,
// junto con las horas (relaciones entre días y periodos) según los parámetros dados.
function regenerarPeriodosYHoras(mysqli $con, $horaInicio, $horaFinal, $duracionRecreoMinutos, $duracionClaseMinutos)
{
    // Convertir los valores a enteros por seguridad
    $duracionRecreoMinutos = (int)$duracionRecreoMinutos;
    $duracionClaseMinutos = (int)$duracionClaseMinutos;

    // ------------------------------------------------------------------------
    // 1) BORRAR PERIODOS EXISTENTES
    // ------------------------------------------------------------------------
    $sql = "DELETE FROM Periodo";
    $result = SQL::actionQuery($con, $sql, "");
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    // ------------------------------------------------------------------------
    // 2) BORRAR CONFIGURACIÓN PREVIA DE GENERACIÓN DE PERIODOS
    // ------------------------------------------------------------------------
    $sql = "DELETE FROM GeneracionPeriodos";
    $result = SQL::actionQuery($con, $sql, "");
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    // ------------------------------------------------------------------------
    // 3) INSERTAR NUEVA CONFIGURACIÓN DE GENERACIÓN
    // ------------------------------------------------------------------------
    $sql = "INSERT INTO generacionperiodos(entrada, salida, duracion_clase_minutos, duracion_recreo_minutos)
            VALUES (?, ?, ?, ?)";
    $result = SQL::actionQuery($con, $sql, "ssii", $horaInicio, $horaFinal, $duracionClaseMinutos, $duracionRecreoMinutos);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    // ------------------------------------------------------------------------
    // 4) OBTENER TODAS LAS IDS DE LOS DÍAS (para vincular con cada periodo)
    // ------------------------------------------------------------------------
    $sql = "SELECT id_dia FROM dia";
    $result = SQL::valueQuery($con, $sql, "");
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    $idDias = [];
    while ($row = $result->fetch_assoc()) $idDias[] = $row['id_dia'];

    // ------------------------------------------------------------------------
    // 5) CREAR PERIODOS DE MANERA SECUENCIAL
    // ------------------------------------------------------------------------
    $horaUnixAAgregar = UnixTimeHelper::fromMySQLTime($horaInicio);   // Convertir hora de inicio a formato UNIX
    $horaUnixFinal = UnixTimeHelper::fromMySQLTime($horaFinal);       // Convertir hora final a formato UNIX
    $numeroPeriodo = 1;                                               // Contador de periodos

    // Bucle que genera los periodos hasta llegar al final de la jornada
    while ($horaUnixAAgregar <= UnixTimeHelper::addMinutes($horaUnixFinal, -$duracionClaseMinutos))
    {
        // Calcular inicio y fin del periodo actual
        $inicioPeriodoUnix = $horaUnixAAgregar;
        $finalPeriodoUnix = UnixTimeHelper::addMinutes($horaUnixAAgregar, $duracionClaseMinutos);

        // Preparar hora del siguiente periodo (sumando el recreo)
        $horaUnixAAgregar = UnixTimeHelper::addMinutes($finalPeriodoUnix, $duracionRecreoMinutos);

        // --------------------------------------------------------------------
        // Insertar el periodo actual en la tabla `periodo`
        // --------------------------------------------------------------------
        $inicioPeriodoSQL = UnixTimeHelper::toMySQLTime($inicioPeriodoUnix);
        $finalPeriodoSQL = UnixTimeHelper::toMySQLTime($finalPeriodoUnix);
        $sql = "INSERT INTO periodo(numero, entrada, salida)
                VALUES (?, ?, ?)";
        $result = SQL::actionQuery($con, $sql, "iss", $numeroPeriodo, $inicioPeriodoSQL, $finalPeriodoSQL);
        if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

        // Obtener el ID autogenerado del periodo recién insertado
        $idPeriodo = $con->insert_id;

        // --------------------------------------------------------------------
        // Por cada día disponible, crear una fila en la tabla `hora`
        // que vincule el día con este periodo.
        // --------------------------------------------------------------------
        foreach ($idDias as $idDia)
        {
            $sql = "INSERT INTO hora(id_periodo, id_dia)
                    VALUES (?, ?)";
            $result = SQL::actionQuery($con, $sql, "ii", $idPeriodo, $idDia);
            if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
        }

        // Avanzar al siguiente número de periodo
        $numeroPeriodo++;
    }
}

// ============================================================================
// FUNCIÓN: agregarPeriodoYHora
// ============================================================================
// Agrega un nuevo periodo al final del último existente,
// respetando las duraciones configuradas previamente.
function agregarPeriodoYHora(mysqli $con)
{
    // ------------------------------------------------------------------------
    // 1) OBTENER EL ÚLTIMO PERIODO EXISTENTE
    // ------------------------------------------------------------------------
    $sql = "SELECT
                UNIX_TIMESTAMP(salida) as salida_unix,
                numero
            FROM periodo
            ORDER BY numero DESC
            LIMIT 1";
    $result = SQL::valueQuery($con, $sql, "");
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    $periodo = $result->fetch_assoc();
    $horaUnixSalidaUltimoPeriodo = $periodo['salida_unix'];
    $numeroUltimoPeriodo = (int)$periodo['numero'];

    // ------------------------------------------------------------------------
    // 2) OBTENER TODAS LAS IDS DE LOS DÍAS
    // ------------------------------------------------------------------------
    $sql = "SELECT id_dia FROM dia";
    $result = SQL::valueQuery($con, $sql, "");
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    $idDias = [];
    while ($row = $result->fetch_assoc()) $idDias[] = $row['id_dia'];

    // ------------------------------------------------------------------------
    // 3) OBTENER LOS DATOS DE CONFIGURACIÓN DE GENERACIÓN DE PERIODOS
    // ------------------------------------------------------------------------
    $sql = "SELECT * FROM generacionperiodos";
    $result = SQL::valueQuery($con, $sql, "");
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    $generacionPeriodos = $result->fetch_assoc();
    $duracionClaseMinutos = $generacionPeriodos['duracion_clase_minutos'];
    $duracionRecreoMinutos = $generacionPeriodos['duracion_recreo_minutos'];

    // ------------------------------------------------------------------------
    // 4) CALCULAR HORAS DE INICIO Y FIN DEL NUEVO PERIODO
    // ------------------------------------------------------------------------
    $inicioPeriodoUnix = UnixTimeHelper::addMinutes($horaUnixSalidaUltimoPeriodo, $duracionRecreoMinutos);
    $finalPeriodoUnix = UnixTimeHelper::addMinutes($inicioPeriodoUnix, $duracionClaseMinutos);

    // ------------------------------------------------------------------------
    // 5) INSERTAR EL NUEVO PERIODO EN LA BASE DE DATOS
    // ------------------------------------------------------------------------
    $inicioPeriodoSQL = UnixTimeHelper::toMySQLTime($inicioPeriodoUnix);
    $finalPeriodoSQL = UnixTimeHelper::toMySQLTime($finalPeriodoUnix);
    $sql = "INSERT INTO periodo(numero, entrada, salida)
            VALUES (?, ?, ?)";
    $result = SQL::actionQuery($con, $sql, "iss", $numeroUltimoPeriodo+1, $inicioPeriodoSQL, $finalPeriodoSQL);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    $idPeriodo = $con->insert_id;

    // ------------------------------------------------------------------------
    // 6) CREAR REGISTROS EN `hora` PARA CADA DÍA
    // ------------------------------------------------------------------------
    foreach ($idDias as $idDia)
    {
        $sql = "INSERT INTO hora(id_periodo, id_dia)
                VALUES (?, ?)";
        $result = SQL::actionQuery($con, $sql, "ii", $idPeriodo, $idDia);
        if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    }
}

// ============================================================================
// MANEJO DE PETICIONES HTTP (POST / GET)
// ============================================================================

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    // Verificar que se haya especificado la acción
    if (!isset($_POST['accion'])) Respuestas::enviarError("ACCION_NO_ESPECIFICADA");
    $accion = $_POST['accion'];

    // Conectar a la base de datos e iniciar transacción
    $con = connectDb();
    $con->begin_transaction();

    // Ejecutar según la acción solicitada
    switch ($accion)
    {
        case 'agregar':
            // Agrega un nuevo periodo al final
            agregarPeriodoYHora($con);
            break;

        case 'regenerar':
            // Regenera todos los periodos desde cero
            $horaInicio = $_POST['hora_inicio'];
            $horaFinal = $_POST['hora_final'];
            $duracionRecreoMinutos = $_POST['duracion_recreo_minutos'];
            $duracionClaseMinutos = $_POST['duracion_clase_minutos'];

            // Validaciones de parámetros requeridos
            if (!isset($horaInicio)) Respuestas::enviarError('HORA_INICIO_NO_ESPECIFICADA', $con);
            if (!isset($horaFinal)) Respuestas::enviarError('HORA_FINAL_NO_ESPECIFICADA', $con);
            if (!isset($duracionRecreoMinutos)) Respuestas::enviarError('DURACION_RECREO_NO_ESPECIFICADO', $con);
            if (!isset($duracionClaseMinutos)) Respuestas::enviarError('DURACION_CLASE_NO_ESPECIFICADA', $con);

            regenerarPeriodosYHoras($con, $horaInicio, $horaFinal, $duracionRecreoMinutos, $duracionClaseMinutos);
            break;

        default:
            // Acción no reconocida
            Respuestas::enviarError('ACCION_INVALIDA', $con);
    }

    // Enviar respuesta de éxito
    Respuestas::enviarOk(null, $con);
}

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    // Conexión a la base de datos
    $con = connectDb();

    // Estructura base de la respuesta
    $respuesta = [
        "periodos" => [],
        "generacion_periodos" => null
    ];

    // ------------------------------------------------------------------------
    // 1) OBTENER TODOS LOS PERIODOS
    // ------------------------------------------------------------------------
    $sql = "SELECT * FROM periodo";
    $result = SQL::valueQuery($con, $sql, "");
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    // Recorrer los resultados y agregarlos a la respuesta
    while ($periodo = $result->fetch_assoc())
    {
        $respuesta['periodos'][] = $periodo;
    }

    // ------------------------------------------------------------------------
    // 2) OBTENER CONFIGURACIÓN DE GENERACIÓN DE PERIODOS
    // ------------------------------------------------------------------------
    $sql = "SELECT * FROM generacionperiodos";
    $result = SQL::valueQuery($con, $sql, "");
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    $generacionperiodos = $result->fetch_assoc();
    $respuesta['generacion_periodos'] = $generacionperiodos;

    // ------------------------------------------------------------------------
    // 3) ENVIAR RESPUESTA EXITOSA EN FORMATO ESTÁNDAR
    // ------------------------------------------------------------------------
    Respuestas::enviarOk($respuesta, $con);
}
?>

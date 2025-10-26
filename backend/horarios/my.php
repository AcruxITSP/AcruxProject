<?php
// ============================================================================
// INCLUSIÓN DE DEPENDENCIAS
// ============================================================================
// Se incluyen archivos con funciones auxiliares para conexión a la base de datos,
// ejecución de consultas SQL, manejo de tiempo, respuestas formateadas y errores.
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';     // Contiene la función connectDb() para conectar a la base de datos
require_once dirname(__FILE__).'/../other/sql.php';             // Funciones estáticas SQL::actionQuery y SQL::valueQuery para ejecutar consultas seguras
require_once dirname(__FILE__).'/../other/time.php';            // Herramientas para convertir y manipular tiempos
require_once dirname(__FILE__).'/../other/respuestas.php';      // Maneja respuestas en formato estándar (éxito o error)
require_once dirname(__FILE__).'/../other/db_errors.php';       // Contiene la clase ErrorDB para capturar errores SQL
require_once dirname(__FILE__).'/../util/timing.php';           // Herramientas para medir tiempos de ejecución (opcional)

// PUEDE TIRAR LOS CODIGOS DE ERROR:
// - NECESITA_LOGIN_PROFESOR
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if(!isset($_SESSION['id_profesor'])) Respuestas::enviarError("NECESITA_LOGIN_PROFESOR");
    $con = connectDb();

    $idProfesor = $_SESSION['id_profesor'];
    $idDia = obtenerNumeroDiaActual();
    $fechaActual = obtenerFechaActual();

    // Trae el periodo, espacio y profesor
    // ignorando los horarios donde el profesor falta.
    $sqlHorarios = "SELECT
                periodo.id_periodo,
                periodo.entrada,
                periodo.salida,
                espacio.tipo AS espacio_tipo,
                espacio.numero AS espacio_numero,
                usuario.nombre AS nombre_profesor,
                usuario.apellido AS apellido_profesor,
                CONCAT(grupo.grado, ' ', grupo.nombre) as nombre_grupo
            FROM modulo 
            JOIN clase ON modulo.id_clase = clase.id_clase
            JOIN profesor ON clase.id_profesor = profesor.id_profesor
            JOIN usuario ON profesor.id_usuario = usuario.id_usuario
            JOIN espacio ON modulo.id_espacio = espacio.id_espacio
            JOIN hora ON modulo.id_hora = hora.id_hora
            JOIN periodo ON hora.id_periodo = periodo.id_periodo
            JOIN grupo ON grupo.id_grupo = modulo.id_grupo
            WHERE profesor.id_profesor = ?
            AND hora.id_dia = ?
            AND periodo.id_periodo NOT IN (
                SELECT p.id_periodo 
                FROM Ausencia a 
                JOIN Ausencia_IntervaloAusencia aia ON a.id_ausencia = aia.id_ausencia
                JOIN IntervaloAusencia ia ON ia.id_intervalo_ausencia = aia.id_intervalo_ausencia
                JOIN Periodo p ON p.id_periodo BETWEEN ia.id_periodo_inicio
                AND ia.id_periodo_final 
                WHERE a.id_profesor = ? AND ia.fecha = ?
            );";
    $resultHorarios = SQL::valueQuery($con, $sqlHorarios, "iiis",
        $idProfesor, $idDia, $idProfesor, $fechaActual
    );
    if($resultHorarios instanceof ErrorDB) Respuestas::enviarError($resultHorarios, $con);
    
    // Reservas de espacios del profesor
    $sqlReservas = "SELECT
            periodo.id_periodo,
            espacio.numero as numero_espacio,
            espacio.tipo as tipo_espacio,
            espacio.id_espacio
            FROM reservaespacio, profesor, usuario, periodo, periodoreservaespacio, espacio
            WHERE reservaespacio.id_usuario = usuario.id_usuario
            AND profesor.id_usuario = usuario.id_usuario
            AND periodoreservaespacio.id_reserva = reservaespacio.id_reserva
            AND periodoreservaespacio.id_periodo = periodo.id_periodo
            AND reservaespacio.id_espacio = espacio.id_espacio
            AND profesor.id_profesor = ?
            AND reservaespacio.fecha = ?";
    $resultReservas = SQL::valueQuery($con, $sqlReservas, "is",
        $idProfesor, $fechaActual
    );
    if($resultReservas instanceof ErrorDB) Respuestas::enviarError($resultReservas, $con);
    $reservasPorIdPeriodo = [];
    while($reserva = $resultReservas->fetch_assoc()) $reservasPorIdPeriodo[$reserva['id_periodo']] = $reserva;

    $respuesta = [];
    while($horario = $resultHorarios->fetch_assoc())
    {
        // Si hay una reserva de este docente para este periodo. "sobreescribir"
        // el espacio para que se muestre el de la reserva y no el de la planilla.
        if(array_key_exists($horario['id_periodo'], $reservasPorIdPeriodo))
        {
            $reserva = $reservasPorIdPeriodo[$horario['id_periodo']];
            $horario['espacio_tipo'] = $reserva['tipo_espacio'];
            $horario['espacio_numero'] = $reserva['numero_espacio'];
        }

        $respuesta[] = $horario;
    }

    Respuestas::enviarOk($respuesta, $con);
}
?>

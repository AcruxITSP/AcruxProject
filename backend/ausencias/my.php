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

    // Obtener todas las ausencias del profesor que esta logeado.
    $sql = "SELECT *
            FROM ausencia, profesor
            WHERE ausencia.id_profesor = profesor.id_profesor
            AND profesor.id_profesor = ?";

    // Obtener 
    $sql = "SELECT
            intervaloausencia.id_periodo_inicio, intervaloausencia.id_periodo_final
            FROM ausencia, ausencia_intervaloausencia, intervaloausencia, profesor
            WHERE ausencia.id_profesor = profesor.id_profesor
            AND ausencia_intervaloausencia.id_ausencia = ausencia.id_ausencia
            AND ausencia_intervaloausencia.id_intervalo_ausencia = intervaloausencia.id_intervalo_ausencia
            AND intervaloausencia.fecha = '2025-10-21'
            AND profesor.id_profesor = ";

    // TODO: MORIR
    $resultPeriodoAusencias = SQL::valueQuery($con, $sql, "s", $fechaActual);
    if($resultPeriodoAusencias instanceof ErrorDB) Respuestas::enviarError($resultPeriodoAusencias, $con);

    $periodoAusencias;
}
?>

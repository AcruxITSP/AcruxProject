<?php
// Se incluyen los archivos necesarios que contienen funciones auxiliares, conexiones y manejo de errores
require_once dirname(__FILE__).'/../other/connection.php';     // conexión a la base de datos
require_once dirname(__FILE__).'/../other/sql.php';             // funciones para ejecutar consultas SQL
require_once dirname(__FILE__).'/../other/time.php';            // utilidades relacionadas con el tiempo/horas
require_once dirname(__FILE__).'/../other/respuestas.php';      // utilidades para enviar respuestas en formato estándar
require_once dirname(__FILE__).'/../other/db_errors.php';       // manejo de errores de base de datos
require_once dirname(__FILE__).'/../util/timing.php';           // utilidades de medición de tiempo
require_once dirname(__FILE__).'/../util/profes.php'; // funciones relacionadas con profesores

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $idProfesor = $_GET['id_profesor'];
    $numeroIntervalo = $_GET['numero_intervalo'];
    $nombreDia = $_GET['nombre_dia'];

    if(!isset($idProfesor)) Respuestas::enviarError('INDIQUE_ID_PROFESOR');
    if(!isset($nombreDia)) Respuestas::enviarError('INDIQUE_NOMBRE_DIA');
    if(!isset($numeroIntervalo)) Respuestas::enviarOk('INDIQUE_NUMERO_INTERVALO');

    $fecha = obtenerFechaActual();
    $idDia = obtenerNumeroDiaPorNombre($nombreDia);

    $con = connectDb();
    Respuestas::enviarOk(profeEstaAusente($con, $idProfesor, $idDia, $fecha, $numeroIntervalo));
}
?>

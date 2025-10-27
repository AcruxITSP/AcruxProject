<?php
// Se incluyen los archivos necesarios que contienen funciones auxiliares, conexiones y manejo de errores
require_once dirname(__FILE__).'/../other/connection.php';     // conexión a la base de datos
require_once dirname(__FILE__).'/../other/sql.php';             // funciones para ejecutar consultas SQL
require_once dirname(__FILE__).'/../other/time.php';            // utilidades relacionadas con el tiempo/horas
require_once dirname(__FILE__).'/../other/respuestas.php';      // utilidades para enviar respuestas en formato estándar
require_once dirname(__FILE__).'/../other/db_errors.php';       // manejo de errores de base de datos
require_once dirname(__FILE__).'/../util/timing.php';           // utilidades de medición de tiempo
require_once dirname(__FILE__).'/../util/reserva_recursos.php'; // funciones relacionadas con reservas de recursos

// CODIGOS DE ERROR:
// - FALTA_GRUPO
// - FALTA_DIA
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if(!isset($_POST['nombre_dia'])) Respuestas::enviarError("FALTA_DIA");
    if(!isset($_POST['id_grupo'])) Respuestas::enviarError("FALTA_GRUPO");

    $nombreDia = $_POST['nombre_dia'];
    $idGrupo = $_POST['id_grupo'];
    $idDia = obtenerNumeroDiaPorNombre($nombreDia);

    $con = connectDb();
    $con->begin_transaction();

    $sql = "DELETE FROM Modulo
            WHERE Modulo.id_modulo = (
                SELECT Modulo.id_modulo
                FROM Modulo, Hora, Grupo, Periodo
                WHERE Modulo.id_hora = Hora.id_hora
                AND Modulo.id_grupo = Grupo.id_grupo
                AND Hora.id_periodo = Periodo.id_periodo
                AND Hora.id_dia = ?
                AND Grupo.id_grupo = ?
                ORDER BY Periodo.numero DESC
                LIMIT 1
            )";
    
    $result = SQL::actionQuery($con, $sql, "ii", $idDia, $idGrupo);
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    Respuestas::enviarOk(null, $con);
}
?>

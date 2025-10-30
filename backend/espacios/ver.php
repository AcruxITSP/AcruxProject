<?php
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';
require_once dirname(__FILE__).'/../util/periodos.php';
require_once dirname(__FILE__).'/../util/reserva_recursos.php';
require_once dirname(__FILE__).'/../util/espacios.php';

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $con = connectDb();
    $respuesta = [];

    $sql = "SELECT * FROM espacio";
    $result = SQL::valueQuery($con, $sql, "");
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    while($espacio = $result->fetch_assoc())
    {
        $idEspacio = $espacio['id_espacio'];

        $respuestaEspacio = $espacio;
        $respuestaEspacio['disponibilidad'] = datosDisponibilidadDeEspacioHoyEn($con, $idEspacio);

        $respuesta[] = $respuestaEspacio;
    }
    
    Respuestas::enviarOk($respuesta);
}
?>
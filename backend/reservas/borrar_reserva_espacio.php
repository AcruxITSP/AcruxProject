<?php
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';
require_once dirname(__FILE__).'/../util/strings.php';
require_once dirname(__FILE__).'/../util/timing.php';


if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if(!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");
    $con = connectDb();

    $idReservaEspacio = $_POST['id_reserva_espacio'];
    $sql = "DELETE FROM reservaespacio WHERE id_reserva = ?";
    $result = SQL::actionQuery($con, $sql, "i", $idReservaEspacio);
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    Respuestas::enviarOk(null, $con);
}

?>
<?php
@session_start();
require_once dirname(__FILE__).'/../../utils/sql.php';
require_once dirname(__FILE__).'/../../utils/respuestas.php';
require_once dirname(__FILE__).'/../../db/connection.php';
require_once dirname(__FILE__).'/../../models/Hora.php';
require_once dirname(__FILE__).'/../_auth/roles.php';
require_once dirname(__FILE__).'/../_auth/redirects.php';

if($_SERVER['REQUEST_METHOD'] !== "POST")
{
    die();
}


$con = connectDb();
$idDia = 2;

$sql = "SELECT *
        FROM hora, intervalo, dia 
        WHERE dia.Id_dia = ? AND
        hora.Id_intervalo = intervalo.Id_intervalo AND
        hora.Id_dia = dia.Id_dia";
$getHorasResult = SQL::valueQuery($con, $sql, "i", $idDia);
if(!($getHorasResult instanceof mysqli_result)) Respuestas::enviarError($getHorasResult);

$horas = [];
while($hora = $getHorasResult->fetch_assoc())
{
    $horas[] = $hora;
}

Respuestas::enviarOk($horas);
?>
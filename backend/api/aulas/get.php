<?php
@session_start();
require_once dirname(__FILE__).'/../../utils/sql.php';
require_once dirname(__FILE__).'/../../utils/respuestas.php';
require_once dirname(__FILE__).'/../../db/connection.php';
require_once dirname(__FILE__).'/../../models/Aula.php';
require_once dirname(__FILE__).'/../_auth/roles.php';
require_once dirname(__FILE__).'/../_auth/redirects.php';

if($_SERVER['REQUEST_METHOD'] !== "POST")
{
    die();
}


$con = connectDb();


$getAulasResult = SQL::valueQuery($con, "SELECT * FROM aula", "");
if(!($getAulasResult instanceof mysqli_result)) Respuestas::enviarError($getAulasResult);

$aulas = [];
while($aula = $getAulasResult->fetch_assoc())
{
    $aulas[] = $aula;
}

Respuestas::enviarOk($aulas);
?>
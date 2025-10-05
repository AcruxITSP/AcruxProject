<?php
@session_start();
require_once dirname(__FILE__).'/../../utils/sql.php';
require_once dirname(__FILE__).'/../../utils/respuestas.php';
require_once dirname(__FILE__).'/../../utils/textNormalizer.php';
require_once dirname(__FILE__).'/../../utils/time.php';
require_once dirname(__FILE__).'/../../db/connection.php';
require_once dirname(__FILE__).'/../../models/Hora.php';
require_once dirname(__FILE__).'/../../models/Intervalo.php';
require_once dirname(__FILE__).'/../../models/Dia.php';
require_once dirname(__FILE__).'/../_auth/roles.php';
require_once dirname(__FILE__).'/../_auth/redirects.php';

enum CursoRegisterError : string
{
    case MISSING_PERMISSIONS = "MATERIA_REGISTER_MISSING_PERMISSIONS";
}

if($_SERVER['REQUEST_METHOD'] !== "POST")
{
    die();
}
// TODO: APLICAR LUEGO DE TERMINAR REGISTERS
// if(!isLoginAdscripta() && !isLoginAdministrador()) sendRedirectResponse("login.php");
$con = connectDb();

$idDias = [];
$getDiasResult = SQL::valueQuery($con, "SELECT * FROM dia", "");
if(!($getDiasResult instanceof mysqli_result)) Respuestas::enviarError($getDiasResult);
while($dia = $getDiasResult->fetch_assoc()) $idDias[] = $dia['Id_dia'];

$idIntervalos = [];
$getIntervalosResult = SQL::valueQuery($con, "SELECT * FROM intervalo", "");
if(!($getIntervalosResult instanceof mysqli_result)) Respuestas::enviarError($getIntervalosResult);
while($intervalo = $getIntervalosResult->fetch_assoc()) $idIntervalos[] = $intervalo['Id_intervalo'];

$deleteHorasResult = SQL::actionQuery($con, "DELETE FROM hora", "");
if($deleteHorasResult !== true) Respuestas::enviarError($deleteHorasResult);

foreach($idDias as $idDia)
{
    foreach($idIntervalos as $idIntervalo)
    {
        $createHoraResult = Hora::create($con, $idIntervalo, $idDia);
        if(!($createHoraResult instanceof Hora)) Respuestas::enviarError($createHoraResult);
    }
}

Respuestas::enviarOk();
?>
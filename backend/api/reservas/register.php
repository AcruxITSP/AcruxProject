<?php
@session_start();
require_once dirname(__FILE__).'/../../utils/sql.php';
require_once dirname(__FILE__).'/../../utils/respuestas.php';
require_once dirname(__FILE__).'/../../utils/textNormalizer.php';
require_once dirname(__FILE__).'/../../db/connection.php';
require_once dirname(__FILE__).'/../../models/Reserva.php';
require_once dirname(__FILE__).'/../_auth/roles.php';
require_once dirname(__FILE__).'/../_auth/redirects.php';

enum ReservaRegisterError : string
{
    case MISSING_PERMISSIONS = "RECURSOEXT_REGISTER_MISSING_PERMISSIONS";
}

if($_SERVER['REQUEST_METHOD'] !== "POST")
{
    die();
}

if(!isset($_SESSION['id_funcionario'])) sendRedirectResponse("login.php");

// TODO: APLICAR LUEGO DE TERMINAR REGISTERS
// if(!isLoginAdscripta() && !isLoginAdministrador()) sendRedirectResponse("login.php");
$con = connectDb();

// TODO: VALIDAR
$idHoraInicio = (int)($_POST['id_hora_inicio'] ?? 0);
$idHoraFinal = (int)($_POST['id_hora_final'] ?? 0);
$idAula = (int)($_POST['id_aula'] ?? 0);
$idFuncionario = (int)$_SESSION['id_funcionario'];
$fecha = date("'Y-m-d H:i:s'");

$createReservaResult = Reserva::create($con, $idHoraInicio, $idHoraFinal, $idAula, $idFuncionario, $fecha);
if(!($createReservaResult instanceof Reserva)) Respuestas::enviarError($createReservaResult);

Respuestas::enviarOk();
?>
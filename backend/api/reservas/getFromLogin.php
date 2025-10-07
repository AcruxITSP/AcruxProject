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
$id = $_SESSION['id_funcionario'];

$sql = "SELECT reserva.* FROM `reserva`, funcionario WHERE reserva.Id_funcionario = funcionario.Id_funcionario and funcionario.Id_funcionario = ?";
$result = SQL::valueQuery($con, $sql, "i", $id);
if(!($result instanceof mysqli_result)) Respuestas::enviarError($result);

$reservas = [];
while($reserva = $result->fetch_assoc())
{
    $reservas[] = $reserva;
}

Respuestas::enviarOk($reservas);
?>
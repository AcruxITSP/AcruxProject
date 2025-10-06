<?php
@session_start();
require_once dirname(__FILE__).'/../../utils/sql.php';
require_once dirname(__FILE__).'/../../utils/respuestas.php';
require_once dirname(__FILE__).'/../../utils/textNormalizer.php';
require_once dirname(__FILE__).'/../../db/connection.php';
require_once dirname(__FILE__).'/../../models/Clase.php';
require_once dirname(__FILE__).'/../_auth/roles.php';
require_once dirname(__FILE__).'/../_auth/redirects.php';

enum ClaseRegisterError : string
{
    case CANT_CHECK_IF_CLASE_EXISTS = "CLASE_REGISTER_CANT_CHECK_IF_CLASE_EXISTS";
    case CLASE_ALREADY_EXISTS = "CLASE_REGISTER_CLASE_ALREADY_EXISTS";
    case MISSING_PERMISSIONS = "CLASE_REGISTER_MISSING_PERMISSIONS";
}

if($_SERVER['REQUEST_METHOD'] !== "POST")
{
    die();
}
// TODO: APLICAR LUEGO DE TERMINAR REGISTERS
// if(!isLoginAdscripta() && !isLoginAdministrador()) sendRedirectResponse("login.php");
$con = connectDb();

// TODO: VALIDAR
$idProfesor = (int)($_POST['id_profesor'] ?? 0);
$idMateria = (int)($_POST['id_materia'] ?? 0);

$checkClaseSql = "SELECT COUNT(Id_clase) as count FROM clase WHERE Id_profesor = ? AND Id_materia = ?";
$checkClaseResult = SQL::valueQuery($con, $checkClaseSql, "ii", $idProfesor, $idMateria);
if($checkClaseResult instanceof ErrorDB) Respuestas::enviarError(new ErrorBase(ClaseRegisterError::CANT_CHECK_IF_CLASE_EXISTS, $checkClaseResult));
if((int)$checkClaseResult->fetch_assoc()['count'] > 0) Respuestas::enviarError(new ErrorBase(ClaseRegisterError::CLASE_ALREADY_EXISTS, null));

$createClaseResult = Clase::create($con, $idProfesor, $idMateria);
if(!($createClaseResult instanceof Clase)) Respuestas::enviarError($createClaseResult);

Respuestas::enviarOk($createClaseResult);
?>
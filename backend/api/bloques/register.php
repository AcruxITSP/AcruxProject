<?php
@session_start();
require_once dirname(__FILE__).'/../../utils/sql.php';
require_once dirname(__FILE__).'/../../utils/respuestas.php';
require_once dirname(__FILE__).'/../../utils/textNormalizer.php';
require_once dirname(__FILE__).'/../../db/connection.php';
require_once dirname(__FILE__).'/../../models/Bloque.php';
require_once dirname(__FILE__).'/../_auth/roles.php';
require_once dirname(__FILE__).'/../_auth/redirects.php';

enum BloqueRegisterError : string
{
    case CANT_CHECK_IF_BLOQUE_EXISTS = "BLOQUE_REGISTER_CANT_CHECK_IF_BLOQUE_EXISTS";
    case BLOQUE_ALREADY_EXISTS = "BLOQUE_REGISTER_BLOQUE_ALREADY_EXISTS";
    case MISSING_PERMISSIONS = "BLOQUE_REGISTER_MISSING_PERMISSIONS";
}

if($_SERVER['REQUEST_METHOD'] !== "POST")
{
    die();
}
// TODO: APLICAR LUEGO DE TERMINAR REGISTERS
// if(!isLoginAdscripta() && !isLoginAdministrador()) sendRedirectResponse("login.php");
$con = connectDb();

// TODO: VALIDAR
$idGrupo = (int)($_POST['id_grupo'] ?? 0);
$idClase = (int)($_POST['id_clase'] ?? 0);
$idAula = (int)($_POST['id_aula'] ?? 0);
$idHora = (int)($_POST['id_hora'] ?? 0);

// TODO: Nehuel se encarga de revisar las reglas de UNIQUE para la tabla.
/*
$checkClaseSql = "SELECT COUNT(Id_clase) as count FROM clase WHERE Id_profesor = ? AND Id_materia = ?";
$checkClaseResult = SQL::valueQuery($con, $checkClaseSql, "ii", $idProfesor, $idMateria);
if($checkClaseResult instanceof ErrorDB) Respuestas::enviarError(new ErrorBase(ClaseRegisterError::CANT_CHECK_IF_CLASE_EXISTS, null));
if((int)$checkClaseResult->fetch_assoc()['count'] > 0) Respuestas::enviarError(new ErrorBase(ClaseRegisterError::CLASE_ALREADY_EXISTS, null));
*/

$createBloqueResult = Bloque::create($con, $idGrupo, $idClase, $idAula, $idHora);
if(!($createBloqueResult instanceof Bloque)) Respuestas::enviarError($createBloqueResult);

Respuestas::enviarOk($createBloqueResult);
?>
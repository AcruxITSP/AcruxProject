<?php
@session_start();
require_once dirname(__FILE__).'/../../utils/sql.php';
require_once dirname(__FILE__).'/../../utils/respuestas.php';
require_once dirname(__FILE__).'/../../utils/textNormalizer.php';
require_once dirname(__FILE__).'/../../db/connection.php';
require_once dirname(__FILE__).'/../../models/Grupo.php';
require_once dirname(__FILE__).'/../_auth/roles.php';
require_once dirname(__FILE__).'/../_auth/redirects.php';

enum GrupoRegisterError : string
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

// TODO: VALIDAR
$codigo = $_POST['codigo'] ?? null;
$codigo = normalizeGroupCode($codigo);
$idCurso = $_POST['id_curso'] ?? null;
$idAdscripta = $_POST['id_adscripta'] ?? null;

$createGrupoResult = Grupo::create($con, $codigo, $idAdscripta, $idCurso);
if(!($createGrupoResult instanceof Grupo)) Respuestas::enviarError($createGrupoResult);

Respuestas::enviarOk();
?>
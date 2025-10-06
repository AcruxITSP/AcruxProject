<?php
@session_start();
require_once dirname(__FILE__).'/../../utils/sql.php';
require_once dirname(__FILE__).'/../../utils/respuestas.php';
require_once dirname(__FILE__).'/../../utils/normalizeTitle.php';
require_once dirname(__FILE__).'/../../db/connection.php';
require_once dirname(__FILE__).'/../../models/Materia.php';
require_once dirname(__FILE__).'/../_auth/roles.php';
require_once dirname(__FILE__).'/../_auth/redirects.php';

enum MateriaRegisterError : string
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
$nombre = $_POST['nombre'] ?? null;
$nombre = normalizeTitle($nombre);

$createMateriaResult = Materia::create($con, $nombre);
if(!($createMateriaResult instanceof Materia)) Respuestas::enviarError($createMateriaResult);

Respuestas::enviarOk();
?>
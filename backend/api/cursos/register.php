<?php
@session_start();
require_once dirname(__FILE__).'/../../utils/sql.php';
require_once dirname(__FILE__).'/../../utils/respuestas.php';
require_once dirname(__FILE__).'/../../utils/normalizeTitle.php';
require_once dirname(__FILE__).'/../../db/connection.php';
require_once dirname(__FILE__).'/../../models/Curso.php';
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
// if(!isLoginAdscripta() && !isLoginAdministrador()) sendRedirectResponse("login.php");
$con = connectDb();

$nombre = $_POST['nombre'] ?? null;
$nombre = normalizeTitle($nombre);
$duracionAnios = (int)($_POST['duracionAnios'] ?? 3);

$createCursoResult = Curso::create($con, $nombre, $duracionAnios);
if(!($createCursoResult instanceof Curso)) Respuestas::enviarError($createCursoResult);

Respuestas::enviarOk();
?>
<?php
@session_start();
require_once dirname(__FILE__).'/../../utils/sql.php';
require_once dirname(__FILE__).'/../../utils/respuestas.php';
require_once dirname(__FILE__).'/../../db/connection.php';
require_once dirname(__FILE__).'/../../models/Aula.php';
require_once dirname(__FILE__).'/../_auth/roles.php';
require_once dirname(__FILE__).'/../_auth/redirects.php';

enum AulaRegisterError : string
{
    case MISSING_PERMISSIONS = "AULA_REGISTER_MISSING_PERMISSIONS";
}

if($_SERVER['REQUEST_METHOD'] !== "POST")
{
    die();
}
// if(!isLoginAdscripta() && !isLoginAdministrador()) sendRedirectResponse("login.php");
$con = connectDb();

$codigo = $_POST['codigo'] ?? null;
$piso = $_POST['codigo'] ?? null;
$proposito = $_POST['proposito'] ?? null;
$cantidadSillas = $_POST['cantidadSillas'] ?? null;

$createAultaResult = Aula::create($con, $codigo, $piso, $proposito, $cantidadSillas);
if(!($createAultaResult instanceof Aula)) Respuestas::enviarError($createAultaResult);

Respuestas::enviarOk();
?>
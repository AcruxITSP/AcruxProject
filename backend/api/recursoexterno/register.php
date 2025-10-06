<?php
@session_start();
require_once dirname(__FILE__).'/../../utils/sql.php';
require_once dirname(__FILE__).'/../../utils/respuestas.php';
require_once dirname(__FILE__).'/../../utils/textNormalizer.php';
require_once dirname(__FILE__).'/../../db/connection.php';
require_once dirname(__FILE__).'/../../models/RecursoExterno.php';
require_once dirname(__FILE__).'/../_auth/roles.php';
require_once dirname(__FILE__).'/../_auth/redirects.php';

enum RecursoExtRegisterError : string
{
    case MISSING_PERMISSIONS = "RECURSOEXT_REGISTER_MISSING_PERMISSIONS";
}

if($_SERVER['REQUEST_METHOD'] !== "POST")
{
    die();
}

// TODO: APLICAR LUEGO DE TERMINAR REGISTERS
// if(!isLoginAdscripta() && !isLoginAdministrador()) sendRedirectResponse("login.php");
$con = connectDb();

// TODO: VALIDAR
$tipo = $_POST['tipo'] ?? null;
$tipo = normalizeTitle($tipo);
$cantidad = (int)($_POST['cantidad'] ?? 0);

for($i = 0; $i < $cantidad; ++$i)
{
    $createRecursoResult = RecursoExterno::create($con, $tipo, 1, disponible:true);
    if(!($createRecursoResult instanceof RecursoExterno)) Respuestas::enviarError($createRecursoResult);
}

Respuestas::enviarOk();
?>
<?php
@session_start();
require_once dirname(__FILE__).'/../../utils/sql.php';
require_once dirname(__FILE__).'/../../utils/respuestas.php';
require_once dirname(__FILE__).'/../../utils/textNormalizer.php';
require_once dirname(__FILE__).'/../../db/connection.php';
require_once dirname(__FILE__).'/../../models/RecursoInterno.php';
require_once dirname(__FILE__).'/../_auth/roles.php';
require_once dirname(__FILE__).'/../_auth/redirects.php';

enum RecursoIntRegisterError : string
{
    case MISSING_PERMISSIONS = "RECURSOINT_REGISTER_MISSING_PERMISSIONS";
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
$idAula = (int)($_POST['id_aula'] ?? 0);
$cantidad = $_POST['cantidad'] ?? null;
if($cantidad !== null) $cantidad = (int)$cantidad;

for($i = 0; $i < $cantidad; ++$i)
{
    $createRecursoResult = RecursoInterno::create($con, $tipo, $idAula);
    if(!($createRecursoResult instanceof RecursoInterno)) Respuestas::enviarError($createRecursoResult);
}

Respuestas::enviarOk();
?> 
<?php
@session_start();
require_once dirname(__FILE__).'/../../utils/sql.php';
require_once dirname(__FILE__).'/../../utils/respuestas.php';
require_once dirname(__FILE__).'/../../utils/textNormalizer.php';
require_once dirname(__FILE__).'/../../utils/time.php';
require_once dirname(__FILE__).'/../../db/connection.php';
require_once dirname(__FILE__).'/../../models/Telefono_Persona.php';
require_once dirname(__FILE__).'/../_auth/roles.php';
require_once dirname(__FILE__).'/../_auth/redirects.php';


if($_SERVER['REQUEST_METHOD'] === "POST")
{
    // TODO: APLICAR LUEGO DE TERMINAR REGISTERS
    // if(!isLoginAdscripta() && !isLoginAdministrador()) sendRedirectResponse("login.php");

    $telefono = $_POST['telefono'];
    $idPersona = (int)($_POST['id_persona'] ?? 0);
    $result = registrarTelefonoPersona($telefono, $idPersona);

    if($result === true) Respuestas::enviarOk($result);
    else Respuestas::enviarError($result);
}

function registrarTelefonoPersona(string $telefono, int $idPersona): bool|ErrorDB|TelefonoPersonaError
{
    $con = connectDb();
    $createTelefonoPersonaResult = TelefonoPersona::create($con, $telefono, $idPersona);
    if(!($createTelefonoPersonaResult instanceof TelefonoPersona)) return $createTelefonoPersonaResult;
    return true;
}




?>
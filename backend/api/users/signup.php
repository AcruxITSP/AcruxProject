<?php
@session_start();
require_once dirname(__FILE__).'/../../utils/sql.php';
require_once dirname(__FILE__).'/../../utils/respuestas.php';
require_once dirname(__FILE__).'/../../db/connection.php';
require_once dirname(__FILE__).'/../../models/Persona.php';
require_once dirname(__FILE__).'/../../models/Funcionario.php';
require_once dirname(__FILE__).'/../../models/Adscripta.php';
require_once dirname(__FILE__).'/../../models/Auxiliar.php';
require_once dirname(__FILE__).'/../../models/Auxiliar_Cargo.php';
require_once dirname(__FILE__).'/../../models/Estudiante.php';
require_once dirname(__FILE__).'/../../models/Secretario.php';
require_once dirname(__FILE__).'/../../models/Administrador.php';
require_once dirname(__FILE__).'/../../models/Telefono_Persona.php';

enum SignupError : string
{
    case INVALID_ROLE = "SIGNUP_INVALID_ROLE";
}

if($_SERVER['REQUEST_METHOD'] !== "POST")
{
    die();
}

$con = connectDb();
$nombre = $_POST["nombre"] ?? null;
$apellido = $_POST["apellido"] ?? null;
$dni = $_POST["dni"] ?? null;
$email = $_POST["email"] ?? null;
$contrasena = $_POST["contrasena"] ?? null;
$rol = $_POST["rol"] ?? null;
$telefonos = json_decode($_POST["telefonos"] ?? "{}");
$contrasenaHash = password_hash($contrasena, PASSWORD_BCRYPT);

$usuario = null;
switch($rol)
{
    case "administrador": $usuario = crearAdministrador(); break;
    case "adscripta": $usuario = crearAdscripta(); break;
    case "auxiliar": $usuario = crearAuxiliar(); break;
    case "estudiante": $usuario = crearEstudiante(); break;
    case "profesor": $usuario = crearProfesor(); break;
    case "secretario": $usuario = crearSecretario(); break;
    default: Respuestas::enviarError(new ErrorBase(SignupError::INVALID_ROLE, $rol));
}
Respuestas::enviarOk($usuario);

function crearPersona() : Persona
{
    global $con;
    global $nombre;
    global $apellido;
    global $dni;
    global $email;
    global $contrasenaHash;
    global $telefonos;
    $createPersonaResult = Persona::create($con, $nombre, $apellido, $dni, $email, $contrasenaHash);
    if(!($createPersonaResult instanceof Persona)) Respuestas::enviarError($createPersonaResult);
    $persona = $createPersonaResult;

    foreach($telefonos as $telefono)
    {
        $createTelefonoPersonaResult = TelefonoPersona::create($con, $telefono, $persona->idPersona);
        if(!($createTelefonoPersonaResult instanceof TelefonoPersona)) Respuestas::enviarError($createTelefonoPersonaResult);
    }

    return $persona;
}

function crearFuncionario() : Funcionario
{
    global $con;
    $persona = crearPersona();
    $createFuncionarioResult = Funcionario::create($con, $persona->idPersona);
    if(!($createFuncionarioResult instanceof Funcionario)) Respuestas::enviarError($createFuncionarioResult);
    return $createFuncionarioResult;
}

function crearAdministrador() : Administrador
{
    global $con;
    $funcionario = crearFuncionario();
    $createAdministradorResult = Administrador::create($con, $funcionario->idFuncionario);
    if(!($createAdministradorResult instanceof Administrador)) Respuestas::enviarError($createAdministradorResult);
    return $createAdministradorResult;
}

function crearAdscripta() : Adscripta
{
    global $con;
    $funcionario = crearFuncionario();
    $createAdscriptaResult = Adscripta::create($con, $funcionario->idFuncionario);
    if(!($createAdscriptaResult instanceof Adscripta)) Respuestas::enviarError($createAdscriptaResult);
    return $createAdscriptaResult;
}

function crearAuxiliar() : Auxiliar
{
    global $con;
    $funcionario = crearFuncionario();

    // TODO: Validar
    $idCargos = json_decode($_POST['id_cargos']);

    $createAuxiliarResult = Auxiliar::create($con, $funcionario->idFuncionario);
    if(!($createAuxiliarResult instanceof Auxiliar)) Respuestas::enviarError($createAuxiliarResult);
    $auxiliar = $createAuxiliarResult;
    $idAuxiliar = $auxiliar->idAuxiliar;

    foreach($idCargos as $idCargo)
    {
        $createAuxiliarCargoResult = AuxiliarCargo::create($con, $idAuxiliar, $idCargo);
        if(!($createAuxiliarCargoResult instanceof AuxiliarCargo)) Respuestas::enviarError($createAuxiliarCargoResult);
    }

    return $createAuxiliarResult;
}

function crearEstudiante() : Estudiante
{
    global $con;
    $persona = crearPersona();

    // TODO: Validar
    $idGrupo = $_POST['id_grupo'];

    $createEstudianteResult = Estudiante::create($con, $idGrupo, $persona->idPersona);
    if(!($createEstudianteResult instanceof Estudiante)) Respuestas::enviarError($createEstudianteResult);
    $estudiante = $createEstudianteResult;
    return $estudiante;
}

function crearProfesor() : Profesor
{
    global $con;
    $funcionario = crearFuncionario();

    // TODO: Validar
    $fechaIngreso = date("Y-m-d");

    $createProfesorResult = Profesor::create($con, $fechaIngreso, $funcionario->idFuncionario);
    if(!($createProfesorResult instanceof Profesor)) Respuestas::enviarError($createProfesorResult);
    $profesor = $createProfesorResult;
    return $profesor;
}

function crearSecretario() : Secretario
{
    global $con;
    $funcionario = crearFuncionario();

    $createSecretarioResult = Secretario::create($con, $funcionario->idFuncionario);
    if(!($createSecretarioResult instanceof Secretario)) Respuestas::enviarError($createSecretarioResult);
    $secretario = $createSecretarioResult;
    return $secretario;
}
?>
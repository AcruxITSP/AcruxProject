<?php
session_start();
include_once '../../utils/sql.php';
include_once '../../utils/respuestas.php';
include_once '../../db/connection.php';
include_once '../../models/Persona.php';
include_once '../../models/Funcionario.php';

enum LoginError : string
{
    case INVALID_PASSWORD = "LOGIN_INVALID_PASSWORD";
}

if($_SERVER['REQUEST_METHOD'] !== "POST")
{
    die();
}

$con = connectDb();
$ci = $_POST["ci"] ?? null;
$contrasena = $_POST["contrasena"] ?? null;

// Obtener Persona
$getPersonaResult = Persona::getByDni($con, $ci);
if(!($getPersonaResult instanceof Persona)) Respuestas::enviarError($getPersonaResult);
$persona = $getPersonaResult;

// Validar contrasena
if(!password_verify($persona->contrasena, PASSWORD_BCRYPT))
{
    Respuestas::enviarError(LoginError::INVALID_PASSWORD);
}

// Guardar persona
$_SESSION["id_persona"] = $persona->idPersona;
$_SESSION["nombre"] = $persona->nombre;
$_SESSION["apellido"] = $persona->apellido;
$_SESSION["dni"] = $persona->dni;
$_SESSION["email"] = $persona->email;
$_SESSION["contrasenaHash"] = $persona->contrasena;
$_SESSION["rol"] = null;

// Obtener y guardar Funcionario (si aplica)
$sql = "SELECT Funcionario.* 
        FROM Funcionario, Persona 
        WHERE Persona.DNI = ?
        AND Persona.Id_persona = Funcionario.Id_persona";
$getFuncionarioResult = SQL::valueQuery($con, $sql, "s", $ci);
if(!($getFuncionarioResult instanceof mysqli_result)) Respuestas::enviarError($getFuncionarioResult);
if($getFuncionarioResult->num_rows != 0)
{
    $funcionario = $getFuncionarioResult->fetch_assoc();
    $_SESSION["id_funcionario"] = $funcionario['Id_funcionario'];
}

// Obtener y guardar Administrador (si aplica)
$sql = "SELECT Administrador.*
        FROM Administrador, Funcionario, Persona
        WHERE Persona.DNI = ? 
        AND Persona.Id_persona = Funcionario.Id_persona 
        AND Funcionario.Id_funcionario = Administrador.Id_funcionario";
$getAdministradorResult = SQL::valueQuery($con, $sql, "s", $ci);
if(!($getAdministradorResult instanceof mysqli_result)) Respuestas::enviarError($getAdministradorResult);
if($getAdministradorResult->num_rows != 0)
{
    $administrador = $getAdministradorResult->fetch_assoc();
    $_SESSION["Id_administrador"] = $funcionario['Id_administrador'];
    $_SESSION["rol"] = "administrador";
}

// Obtener y guardar Adscripta (si aplica)
$sql = "SELECT Adscripta.*
        FROM Adscripta, Funcionario, Persona
        WHERE Persona.DNI = ? 
        AND Persona.Id_persona = Funcionario.Id_persona 
        AND Funcionario.Id_funcionario = Adscripta.Id_funcionario";
$getAdscriptaResult = SQL::valueQuery($con, $sql, "s", $ci);
if(!($getAdscriptaResult instanceof mysqli_result)) Respuestas::enviarError($getAdscriptaResult);
if($getAdscriptaResult->num_rows != 0)
{
    $adscripta = $getAdscriptaResult->fetch_assoc();
    $_SESSION["Id_adscripta"] = $funcionario['Id_adscripta'];
    $_SESSION["rol"] = "adscripta";
}

// Obtener y guardar Auxiliar (si aplica)
$sql = "SELECT Auxiliar.*
        FROM Auxiliar, Funcionario, Persona
        WHERE Persona.DNI = ? 
        AND Persona.Id_persona = Funcionario.Id_persona 
        AND Funcionario.Id_funcionario = Auxiliar.Id_funcionario";
$getAuxiliarResult = SQL::valueQuery($con, $sql, "s", $ci);
if(!($getAuxiliarResult instanceof mysqli_result)) Respuestas::enviarError($getAuxiliarResult);
if($getAuxiliarResult->num_rows != 0)
{
    $auxiliar = $getAuxiliarResult->fetch_assoc();
    $_SESSION["Id_auxiliar"] = $funcionario['Id_auxiliar'];
    $_SESSION["rol"] = "auxiliar";
}

// Obtener y guardar Profesor (si aplica)
$sql = "SELECT Profesor.*
        FROM Auxiliar, Funcionario, Persona
        WHERE Persona.DNI = ? 
        AND Persona.Id_persona = Funcionario.Id_persona 
        AND Funcionario.Id_funcionario = Profesor.Id_funcionario";
$getProfesorResult = SQL::valueQuery($con, $sql, "s", $ci);
if(!($getProfesorResult instanceof mysqli_result)) Respuestas::enviarError($getProfesorResult);
if($getProfesorResult->num_rows != 0)
{
    $profesor = $getProfesorResult->fetch_assoc();
    $_SESSION["Id_profesor"] = $funcionario['Id_profesor'];
    $_SESSION["rol"] = "profesor";
}

// Obtener y guardar Secretario (si aplica)
$sql = "SELECT Secretario.*
        FROM Secretario, Funcionario, Persona
        WHERE Persona.DNI = ? 
        AND Persona.Id_persona = Funcionario.Id_persona 
        AND Funcionario.Id_funcionario = Secretario.Id_funcionario";
$getSecretarioResult = SQL::valueQuery($con, $sql, "s", $ci);
if(!($getSecretarioResult instanceof mysqli_result)) Respuestas::enviarError($getSecretarioResult);
if($getSecretarioResult->num_rows != 0)
{
    $secretario = $getSecretarioResult->fetch_assoc();
    $_SESSION["Id_secretario"] = $funcionario['Id_secretario'];
    $_SESSION["rol"] = "secretario";
}

// Obtener y guardar Estudiante (si aplica)
$sql = "SELECT Estudiante.*
        FROM Estudiante, Persona
        WHERE Persona.DNI = ? 
        AND Persona.Id_persona = Estudiante.Id_funcionario";
$getEstudianteResult = SQL::valueQuery($con, $sql, "s", $ci);
if(!($getEstudianteResult instanceof mysqli_result)) Respuestas::enviarError($getEstudianteResult);
if($getEstudianteResult->num_rows != 0)
{
    $estudiante = $getEstudianteResult->fetch_assoc();
    $_SESSION["Id_estudiante"] = $funcionario['Id_estudiante'];
    $_SESSION["rol"] = "estudiante";
}
?>
<?php
session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';
require_once dirname(__FILE__).'/../util/timing.php';
require_once dirname(__FILE__).'/../util/reserva_recursos.php';

// - CI_NO_INGRESADA
// - CONTRASENA_NO_INGRESADA
// - USUARIO_NO_ENCONTRADO
// - CONTRASENA_INVALIDA
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    // Validar que la ci este definida
    if(!isset($_POST['ci'])) Respuestas::enviarError("CI_NO_INGRESADA");
    $ci = $_POST['ci'];

    // Validar que la contrasena este definida
    if(!isset($_POST['password'])) Respuestas::enviarError("CONTRASENA_NO_INGRESADA");
    $password = $_POST['password'];

    // Conectarme a la base de datos
    $con = connectDb();

    // Obtener usuario

    $sql = "SELECT * FROM Usuario WHERE ci = ?";    // Consulta para obtener el usuario por la CI
    $result = SQL::valueQuery($con, $sql, "s", $ci); // Ejecutar la consulta
    if($result instanceof ErrorDB) Respuestas::enviarError($result); // Si la consula falla, terminar el php y enviar error

    // Obtener los datos del usuario en un arreglo assosiativo
    $usuario = $result->fetch_assoc();
    // Si no se pudieron obtener los datos es porque no se econtro el usuario con esa ci.
    if($usuario == null) Respuestas::enviarError("USUARIO_NO_ENCONTRADO");
    // Verificar que la contrasena ingresada sea la correcta.
    if(!password_verify($password, $usuario["contrasena"])
        && $usuario["contrasena"] != $password) Respuestas::enviarError(valor: "CONTRASENA_INVALIDA");

    // Guardar los datos del usuario en la sesion.
    $idUsuario = $usuario['id_usuario'];
    $_SESSION['id_usuario'] = $idUsuario;
    $_SESSION['ci'] = $usuario['ci'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['apellido'] = $usuario['apellido'];
    $_SESSION['username'] = $usuario['nombre']. ". ". $usuario['apellido'];
    $_SESSION['hash_contrasena'] = $usuario['contrasena'];
    $_SESSION['email'] = $usuario['email'];
    $_SESSION['rol'] = null;

    // Guardar datos especificos del usuario como si fuera un adscipto, solo si aplica el rol.
    $usuarioEspecificoCargado = cargarAdscriptoSiAplica($con, $idUsuario);
    // Si el usuario no era Adscripto, Guardar datos especificos del usuario como si fuera un profesor, solo si aplica el rol
    $usuarioEspecificoCargado ??= cargarProfesorSiAplica($con, $idUsuario);

    // Enviar que todo fue bien y pasamos los datos de la sesion.
    Respuestas::enviarOk(["session" => $_SESSION]);
}

function cargarAdscriptoSiAplica($con, $idUsuario) : true|null
{
    $sql = "SELECT * FROM Adscrito WHERE id_usuario = ?";
    $result = SQL::valueQuery($con, $sql, "i", $idUsuario);
    if($result instanceof ErrorDB) Respuestas::enviarError($result);

    $adscripto = $result->fetch_assoc();
    if($adscripto)
    {
        $_SESSION['id_adscripto'] = $adscripto['id_adscrito'];
        $_SESSION['rol'] = 'adscripto';
        return true;
    }

    return null;
}

function cargarProfesorSiAplica($con, $idUsuario) : true|null
{
    $sql = "SELECT * FROM Profesor WHERE id_usuario = ?";
    $result = SQL::valueQuery($con, $sql, "i", $idUsuario);
    if($result instanceof ErrorDB) Respuestas::enviarError($result);

    $profesor = $result->fetch_assoc();
    if($profesor)
    {
        $_SESSION['id_profesor'] = $profesor['id_profesor'];
        $_SESSION['rol'] = 'profesor';
        return true;
    }

    return null;
}

?>
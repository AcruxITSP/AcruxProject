<?php
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';
require_once dirname(__FILE__).'/../util/strings.php';

// PUEDE TIRAR LOS CODIGOS DE ERROR:
// - NECESITA_LOGIN
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if(!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");

    if(!isset($_POST['ci'])) Respuestas::enviarError("NECESITA_CI");
    if(!isset($_POST['nombre'])) Respuestas::enviarError("NECESITA_NOMBRE");
    if(!isset($_POST['apellido'])) Respuestas::enviarError("NECESITA_APELLIDO");
    if(!isset($_POST['contrasena'])) Respuestas::enviarError("NECESITA_CONTRASENA");
    if(!isset($_POST['email'])) Respuestas::enviarError("NECESITA_EMAIL");

    $ci = $_POST['ci'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $contrasena = $_POST['contrasena'];
    $email = $_POST['email'];

    $con = connectDb();
    $con->begin_transaction();

    $sql = "INSERT INTO Usuario(ci, nombre, apellido, contrasena, email) VALUES (?, ?, ?, ?, ?)";
    $result = SQL::actionQuery($con, $sql, "sssss", $ci, $nombre, $apellido, $contrasena, $email);
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    Respuestas::enviarOk(null, $con);
}
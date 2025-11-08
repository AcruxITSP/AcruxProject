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

    if(!isset($_POST['grado'])) Respuestas::enviarError("NECESITA_GRADO");
    if(!isset($_POST['nombre'])) Respuestas::enviarError("NECESITA_NOMBRE");
    if(!isset($_POST['id_curso'])) Respuestas::enviarError("NECESITA_ID_CURSO");
    if(!isset($_POST['id_adscrito'])) Respuestas::enviarError("NECESITA_ID_ADSCRITO");

    $grado = $_POST['grado'];
    $nombre = $_POST['nombre'];
    $id_curso = $_POST['id_curso'];
    $id_adscrito = $_POST['id_adscrito'];

    $con = connectDb();
    $con->begin_transaction();

    $sql = "INSERT INTO Grupo(grado, nombre, id_curso, id_adscrito) VALUES (?, ?, ?, ?)";
    $result = SQL::actionQuery($con, $sql, "ssii", $grado, $nombre, $id_curso, $id_adscrito);
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    Respuestas::enviarOk(null, $con);
}
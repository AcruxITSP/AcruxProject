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

    if(!isset($_POST['tipo'])) Respuestas::enviarError("NECESITA_TIPO");
    if(!isset($_POST['numero'])) Respuestas::enviarError("NECESITA_NUMERO");
    if(!isset($_POST['capacidad'])) Respuestas::enviarError("NECESITA_CAPACIDAD");
    if(!isset($_POST['ubicacion'])) Respuestas::enviarError("NECESITA_UBICACION");

    $tipo = $_POST['tipo'];
    $numero = $_POST['numero'];
    $capacidad = $_POST['capacidad'];
    $ubicacion = $_POST['ubicacion'];

    $con = connectDb();
    $con->begin_transaction();

    $sql = "INSERT INTO Espacio(tipo, numero, capacidad, ubicacion) VALUES (?, ?, ?, ?)";
    $result = SQL::actionQuery($con, $sql, "siis", $tipo, $numero, $capacidad, $ubicacion);
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    Respuestas::enviarOk(null, $con);
}
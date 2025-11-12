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
// - NECESITA_TIPO
// - NECESITA_NUMERO
// - NECESITA_CAPACIDAD
// - NECESITA_UBICACION
// - TIPO_NUMERO_REPETIDO
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if(!isset($_SESSION['id_usuario']))
        Respuestas::enviarError("NECESITA_LOGIN");

    if(!isset($_POST['tipo']))
        Respuestas::enviarError("NECESITA_TIPO");

    if(!isset($_POST['numero']))
        Respuestas::enviarError("NECESITA_NUMERO");

    if(!isset($_POST['capacidad']))
        Respuestas::enviarError("NECESITA_CAPACIDAD");

    if(!isset($_POST['ubicacion']))
        Respuestas::enviarError("NECESITA_UBICACION");

    $tipo = $_POST['tipo'];
    $numero = $_POST['numero'];
    if($numero == '') $numero = null;
    $capacidad = $_POST['capacidad'];
    $ubicacion = $_POST['ubicacion'];

    $con = connectDb();
    $con->begin_transaction();

    // Verificar que no exista un espacio con el mismo tipo y número
    $sqlCheck = "SELECT COUNT(*) AS total FROM Espacio WHERE tipo = ? AND numero <=> ?";
    $dup = SQL::valueQuery($con, $sqlCheck, "ss", $tipo, $numero);
    if($dup instanceof ErrorDB)
        Respuestas::enviarError($dup, $con);

    if($dup->fetch_assoc()['total'] > 0)
        Respuestas::enviarError("TIPO_NUMERO_REPETIDO", $con);

    // Insertar nuevo espacio
    $sql = "INSERT INTO Espacio(tipo, numero, capacidad, ubicacion) VALUES (?, ?, ?, ?)";
    $result = SQL::actionQuery($con, $sql, "siis", $tipo, $numero, $capacidad, $ubicacion);
    if($result instanceof ErrorDB)
        Respuestas::enviarError($result, $con);

    Respuestas::enviarOk(null, $con);
}
?>

<?php
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';
require_once dirname(__FILE__).'/../util/strings.php';

// PUEDE TIRAR LOS CÓDIGOS DE ERROR:
// - NECESITA_LOGIN
// - NECESITA_ID
// - NECESITA_TIPO / NUMERO / CAPACIDAD / UBICACION
// - TIPO_NUMERO_REPETIDO
// - ID_ESPACIO_INVALIDA
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if(!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");

    if(!isset($_POST['id'])) Respuestas::enviarError("NECESITA_ID");
    if(!isset($_POST['tipo'])) Respuestas::enviarError("NECESITA_TIPO");
    if(!isset($_POST['numero'])) Respuestas::enviarError("NECESITA_NUMERO");
    if(!isset($_POST['capacidad'])) Respuestas::enviarError("NECESITA_CAPACIDAD");
    if(!isset($_POST['ubicacion'])) Respuestas::enviarError("NECESITA_UBICACION");

    $id = $_POST['id'];
    $tipo = $_POST['tipo'];
    $numero = $_POST['numero'];
    if($numero == '') $numero = null;
    $capacidad = $_POST['capacidad'];
    $ubicacion = $_POST['ubicacion'];

    $con = connectDb();
    $con->begin_transaction();

    $sqlCheck = "SELECT COUNT(*) AS c FROM espacio WHERE id_espacio = ?";
    $result = SQL::valueQuery($con, $sqlCheck, "i", $id);
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    if($result->fetch_assoc()['c'] == 0) Respuestas::enviarError("ID_ESPACIO_INVALIDA", $con);

    // Verificar duplicado de tipo + numero (excluyendo el mismo id)
    $sqlCheck = "SELECT COUNT(*) AS total FROM espacio WHERE tipo = ? AND numero = ? AND id_espacio <> ?";
    $dup = SQL::valueQuery($con, $sqlCheck, "sii", $tipo, $numero, $id);
    if($dup instanceof ErrorDB) Respuestas::enviarError($dup, $con);

    if($dup->fetch_assoc()['total'] > 0) {
        Respuestas::enviarError("TIPO_NUMERO_REPETIDO", $con);
    }

    // Actualizar registro
    $sql = "UPDATE espacio SET tipo = ?, numero = ?, capacidad = ?, ubicacion = ? WHERE id_espacio = ?";
    $result = SQL::actionQuery($con, $sql, "siisi", $tipo, $numero, $capacidad, $ubicacion, $id);
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    if($con->affected_rows === 0)
        Respuestas::enviarError("NO_EXISTE_ESPACIO", $con);

    Respuestas::enviarOk(null, $con);
}
?>

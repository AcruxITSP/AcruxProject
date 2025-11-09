<?php
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';
require_once dirname(__FILE__).'/../util/periodos.php';
require_once dirname(__FILE__).'/../util/reserva_recursos.php';
require_once dirname(__FILE__).'/../util/espacios.php';

// - NECESITA_LOGIN
// - FALTA_ID_RECURSO
// - FALTA_TIPO
// - FALTA_ID_ESPACIO
// - FALTA_CANTIDAD
// - ESPACIO_NO_EXISTE
if($_SERVER["REQUEST_METHOD"] == 'POST')
{
    if(!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");

    // RECURSO A MODIFICAR //
    if(!isset($_POST['id_recurso_externo'])) Respuestas::enviarError("FALTA_ID_RECURSO");
    $idRecursoExterno = $_POST['id_recurso_externo'];
    ////////////////////////


    // NUEVOS VALORES //
    if(!isset($_POST['tipo'])) Respuestas::enviarError("FALTA_TIPO");
    $nuevoTipo = $_POST['tipo'];

    if(!isset($_POST['id_espacio'])) Respuestas::enviarError("FALTA_ID_ESPACIO");
    $nuevoIdEspacio = $_POST['id_espacio'];
    if($nuevoIdEspacio == '') $nuevoIdEspacio = null;

    if(!isset($_POST['cantidad'])) Respuestas::enviarError("FALTA_CANTIDAD");
    $nuevaCantidad = $_POST['cantidad'];
    ///////////////////


    $con = connectDb();
    $con->begin_transaction();

    // Validar que el espacio exista (si id espacio no es null)
    if(isset($nuevoIdEspacio))
    {
        $sql = "SELECT COUNT(*) as c FROM espacio WHERE id_espacio = ?";
        $result = SQL::valueQuery($con, $sql, "i", $nuevoIdEspacio);
        if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
        if(intval($result->fetch_assoc()['c']) != 1) Respuestas::enviarError("ESPACIO_NO_EXISTE");
    }

    $sql = "SELECT id_recurso FROM recursoexterno WHERE id_recurso_externo = ?";
    $idRecursoBase = SQL::valueQuery($con, $sql, "i", $idRecursoExterno);
    if($idRecursoBase instanceof ErrorDB) Respuestas::enviarError($idRecursoBase, $con);
    $idRecursoBase = $idRecursoBase->fetch_assoc()['id_recurso'];

    // Actualizar recurso base
    $sql = "UPDATE recurso SET tipo = ? WHERE id_recurso = ?";
    $result = SQL::actionQuery($con, $sql, "si", $nuevoTipo, $idRecursoBase);
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    // Actualizar recurso externo
    $sql = "UPDATE recursoexterno SET cantidad_total = ?, id_espacio = ? WHERE id_recurso_externo = ?";
    $result = SQL::actionQuery($con, $sql, "iii", $nuevaCantidad, $nuevoIdEspacio, $idRecursoExterno);
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    Respuestas::enviarOk(null, $con);
}
?>
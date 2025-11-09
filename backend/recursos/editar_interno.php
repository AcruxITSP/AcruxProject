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
// - FALTA_ID_ESPACIOS
// - FALTA_CANTIDADES_EN_ESPACIOS
// - ESPACIO_NO_EXISTE
if($_SERVER["REQUEST_METHOD"] == 'POST')
{
    if(!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");

    // RECURSO A MODIFICAR //
    if(!isset($_POST['id_recurso_interno'])) Respuestas::enviarError("FALTA_ID_RECURSO");
    $idRecursoInterno = $_POST['id_recurso_interno'];
    ////////////////////////


    // NUEVOS VALORES //
    if(!isset($_POST['tipo'])) Respuestas::enviarError("FALTA_TIPO");
    $nuevoTipo = $_POST['tipo'];

    if(!isset($_POST['id_espacios'])) Respuestas::enviarError("FALTA_ID_ESPACIOS");
    $nuevosIdEspacios = $_POST['id_espacios'];

    if(!isset($_POST['cantidades_en_espacios'])) Respuestas::enviarError("FALTA_CANTIDADES_EN_ESPACIOS");
    $nuevasCantidadesEnEspacio = $_POST['cantidades_en_espacios'];
    ///////////////////


    $con = connectDb();
    $con->begin_transaction();

    $sql = "SELECT id_recurso FROM recursointerno WHERE id_recurso_interno = ?";
    $idRecursoBase = SQL::valueQuery($con, $sql, "i", $idRecursoInterno);
    if($idRecursoBase instanceof ErrorDB) Respuestas::enviarError($idRecursoBase, $con);
    $idRecursoBase = $idRecursoBase->fetch_assoc()['id_recurso'];

    // Actualizar recurso base
    $sql = "UPDATE recurso SET tipo = ? WHERE id_recurso = ?";
    $result = SQL::actionQuery($con, $sql, "si", $nuevoTipo, $idRecursoBase);
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    // Borrar relaciones hacia espacios para registrar las nuevas
    $sql = "DELETE FROM espacio_recursointerno WHERE id_recurso_interno = ?";
    $result = SQL::actionQuery($con, $sql, "i", $idRecursoInterno);
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    for($i = 0; $i < count($nuevosIdEspacios); ++$i)
    {
        $idEspacio = $nuevosIdEspacios[$i];
        $cantidad = $nuevasCantidadesEnEspacio[$i];

        // Verificar que el espacio exista
        $sql = "SELECT COUNT(*) as c FROM espacio WHERE id_espacio = ?";
        $result = SQL::valueQuery($con, $sql, "i", $idEspacio);
        if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
        if(intval($result->fetch_assoc()['c']) != 1) Respuestas::enviarError("ESPACIO_NO_EXISTE");

        // Relacionar recurso con el espacio
        $sql = "INSERT INTO espacio_recursointerno (id_recurso_interno, id_espacio, cantidad) VALUES (?, ?, ?)";
        $result = SQL::actionQuery($con, $sql, "iii", $idRecursoInterno, $idEspacio, $cantidad);
        if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    }

    Respuestas::enviarOk(null, $con);
}
?>
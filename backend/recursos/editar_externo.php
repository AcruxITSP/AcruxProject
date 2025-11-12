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

// Posibles errores:
// - NECESITA_LOGIN
// - FALTA_ID_RECURSO
// - FALTA_TIPO
// - FALTA_ID_ESPACIO
// - FALTA_CANTIDAD
// - NOMBRE_VACIO
// - TIPO_RECURSO_DUPLICADO
// - ESPACIO_NO_EXISTE

if ($_SERVER["REQUEST_METHOD"] == 'POST')
{
    if (!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");

    // RECURSO A MODIFICAR //
    if (!isset($_POST['id_recurso_base'])) Respuestas::enviarError("FALTA_ID_RECURSO");
    $idRecursoBase = $_POST['id_recurso_base'];
    ////////////////////////

    // NUEVOS VALORES //
    if (!isset($_POST['tipo'])) Respuestas::enviarError("FALTA_TIPO");
    $nuevoTipo = trim($_POST['tipo']);
    if ($nuevoTipo === "") Respuestas::enviarError("NOMBRE_VACIO");

    if (!isset($_POST['id_espacio'])) Respuestas::enviarError("FALTA_ID_ESPACIO");
    $nuevoIdEspacio = $_POST['id_espacio'];
    if ($nuevoIdEspacio === '' || $nuevoIdEspacio == 0) $nuevoIdEspacio = null;

    if (!isset($_POST['cantidad'])) Respuestas::enviarError("FALTA_CANTIDAD");
    $nuevaCantidad = $_POST['cantidad'];
    ///////////////////

    $con = connectDb();
    $con->begin_transaction();

    // Validar que no haya otro recurso con el mismo tipo
    $sql = "SELECT COUNT(*) AS c FROM recurso WHERE tipo = ? AND id_recurso != ?";
    $result = SQL::valueQuery($con, $sql, "si", $nuevoTipo, $idRecursoBase);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    $count = intval($result->fetch_assoc()['c']);
    if ($count > 0) Respuestas::enviarError("TIPO_RECURSO_DUPLICADO", $con);

    // Obtener id recurso externo
    $sql = "SELECT id_recurso_externo FROM recursoexterno WHERE id_recurso = ?";
    $result = SQL::valueQuery($con, $sql, "i", $idRecursoBase);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    $row = $result->fetch_assoc();
    if (!$row) Respuestas::enviarError("RECURSO_NO_EXISTE", $con);
    $idRecursoExterno = $row['id_recurso_externo'];

    // Validar que el espacio exista (si id espacio no es null)
    if (isset($nuevoIdEspacio))
    {
        $sql = "SELECT COUNT(*) AS c FROM espacio WHERE id_espacio = ?";
        $result = SQL::valueQuery($con, $sql, "i", $nuevoIdEspacio);
        if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
        if (intval($result->fetch_assoc()['c']) != 1) Respuestas::enviarError("ESPACIO_NO_EXISTE", $con);
    }

    // Actualizar recurso base
    $sql = "UPDATE recurso SET tipo = ? WHERE id_recurso = ?";
    $result = SQL::actionQuery($con, $sql, "si", $nuevoTipo, $idRecursoBase);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    // Actualizar recurso externo
    $sql = "UPDATE recursoexterno SET cantidad_total = ?, id_espacio = ? WHERE id_recurso_externo = ?";
    $result = SQL::actionQuery($con, $sql, "iii", $nuevaCantidad, $nuevoIdEspacio, $idRecursoExterno);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    Respuestas::enviarOk(null, $con);
}


// GET — obtener el estado actual del recurso externo
if ($_SERVER["REQUEST_METHOD"] == 'GET')
{
    if (!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");
    if (!isset($_GET['id_recurso_base'])) Respuestas::enviarError("FALTA_ID_RECURSO");

    $idRecursoBase = $_GET['id_recurso_base'];
    $con = connectDb();

    // Obtener información del recurso
    $sql = "
        SELECT 
            r.tipo,
            re.cantidad_total,
            re.id_espacio,
            CONCAT(e.tipo, IF(e.numero IS NOT NULL, CONCAT(' ', e.numero), '')) AS nombre_espacio
        FROM recurso r
        INNER JOIN recursoexterno re ON r.id_recurso = re.id_recurso
        LEFT JOIN espacio e ON re.id_espacio = e.id_espacio
        WHERE r.id_recurso = ?
    ";

    $result = SQL::valueQuery($con, $sql, "i", $idRecursoBase);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    $row = $result->fetch_assoc();
    if (!$row) Respuestas::enviarError("RECURSO_NO_EXISTE");

    $data = [
        'id_recurso' => intval($idRecursoBase),
        'tipo' => $row['tipo'],
        'id_espacio' => isset($row['id_espacio']) ? intval($row['id_espacio']) : null,
        'nombre_espacio' => $row['nombre_espacio'],
        'cantidad_total' => intval($row['cantidad_total'])
    ];

    Respuestas::enviarOk($data);
}
?>

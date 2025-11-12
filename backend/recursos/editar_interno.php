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

if ($_SERVER["REQUEST_METHOD"] == 'POST') {

    if (!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");
    if (!isset($_POST['id_recurso_base'])) Respuestas::enviarError("FALTA_ID_RECURSO");
    if (!isset($_POST['tipo'])) Respuestas::enviarError("FALTA_TIPO");
    if (!isset($_POST['id_espacios'])) Respuestas::enviarError("FALTA_ID_ESPACIOS");
    if (!isset($_POST['cantidades'])) Respuestas::enviarError("FALTA_CANTIDADES_EN_ESPACIOS");

    $idRecursoBase = $_POST['id_recurso_base'];
    $nuevoTipo = $_POST['tipo'];
    $nuevosIdEspacios = $_POST['id_espacios'];
    $nuevasCantidadesEnEspacio = $_POST['cantidades'];

    $con = connectDb();
    $con->begin_transaction();

    // Obtener id_recurso_interno
    $sql = "SELECT id_recurso_interno FROM recursointerno WHERE id_recurso = ?";
    $idRecursoInterno = SQL::valueQuery($con, $sql, "i", $idRecursoBase);
    if ($idRecursoInterno instanceof ErrorDB) Respuestas::enviarError($idRecursoInterno, $con);

    $idRecursoInterno = $idRecursoInterno->fetch_assoc()['id_recurso_interno'];

    // Actualizar tipo
    $sql = "UPDATE recurso SET tipo = ? WHERE id_recurso = ?";
    $result = SQL::actionQuery($con, $sql, "si", $nuevoTipo, $idRecursoBase);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    // Obtener relaciones actuales
    $sql = "SELECT id_espacio, cantidad FROM espacio_recursointerno WHERE id_recurso_interno = ?";
    $actualesRes = SQL::valueQuery($con, $sql, "i", $idRecursoInterno);
    if ($actualesRes instanceof ErrorDB) Respuestas::enviarError($actualesRes, $con);

    $espaciosActuales = [];
    $cantidadesActuales = [];
    while ($row = $actualesRes->fetch_assoc()) {
        $espaciosActuales[] = intval($row['id_espacio']);
        $cantidadesActuales[intval($row['id_espacio'])] = intval($row['cantidad']);
    }

    // Calcular diferencias
    $aAgregar = array_diff($nuevosIdEspacios, $espaciosActuales);
    $aEliminar = array_diff($espaciosActuales, $nuevosIdEspacios);
    $aPosiblesActualizar = array_intersect($espaciosActuales, $nuevosIdEspacios);

    // Eliminar relaciones que ya no están
    foreach ($aEliminar as $idEspacio) {
        $sql = "DELETE FROM espacio_recursointerno WHERE id_recurso_interno = ? AND id_espacio = ?";
        $result = SQL::actionQuery($con, $sql, "ii", $idRecursoInterno, $idEspacio);
        if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    }

    // Insertar nuevas relaciones
    foreach ($aAgregar as $idEspacio) {
        $i = array_search($idEspacio, $nuevosIdEspacios);
        $cantidad = intval($nuevasCantidadesEnEspacio[$i]);

        // Verificar existencia del espacio
        $sql = "SELECT COUNT(*) AS c FROM espacio WHERE id_espacio = ?";
        $result = SQL::valueQuery($con, $sql, "i", $idEspacio);
        if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
        if (intval($result->fetch_assoc()['c']) != 1) Respuestas::enviarError("ESPACIO_NO_EXISTE");

        $sql = "INSERT INTO espacio_recursointerno (id_recurso_interno, id_espacio, cantidad) VALUES (?, ?, ?)";
        $result = SQL::actionQuery($con, $sql, "iii", $idRecursoInterno, $idEspacio, $cantidad);
        if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    }

    // Actualizar cantidades si cambian
    foreach ($aPosiblesActualizar as $idEspacio) {
        $i = array_search($idEspacio, $nuevosIdEspacios);
        $nuevaCantidad = intval($nuevasCantidadesEnEspacio[$i]);
        $viejaCantidad = intval($cantidadesActuales[$idEspacio] ?? -1);

        if ($nuevaCantidad !== $viejaCantidad) {
            $sql = "UPDATE espacio_recursointerno SET cantidad = ? WHERE id_recurso_interno = ? AND id_espacio = ?";
            $result = SQL::actionQuery($con, $sql, "iii", $nuevaCantidad, $idRecursoInterno, $idEspacio);
            if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
        }
    }

    Respuestas::enviarOk(null, $con);
}

if ($_SERVER["REQUEST_METHOD"] == 'GET')
{
    if (!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");
    if (!isset($_GET['id_recurso_base'])) Respuestas::enviarError("FALTA_ID_RECURSO");

    $idRecursoBase = $_GET['id_recurso_base'];
    $con = connectDb();

    // Obtener información base del recurso
    $sql = "SELECT tipo FROM recurso WHERE id_recurso = ?";
    $result = SQL::valueQuery($con, $sql, "i", $idRecursoBase);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    $row = $result->fetch_assoc();
    if (!$row) Respuestas::enviarError("RECURSO_NO_EXISTE");

    $tipo = $row['tipo'];

    // Obtener el id_recurso_interno correspondiente
    $sql = "SELECT id_recurso_interno FROM recursointerno WHERE id_recurso = ?";
    $result = SQL::valueQuery($con, $sql, "i", $idRecursoBase);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    $row = $result->fetch_assoc();
    if (!$row) Respuestas::enviarError("RECURSO_INTERNO_NO_EXISTE");

    $idRecursoInterno = $row['id_recurso_interno'];

    // Obtener las relaciones actuales con espacios
    $sql = "
        SELECT 
            e.id_espacio,
            CONCAT(e.tipo, IF(e.numero IS NOT NULL, CONCAT(' ', e.numero), '')) AS nombre,
            eri.cantidad
        FROM espacio_recursointerno eri
        INNER JOIN espacio e ON e.id_espacio = eri.id_espacio
        WHERE eri.id_recurso_interno = ?
    ";

    $result = SQL::valueQuery($con, $sql, "i", $idRecursoInterno);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    $espacios = [];
    while ($row = $result->fetch_assoc())
    {
        $espacios[] = [
            'id_espacio' => intval($row['id_espacio']),
            'nombre' => $row['nombre'],
            'cantidad' => intval($row['cantidad'])
        ];
    }

    $data = [
        'id_recurso' => intval($idRecursoBase),
        'tipo' => $tipo,
        'espacios' => $espacios
    ];

    Respuestas::enviarOk($data);
}
?>

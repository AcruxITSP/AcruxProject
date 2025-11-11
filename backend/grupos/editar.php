<?php
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';
require_once dirname(__FILE__).'/../util/strings.php';

// POSIBLES ERRORES:
// - NECESITA_LOGIN
// - NECESITA_ID_GRUPO
// - NECESITA_GRADO
// - NECESITA_NOMBRE
// - NECESITA_ID_CURSO
// - NECESITA_ID_ADSCRITO
// - GRUPO_NO_ENCONTRADO

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    // Verificar sesión activa
    if (!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");

    // Verificar parámetros obligatorios
    if (!isset($_POST['id_grupo'])) Respuestas::enviarError("NECESITA_ID_GRUPO");
    if (!isset($_POST['grado'])) Respuestas::enviarError("NECESITA_GRADO");
    if (!isset($_POST['nombre'])) Respuestas::enviarError("NECESITA_NOMBRE");
    if (!isset($_POST['id_curso'])) Respuestas::enviarError("NECESITA_ID_CURSO");
    if (!isset($_POST['id_adscrito'])) Respuestas::enviarError("NECESITA_ID_ADSCRITO");

    // Obtener valores del POST
    $idGrupo = $_POST['id_grupo'];
    $grado = $_POST['grado'];
    $nombre = $_POST['nombre'];
    $idCurso = $_POST['id_curso'];
    $idAdscrito = $_POST['id_adscrito'];

    // Conectar e iniciar transacción
    $con = connectDb();
    $con->begin_transaction();

    // Verificar que el grupo exista
    $sql = "SELECT id_grupo FROM Grupo WHERE id_grupo = ?";
    $result = SQL::valueQuery($con, $sql, "i", $idGrupo);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    if ($result->num_rows == 0) Respuestas::enviarError("GRUPO_NO_ENCONTRADO");

    // Actualizar los campos del grupo
    $sql = "UPDATE Grupo 
            SET grado = ?, nombre = ?, id_curso = ?, id_adscrito = ?
            WHERE id_grupo = ?";
    $result = SQL::actionQuery($con, $sql, "ssiii", $grado, $nombre, $idCurso, $idAdscrito, $idGrupo);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    // Confirmar la transacción
    Respuestas::enviarOk(null, $con);
}
?>

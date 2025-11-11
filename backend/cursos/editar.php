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
// - NECESITA_ID_CURSO
// - NECESITA_NOMBRE
// - NECESITA_ID_MATERIAS

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    // Verificar que el usuario esté logueado
    if (!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");

    // Verificar parámetros obligatorios
    if (!isset($_POST['id_curso'])) Respuestas::enviarError("NECESITA_ID_CURSO");
    if (!isset($_POST['nombre'])) Respuestas::enviarError("NECESITA_NOMBRE");
    if (!isset($_POST['id_materias'])) Respuestas::enviarError("NECESITA_ID_MATERIAS");

    $idCurso = $_POST['id_curso'];
    $nombre = $_POST['nombre'];
    $idMaterias = $_POST['id_materias'];

    // Validar formato de lista de materias
    if (!is_array($idMaterias)) Respuestas::enviarError("FORMATO_ID_MATERIAS_INVALIDO");

    // Conectar a la base de datos e iniciar transacción
    $con = connectDb();
    $con->begin_transaction();

    // Verificar que el curso exista
    $sql = "SELECT id_curso FROM Curso WHERE id_curso = ?";
    $result = SQL::valueQuery($con, $sql, "i", $idCurso);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    if ($result->num_rows == 0) Respuestas::enviarError("CURSO_NO_ENCONTRADO");

    // Actualizar el nombre del curso
    $sql = "UPDATE Curso SET nombre = ? WHERE id_curso = ?";
    $result = SQL::actionQuery($con, $sql, "si", $nombre, $idCurso);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    // Obtener relaciones actuales con materias
    $sql = "SELECT id_materia FROM Curso_Materia WHERE id_curso = ?";
    $result = SQL::valueQuery($con, $sql, "i", $idCurso);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    $materiasActuales = [];
    while ($row = $result->fetch_assoc()) $materiasActuales[] = (int)$row['id_materia'];

    // Calcular diferencias
    $materiasAAgregar = array_diff($idMaterias, $materiasActuales);
    $materiasAEliminar = array_diff($materiasActuales, $idMaterias);

    // Eliminar solo las relaciones que ya no existan
    foreach ($materiasAEliminar as $idMateria)
    {
        $sql = "DELETE FROM Curso_Materia WHERE id_curso = ? AND id_materia = ?";
        $result = SQL::actionQuery($con, $sql, "ii", $idCurso, $idMateria);
        if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    }

    // Insertar solo las nuevas relaciones
    foreach ($materiasAAgregar as $idMateria)
    {
        $sql = "INSERT INTO Curso_Materia(id_curso, id_materia) VALUES (?, ?)";
        $result = SQL::actionQuery($con, $sql, "ii", $idCurso, $idMateria);
        if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    }

    // Confirmar cambios
    Respuestas::enviarOk(null, $con);
}


if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    // Verificar login
    if (!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");

    // Verificar parámetros obligatorios
    if (!isset($_GET['id_curso'])) Respuestas::enviarError("NECESITA_ID_CURSO");

    $idCurso = $_GET['id_curso'];

    $con = connectDb();

    // Verificar que el curso exista
    $sql = "SELECT id_curso, nombre FROM Curso WHERE id_curso = ?";
    $curso = SQL::valueQuery($con, $sql, "i", $idCurso);
    if ($curso instanceof ErrorDB) Respuestas::enviarError($curso, $con);

    if ($curso->num_rows == 0)
        Respuestas::enviarError("CURSO_NO_ENCONTRADO", $con);

    $dataCurso = $curso->fetch_assoc();

    // Obtener las materias asociadas al curso
    $sql = "
        SELECT m.id_materia, m.nombre
        FROM Curso_Materia cm
        INNER JOIN Materia m ON cm.id_materia = m.id_materia
        WHERE cm.id_curso = ?
    ";
    $materias = SQL::valueQuery($con, $sql, "i", $idCurso);
    if ($materias instanceof ErrorDB) Respuestas::enviarError($materias, $con);

    $listaMaterias = [];
    while ($row = $materias->fetch_assoc())
        $listaMaterias[] = [
            "id_materia" => (int)$row["id_materia"],
            "nombre" => $row["nombre"]
        ];

    // Enviar respuesta
    $response = [
        "id_curso" => (int)$dataCurso["id_curso"],
        "nombre" => $dataCurso["nombre"],
        "materias" => $listaMaterias
    ];

    Respuestas::enviarOk($response, $con);
}
?>

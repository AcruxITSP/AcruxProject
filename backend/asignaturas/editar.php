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
// - NECESITA_ID_MATERIA
// - NECESITA_NOMBRE
// - NECESITA_ID_PROFESORES
// - NECESITA_ID_CURSOS

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    // Verifica si hay sesión activa
    if (!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");

    // Verifica parámetros obligatorios
    if (!isset($_POST['id_materia'])) Respuestas::enviarError("NECESITA_ID_MATERIA");
    if (!isset($_POST['nombre'])) Respuestas::enviarError("NECESITA_NOMBRE");
    if (!isset($_POST['id_profesores'])) Respuestas::enviarError("NECESITA_ID_PROFESORES");
    if (!isset($_POST['id_cursos'])) Respuestas::enviarError("NECESITA_ID_CURSOS");

    // Se obtienen los datos del POST
    $idMateria = $_POST['id_materia'];
    $nombre = $_POST['nombre'];
    $idProfesores = $_POST['id_profesores'];
    $idCursos = $_POST['id_cursos'];

    // Validación adicional: deben ser arrays
    if (!is_array($idProfesores)) Respuestas::enviarError("FORMATO_ID_PROFESORES_INVALIDO");
    if (!is_array($idCursos)) Respuestas::enviarError("FORMATO_ID_CURSOS_INVALIDO");

    // Se establece la conexión y se inicia la transacción
    $con = connectDb();
    $con->begin_transaction();

    // Verifica que la materia exista
    $sql = "SELECT id_materia FROM Materia WHERE id_materia = ?";
    $result = SQL::valueQuery($con, $sql, "i", $idMateria);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    if ($result->num_rows == 0) Respuestas::enviarError("MATERIA_NO_ENCONTRADA");

    // Actualiza el nombre de la materia
    $sql = "UPDATE Materia SET nombre = ? WHERE id_materia = ?";
    $result = SQL::actionQuery($con, $sql, "si", $nombre, $idMateria);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    // Elimina relaciones antiguas con cursos y profesores
    $sql = "DELETE FROM Curso_Materia WHERE id_materia = ?";
    $result = SQL::actionQuery($con, $sql, "i", $idMateria);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    $sql = "DELETE FROM Clase WHERE id_materia = ?";
    $result = SQL::actionQuery($con, $sql, "i", $idMateria);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    // Inserta nuevas relaciones con cursos
    foreach ($idCursos as $idCurso)
    {
        $sql = "INSERT INTO Curso_Materia(id_curso, id_materia) VALUES (?, ?)";
        $result = SQL::actionQuery($con, $sql, "ii", $idCurso, $idMateria);
        if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    }

    // Inserta nuevas clases con los profesores seleccionados
    foreach ($idProfesores as $idProfesor)
    {
        $sql = "INSERT INTO Clase(id_materia, id_profesor) VALUES (?, ?)";
        $result = SQL::actionQuery($con, $sql, "ii", $idMateria, $idProfesor);
        if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    }

    // Confirma la transacción
    Respuestas::enviarOk(null, $con);
}

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    // Verifica si hay sesión activa
    if (!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");

    // Verifica que se haya enviado el id de la materia
    if (!isset($_GET['id_materia'])) Respuestas::enviarError("NECESITA_ID_MATERIA");

    $idMateria = $_GET['id_materia'];

    $con = connectDb();

    // 1. Obtener nombre de la materia
    $sql = "SELECT nombre FROM Materia WHERE id_materia = ?";
    $result = SQL::valueQuery($con, $sql, "i", $idMateria);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    if ($result->num_rows === 0) Respuestas::enviarError("MATERIA_NO_ENCONTRADA");
    $row = $result->fetch_assoc();
    $nombreMateria = $row['nombre'];

    // 2. Obtener los profesores asociados a la materia
    // Clase → Profesor → Usuario
    $sql = "SELECT 
                p.id_profesor AS id, 
                u.nombre AS nombre, 
                u.apellido AS apellido
            FROM Clase c
            INNER JOIN Profesor p ON c.id_profesor = p.id_profesor
            INNER JOIN Usuario u ON p.id_usuario = u.id_usuario
            WHERE c.id_materia = ?";
    $result = SQL::valueQuery($con, $sql, "i", $idMateria);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    $profesores = [];
    while ($row = $result->fetch_assoc()) {
        $profesores[] = $row;
    }

    // 3. Obtener los cursos asociados
    // Curso_Materia → Curso
    $sql = "SELECT 
                c.id_curso AS id, 
                c.nombre AS nombre
            FROM Curso_Materia cm
            INNER JOIN Curso c ON cm.id_curso = c.id_curso
            WHERE cm.id_materia = ?";
    $result = SQL::valueQuery($con, $sql, "i", $idMateria);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    $cursos = [];
    while ($row = $result->fetch_assoc()) {
        $cursos[] = $row;
    }

    // 4. Armar respuesta final
    $data = [
        "id_materia" => (int)$idMateria,
        "nombre" => $nombreMateria,
        "profesores" => $profesores,
        "cursos" => $cursos
    ];

    Respuestas::enviarOk($data, $con);
}

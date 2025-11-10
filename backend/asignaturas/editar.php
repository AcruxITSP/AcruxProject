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
    // Verificar si hay sesión activa
    if (!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");

    // Verificar que se hayan enviado todos los parámetros necesarios
    if (!isset($_POST['id_materia'])) Respuestas::enviarError("NECESITA_ID_MATERIA");
    if (!isset($_POST['nombre'])) Respuestas::enviarError("NECESITA_NOMBRE");
    if (!isset($_POST['id_profesores'])) Respuestas::enviarError("NECESITA_ID_PROFESORES");
    if (!isset($_POST['id_cursos'])) Respuestas::enviarError("NECESITA_ID_CURSOS");

    // Obtener datos del POST
    $idMateria = $_POST['id_materia'];
    $nombre = $_POST['nombre'];
    $idProfesores = $_POST['id_profesores'];
    $idCursos = $_POST['id_cursos'];

    // Validar que las listas sean arrays
    if (!is_array($idProfesores)) Respuestas::enviarError("FORMATO_ID_PROFESORES_INVALIDO");
    if (!is_array($idCursos)) Respuestas::enviarError("FORMATO_ID_CURSOS_INVALIDO");

    // Iniciar conexión y transacción
    $con = connectDb();
    $con->begin_transaction();

    // Verificar que la materia exista
    $sql = "SELECT id_materia FROM Materia WHERE id_materia = ?";
    $result = SQL::valueQuery($con, $sql, "i", $idMateria);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    if ($result->num_rows == 0) Respuestas::enviarError("MATERIA_NO_ENCONTRADA");

    // Actualizar nombre de la materia
    $sql = "UPDATE Materia SET nombre = ? WHERE id_materia = ?";
    $result = SQL::actionQuery($con, $sql, "si", $nombre, $idMateria);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    // Obtener relaciones actuales con cursos
    $sql = "SELECT id_curso FROM Curso_Materia WHERE id_materia = ?";
    $result = SQL::valueQuery($con, $sql, "i", $idMateria);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    $cursosActuales = [];
    while ($row = $result->fetch_assoc()) $cursosActuales[] = (int)$row['id_curso'];

    // Obtener relaciones actuales con profesores
    $sql = "SELECT id_profesor FROM Clase WHERE id_materia = ?";
    $result = SQL::valueQuery($con, $sql, "i", $idMateria);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    $profesoresActuales = [];
    while ($row = $result->fetch_assoc()) $profesoresActuales[] = (int)$row['id_profesor'];

    // Determinar qué cursos agregar y cuáles eliminar
    $cursosAAgregar = array_diff($idCursos, $cursosActuales);
    $cursosAEliminar = array_diff($cursosActuales, $idCursos);

    // Determinar qué profesores agregar y cuáles eliminar
    $profesoresAAgregar = array_diff($idProfesores, $profesoresActuales);
    $profesoresAEliminar = array_diff($profesoresActuales, $idProfesores);

    // Eliminar solo las relaciones de cursos que ya no estén asociadas
    foreach ($cursosAEliminar as $idCurso)
    {
        $sql = "DELETE FROM Curso_Materia WHERE id_curso = ? AND id_materia = ?";
        $result = SQL::actionQuery($con, $sql, "ii", $idCurso, $idMateria);
        if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    }

    // Insertar nuevas relaciones de cursos
    foreach ($cursosAAgregar as $idCurso)
    {
        $sql = "INSERT INTO Curso_Materia(id_curso, id_materia) VALUES (?, ?)";
        $result = SQL::actionQuery($con, $sql, "ii", $idCurso, $idMateria);
        if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    }

    // Eliminar solo las relaciones de profesores que ya no estén asociadas
    foreach ($profesoresAEliminar as $idProfesor)
    {
        $sql = "DELETE FROM Clase WHERE id_materia = ? AND id_profesor = ?";
        $result = SQL::actionQuery($con, $sql, "ii", $idMateria, $idProfesor);
        if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    }

    // Insertar nuevas relaciones de profesores
    foreach ($profesoresAAgregar as $idProfesor)
    {
        $sql = "INSERT INTO Clase(id_materia, id_profesor) VALUES (?, ?)";
        $result = SQL::actionQuery($con, $sql, "ii", $idMateria, $idProfesor);
        if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    }

    // Confirmar transacción
    Respuestas::enviarOk(null, $con);
}

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    // Verificar si hay sesión activa
    if (!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");

    // Verificar que se haya enviado el id de la materia
    if (!isset($_GET['id_materia'])) Respuestas::enviarError("NECESITA_ID_MATERIA");

    $idMateria = $_GET['id_materia'];

    $con = connectDb();

    // Obtener nombre de la materia
    $sql = "SELECT nombre FROM Materia WHERE id_materia = ?";
    $result = SQL::valueQuery($con, $sql, "i", $idMateria);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    if ($result->num_rows === 0) Respuestas::enviarError("MATERIA_NO_ENCONTRADA");

    $row = $result->fetch_assoc();
    $nombreMateria = $row['nombre'];

    // Obtener los profesores asociados a la materia
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
    while ($row = $result->fetch_assoc()) $profesores[] = $row;

    // Obtener los cursos asociados
    $sql = "SELECT 
                c.id_curso AS id, 
                c.nombre AS nombre
            FROM Curso_Materia cm
            INNER JOIN Curso c ON cm.id_curso = c.id_curso
            WHERE cm.id_materia = ?";
    $result = SQL::valueQuery($con, $sql, "i", $idMateria);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    $cursos = [];
    while ($row = $result->fetch_assoc()) $cursos[] = $row;

    // Armar respuesta final
    $data = [
        "id_materia" => (int)$idMateria,
        "nombre" => $nombreMateria,
        "profesores" => $profesores,
        "cursos" => $cursos
    ];

    Respuestas::enviarOk($data, $con);
}

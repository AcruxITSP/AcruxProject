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
// - NECESITA_NOMBRE
// - NECESITA_ID_PROFESORES
// - NECESITA_ID_CURSOS

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
    // Verifica si hay sesión activa
    if (!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");

    // Verifica parámetros obligatorios
    if (!isset($_POST['nombre'])) Respuestas::enviarError("NECESITA_NOMBRE");
    if (!isset($_POST['id_profesores'])) Respuestas::enviarError("NECESITA_ID_PROFESORES");
    if (!isset($_POST['id_cursos'])) Respuestas::enviarError("NECESITA_ID_CURSOS");

    // Se obtienen los datos del POST
    $nombre = $_POST['nombre'];
    $idProfesores = $_POST['id_profesores'];
    $idCursos = $_POST['id_cursos'];

    // Validación adicional: deben ser arrays
    if (!is_array($idProfesores)) Respuestas::enviarError("FORMATO_ID_PROFESORES_INVALIDO");
    if (!is_array($idCursos)) Respuestas::enviarError("FORMATO_ID_CURSOS_INVALIDO");

    // Se establece la conexión y se inicia la transacción
    $con = connectDb();
    $con->begin_transaction();

    // Inserta la materia
    $sql = "INSERT INTO Materia(nombre) VALUES (?)";
    $result = SQL::actionQuery($con, $sql, "s", $nombre);
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    $idMateria = $con->insert_id;

    // Asocia la materia a todos los cursos indicados
    foreach ($idCursos as $idCurso)
    {
        $sql = "INSERT INTO Curso_Materia(id_curso, id_materia) VALUES (?, ?)";
        $result = SQL::actionQuery($con, $sql, "ii", $idCurso, $idMateria);
        if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    }

    // Crea las clases asociadas a los profesores
    foreach ($idProfesores as $idProfesor)
    {
        $sql = "INSERT INTO Clase(id_materia, id_profesor) VALUES (?, ?)";
        $result = SQL::actionQuery($con, $sql, "ii", $idMateria, $idProfesor);
        if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    }

    // Confirma la transacción
    Respuestas::enviarOk(null, $con);
}

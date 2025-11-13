<?php
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';
require_once dirname(__FILE__).'/../util/strings.php';
require_once dirname(__FILE__).'/../util/timing.php';

// =====================================
// OBTENER MATERIAS SEGÚN ID_USUARIO
// =====================================
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET['id_usuario']))
        Respuestas::enviarError("FALTA_ID_USUARIO");

    $idUsuario = intval($_GET['id_usuario']);

    $con = connectDb();

    // Primero obtener el id_profesor asociado
    $sqlProfesor = "SELECT id_profesor FROM profesor WHERE id_usuario = ?";
    $resultProfesor = SQL::valueQuery($con, $sqlProfesor, "i", $idUsuario);
    if ($resultProfesor instanceof ErrorDB) Respuestas::enviarError($resultProfesor, $con);

    $rowProfesor = $resultProfesor->fetch_assoc();
    if (!$rowProfesor) Respuestas::enviarError("USUARIO_NO_ES_PROFESOR");

    $idProfesor = intval($rowProfesor['id_profesor']);

    Respuestas::enviarOk(
        obtenerMateriasPorProfesor($con, $idProfesor),
        $con
    );
}

function obtenerMateriasPorProfesor($con, $idProfesor)
{
    $respuesta = [];

    // Obtener las materias asociadas al profesor
    $sqlMaterias = "SELECT DISTINCT m.*
                    FROM materia m
                    INNER JOIN clase c ON c.id_materia = m.id_materia
                    WHERE c.id_profesor = ?";
    $resultMaterias = SQL::valueQuery($con, $sqlMaterias, "i", $idProfesor);
    if ($resultMaterias instanceof ErrorDB) Respuestas::enviarError($resultMaterias, $con);

    while ($materia = $resultMaterias->fetch_assoc()) {
        $idMateria = $materia['id_materia'];
        $materiaData = $materia;
        $materiaData['docentes'] = [];
        $materiaData['cursos'] = [];

        // === DOCENTES ===
        $sqlDocentes = "SELECT
                            p.id_profesor,
                            u.id_usuario,
                            CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo
                        FROM clase c
                        INNER JOIN profesor p ON c.id_profesor = p.id_profesor
                        INNER JOIN usuario u ON p.id_usuario = u.id_usuario
                        WHERE c.id_materia = ?";
        $resultDocentes = SQL::valueQuery($con, $sqlDocentes, "i", $idMateria);
        if ($resultDocentes instanceof ErrorDB) Respuestas::enviarError($resultDocentes, $con);

        while ($docente = $resultDocentes->fetch_assoc())
            $materiaData['docentes'][] = $docente;

        // === CURSOS ===
        $sqlCursos = "SELECT 
                          c.id_curso,
                          c.nombre AS nombre_curso
                      FROM curso c
                      INNER JOIN curso_materia cm ON c.id_curso = cm.id_curso
                      WHERE cm.id_materia = ?";
        $resultCursos = SQL::valueQuery($con, $sqlCursos, "i", $idMateria);
        if ($resultCursos instanceof ErrorDB) Respuestas::enviarError($resultCursos, $con);

        while ($curso = $resultCursos->fetch_assoc())
            $materiaData['cursos'][] = $curso;

        $respuesta[] = $materiaData;
    }

    return $respuesta;
}
?>

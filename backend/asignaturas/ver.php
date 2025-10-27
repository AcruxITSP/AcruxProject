<?php
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';
require_once dirname(__FILE__).'/../util/strings.php';
require_once dirname(__FILE__).'/../util/timing.php';

// PUEDE TIRAR LOS CODIGOS DE ERROR:
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $con = connectDb();
    Respuestas::enviarOk(
        obtenerDatosMaterias($con),
        $con
    );
}

function obtenerDatosMaterias($con)
{
    // Obtener los datos de TODAS las materias
    $respuesta = [];
    $sqlMaterias = "SELECT * FROM materia";
    $resultMaterias = SQL::valueQuery($con, $sqlMaterias, "");
    if($resultMaterias instanceof ErrorDB) Respuestas::enviarError($resultMaterias, $con);
    
    while($materia = $resultMaterias->fetch_assoc())
    {
        $idMateria = $materia['id_materia'];

        // Estructura base de cada materia
        $materiaData = $materia;
        $materiaData['docentes'] = [];
        $materiaData['cursos'] = [];

        // === DOCENTES ===
        $sqlDocentes = "SELECT
                            profesor.id_profesor,
                            usuario.id_usuario,
                            usuario.nombre AS nombre_profesor,
                            usuario.apellido AS apellido_profesor
                        FROM clase
                        INNER JOIN profesor ON clase.id_profesor = profesor.id_profesor
                        INNER JOIN usuario ON profesor.id_usuario = usuario.id_usuario
                        WHERE clase.id_materia = ?";
        $resultDocentes = SQL::valueQuery($con, $sqlDocentes, "i", $idMateria);
        if($resultDocentes instanceof ErrorDB) Respuestas::enviarError($resultDocentes, $con);
        while($docente = $resultDocentes->fetch_assoc())
        {
            $materiaData['docentes'][] = $docente;
        }

        // === CURSOS ===
        $sqlCursos = "SELECT 
                          curso.id_curso,
                          curso.nombre AS nombre_curso
                      FROM curso
                      INNER JOIN curso_materia ON curso.id_curso = curso_materia.id_curso
                      WHERE curso_materia.id_materia = ?";
        $resultCursos = SQL::valueQuery($con, $sqlCursos, "i", $idMateria);
        if($resultCursos instanceof ErrorDB) Respuestas::enviarError($resultCursos, $con);
        while($curso = $resultCursos->fetch_assoc())
        {
            $materiaData['cursos'][] = $curso;
        }

        // Agregar al arreglo final
        $respuesta[] = $materiaData;
    }

    return $respuesta;
}
?>
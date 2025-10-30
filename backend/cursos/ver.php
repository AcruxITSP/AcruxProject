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
        obtenerDatosCursos($con),
        $con
    );
}

function obtenerDatosCursos($con)
{
    $datosCursos = [];

    $sqlCursos = "SELECT * FROM curso";
    $resultCursos = SQL::valueQuery($con, $sqlCursos, "");
    if($resultCursos instanceof ErrorDB) Respuestas::enviarError($resultCursos, $con);
    
    while($curso = $resultCursos->fetch_assoc())
    {
        // Obtenemos los datos del curso
        $idCurso = $curso['id_curso'];
        $nombreCurso = $curso['nombre'];

        $datosCurso = []; // Aca se guardaran los datos de este curso
        $datosCurso['id_curso'] = $idCurso;
        $datosCurso['nombre'] = $nombreCurso;
        $datosCurso['materias'] = obtenerMateriasDelCurso($con, $idCurso);

        // Agregar los datos de este curso a la respuesta
        $datosCursos[] = $datosCurso;

    }
    $resultCursos->close();

    return $datosCursos;
}

function obtenerMateriasDelCurso($con, $idCurso)
{
    $sqlMateriasPorCurso = "SELECT materia.*
                            FROM curso, materia, curso_materia
                            WHERE curso_materia.id_curso = curso.id_curso
                            AND curso_materia.id_materia = materia.id_materia
                            AND curso.id_curso = ?";
    $resultMaterias = SQL::valueQuery($con, $sqlMateriasPorCurso, "i", $idCurso);
    if($resultMaterias instanceof ErrorDB) Respuestas::enviarError($resultMaterias, $con);

    $materias = [];
    while($materia = $resultMaterias->fetch_assoc()) $materias[] = $materia;
    return $materias;
}
?>
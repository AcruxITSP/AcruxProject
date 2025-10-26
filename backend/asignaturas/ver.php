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
        $respuesta[] = $materia;
        $respuesta[count($respuesta)-1]['docentes'] = [];

        // Obtener los docentes que dictan la materia
        $sqlDocentes = "SELECT
                        profesor.id_profesor,
                        usuario.id_usuario,
                        usuario.nombre as nombre_profesor,
                        usuario.apellido as apellido_profesor
                        FROM materia, clase, profesor, usuario
                        WHERE clase.id_materia = materia.id_materia
                        AND clase.id_profesor = profesor.id_profesor
                        AND profesor.id_usuario = usuario.id_usuario
                        AND materia.id_materia = ?";
        $resultDocentes = SQL::valueQuery($con, $sqlDocentes, "i", $idMateria);
        if($resultDocentes instanceof ErrorDB) Respuestas::enviarError($resultDocentes, $con);
        while($docente = $resultDocentes->fetch_assoc())
        {
            $respuesta[count($respuesta)-1]['docentes'][] = $docente;
        }
    }

    return $respuesta;
    
}
?>
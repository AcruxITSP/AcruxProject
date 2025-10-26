<?php
// Se incluyen los archivos necesarios para la conexión a la base de datos,
// manejo de errores, respuestas estandarizadas y utilidades generales.
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';
require_once dirname(__FILE__).'/../util/timing.php';
require_once dirname(__FILE__).'/../util/reserva_recursos.php';

// Este script se ejecuta solo si la petición HTTP es de tipo GET.
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    // Se establece la conexión con la base de datos.
    $con = connectDb();

    // Se crea la estructura de respuesta que se enviará en formato JSON.
    $respuesta = [
        "grupos" => [],
        "profesores_por_id" => [],
        "id_profesores_por_id_materias" => [],
        "materias_por_id" => [],
        "espacios_libres" => [],
    ];

    // ---------------------------------------------------------------
    // Consulta 1: Obtener todos los grupos registrados.
    // ---------------------------------------------------------------
    // Se seleccionan el id del grupo y un texto formado por grado y nombre.
    $sql = "SELECT id_grupo, CONCAT(grado, ' ', nombre) as texto FROM Grupo";
    $result = SQL::valueQuery($con, $sql, "");

    // Si ocurre un error en la consulta, se envía un mensaje de error y se termina la ejecución.
    if($result instanceof ErrorDB) Respuestas::enviarError($result);

    // Bucle que recorre todas las filas devueltas por la consulta de grupos.
    // Cada fila se agrega al arreglo 'grupos' dentro de la respuesta.
    while($row = $result->fetch_assoc())
        $respuesta["grupos"][] = $row;

    // ---------------------------------------------------------------
    // Consulta 2: Obtener los profesores y las materias que dictan.
    // ---------------------------------------------------------------
    // Se hace una unión entre las tablas Clase, Profesor, Materia y Usuario
    // para obtener el nombre y apellido del profesor, su id y la materia correspondiente.
    $sql = "SELECT
            Usuario.nombre,
            Usuario.apellido,
            Profesor.id_profesor,
            Materia.id_materia
            FROM Clase, Profesor, Materia, Usuario
            WHERE Clase.id_profesor = Profesor.id_profesor
            AND Clase.id_materia = Materia.id_materia
            AND profesor.id_usuario = usuario.id_usuario";

    $result = SQL::valueQuery($con, $sql, "");
    if($result instanceof ErrorDB) Respuestas::enviarError($result);

    // Bucle que recorre los resultados de la consulta anterior.
    while($row = $result->fetch_assoc())
    {
        $idProfe = (int)$row['id_profesor'];

        // Si el profesor todavía no fue agregado al arreglo 'profesores_por_id',
        // se agrega su información para evitar duplicados.
        if(!key_exists($idProfe, $respuesta["profesores_por_id"]))
        {
            $respuesta["profesores_por_id"][$idProfe] = $row;
        }

        $idMateria = (int)$row['id_materia'];

        // Si la materia aún no está registrada en el mapa de profesores por materia,
        // se inicializa con un arreglo vacío.
        if(!key_exists($idMateria, $respuesta["id_profesores_por_id_materias"]))
            $respuesta["id_profesores_por_id_materias"][$idMateria] = [];

        // Si el profesor no está ya listado dentro de esta materia,
        // se agrega su id al arreglo correspondiente.
        if(!in_array($idProfe, $respuesta["id_profesores_por_id_materias"][$idMateria]))
        {
            $respuesta["id_profesores_por_id_materias"][$idMateria][] = $idProfe;
        }
    }

    // ---------------------------------------------------------------
    // Consulta 3: Obtener todas las materias existentes.
    // ---------------------------------------------------------------
    // Se seleccionan todos los campos de la tabla Materia.
    $sql = "SELECT * FROM Materia";
    $result = SQL::valueQuery($con, $sql, "");
    if($result instanceof ErrorDB) Respuestas::enviarError($result);

    // Bucle que recorre todas las materias y las guarda en el mapa
    // 'materias_por_id' con la forma [id_materia] => nombre.
    while($row = $result->fetch_assoc())
    {
        $idMateria = (int)$row['id_materia'];
        $nombreMateria = $row['nombre'];
        $respuesta["materias_por_id"][$idMateria] = $nombreMateria;
    }

    // ---------------------------------------------------------------
    // Consulta 4: Obtener los espacios (aulas, salones, etc.).
    // ---------------------------------------------------------------
    // Por ahora se traen todos los registros de la tabla Espacio,
    // aunque el comentario indica que en el futuro se filtrarán solo los espacios libres.
    $sql = "SELECT * FROM Espacio";
    $result = SQL::valueQuery($con, $sql, "");
    if($result instanceof ErrorDB) Respuestas::enviarError($result);

    // Bucle que recorre todos los espacios y los agrega al arreglo 'espacios_libres'.
    while($row = $result->fetch_assoc())
    {
        $respuesta["espacios_libres"][] = $row;
    }

    // ---------------------------------------------------------------
    // Consulta 5: Obtener los periodos (7:00-7:40, 7:45 - 8:35, etc).
    // ---------------------------------------------------------------
    $sql = "SELECT * FROM Periodo";
    $result = SQL::valueQuery($con, $sql, "");
    if($result instanceof ErrorDB) Respuestas::enviarError($result);

    // Bucle que recorre todos los periodos y los agrega al arreglo 'periodos'.
    while($row = $result->fetch_assoc())
    {
        $respuesta["periodos"][] = $row;
    }

    // ---------------------------------------------------------------
    // Envío de la respuesta final.
    // ---------------------------------------------------------------
    // Si todas las consultas se ejecutaron correctamente, se envía una respuesta
    // con el código de éxito y el contenido JSON generado.
    Respuestas::enviarOk($respuesta);
}
?>

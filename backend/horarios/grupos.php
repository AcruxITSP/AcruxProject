<?php
// Se incluyen los archivos necesarios que contienen funciones auxiliares, conexiones y manejo de errores
require_once dirname(__FILE__).'/../other/connection.php';     // conexión a la base de datos
require_once dirname(__FILE__).'/../other/sql.php';             // funciones para ejecutar consultas SQL
require_once dirname(__FILE__).'/../other/time.php';            // utilidades relacionadas con el tiempo/horas
require_once dirname(__FILE__).'/../other/respuestas.php';      // utilidades para enviar respuestas en formato estándar
require_once dirname(__FILE__).'/../other/db_errors.php';       // manejo de errores de base de datos
require_once dirname(__FILE__).'/../util/timing.php';           // utilidades de medición de tiempo
require_once dirname(__FILE__).'/../util/reserva_recursos.php'; // funciones relacionadas con reservas de recursos

// Si la solicitud HTTP es de tipo GET, se ejecuta el siguiente bloque
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    // Se obtienen los parámetros enviados por GET: el id del grupo y el nombre del día
    $idGrupo = $_GET['id_grupo'];
    $nombreDia = $_GET['dia'];

    // Se establece la conexión con la base de datos
    $con = connectDb();

    /*
        CONSULTA SQL:
        Esta consulta obtiene el horario de un grupo en un día específico.
        Une múltiples tablas para reunir información completa sobre:
        - los módulos del grupo
        - los intervalos de tiempo
        - la materia, profesor y aula (espacio)
        - y el día correspondiente.
    */
    $sql = "SELECT
                mo.id_modulo,                                      -- ID del módulo
                p.numero AS numero_intervalo,                      -- número del intervalo (orden del período)
                TIME_FORMAT(p.entrada, '%H:%i') AS hora_inicio,    -- hora de inicio formateada (HH:mm)
                TIME_FORMAT(p.salida, '%H:%i') AS hora_final,      -- hora de finalización formateada (HH:mm)
                pr.id_profesor,                                    -- ID del profesor
                m.nombre AS nombre_materia,                        -- nombre de la materia
                CONCAT(u.nombre, ' ', u.apellido) AS nombre_profesor, -- nombre completo del profesor
                CONCAT(e.tipo, ' ', e.numero) AS nombre_espacio    -- tipo y número del espacio (aula, laboratorio, etc.)
            FROM Grupo g
            JOIN Modulo mo         ON mo.id_grupo = g.id_grupo     -- relación grupo → módulos
            JOIN Hora h            ON h.id_hora = mo.id_hora       -- relación módulo → hora
            JOIN Periodo p         ON p.id_periodo = h.id_periodo  -- relación hora → período (intervalo horario)
            LEFT JOIN Clase c      ON c.id_clase = mo.id_clase     -- relación opcional módulo → clase
            LEFT JOIN Materia m    ON m.id_materia = c.id_materia  -- clase → materia (si existe)
            LEFT JOIN Profesor pr  ON pr.id_profesor = c.id_profesor -- clase → profesor (si existe)
            LEFT JOIN Usuario u    ON u.id_usuario = pr.id_usuario -- profesor → datos personales del usuario
            LEFT JOIN Espacio e    ON e.id_espacio = mo.id_espacio -- módulo → espacio físico
            JOIN Dia d             ON d.id_dia = h.id_dia          -- hora → día de la semana
            WHERE g.id_grupo = ?                                  -- se filtra por el grupo específico
            AND d.nombre = ?                                       -- y por el nombre del día
            ORDER BY p.numero";                                   // se ordena por número de período (orden cronológico)

    // Se ejecuta la consulta utilizando un método seguro con parámetros (previene inyección SQL)
    // Tipos: "i" → entero, "s" → string (por eso "is" para id_grupo y nombre_dia)
    $result = SQL::valueQuery($con, $sql, "is", $idGrupo, $nombreDia);

    // Si hubo un error en la base de datos, se envía la respuesta de error y se termina la ejecución
    if($result instanceof ErrorDB) Respuestas::enviarError($result);

    // Se prepara un array para guardar todas las filas devueltas
    $respuesta = [];

    // BUCLE: se recorren todas las filas del resultado y se agregan al array de respuesta
    while($row = $result->fetch_assoc()) 
        $respuesta[] = $row;

    // Finalmente se envía la respuesta con formato OK y el array JSON con los datos del horario
    Respuestas::enviarOk($respuesta);
}
?>

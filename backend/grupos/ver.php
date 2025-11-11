<?php
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';

// PUEDE TIRAR LOS CÓDIGOS DE ERROR:
// - NECESITA_LOGIN

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{
    // Verifica que el usuario esté logueado
    if (!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");

    $con = connectDb();

    // Obtiene todos los grupos con su curso y adscrito (si existen)
    $sql = "
        SELECT 
            g.id_grupo,
            g.grado,
            g.nombre,
            g.id_curso,
            c.nombre AS curso_nombre,
            g.id_adscrito,
            u.id_usuario AS adscrito_id_usuario,
            u.nombre AS adscrito_nombre,
            u.apellido AS adscrito_apellido
        FROM Grupo g
        LEFT JOIN Curso c ON g.id_curso = c.id_curso
        LEFT JOIN Adscrito a ON g.id_adscrito = a.id_adscrito
        LEFT JOIN Usuario u ON a.id_usuario = u.id_usuario
        ORDER BY g.grado ASC, g.nombre ASC
    ";

    $result = SQL::valueQuery($con, $sql, "");
    if ($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    $grupos = [];
    while ($row = $result->fetch_assoc())
    {
        $grupos[] = [
            "id_grupo" => (int)$row['id_grupo'],
            "grado" => $row['grado'],
            "nombre" => $row['nombre'],
            "curso" => $row['id_curso'] ? [
                "id_curso" => (int)$row['id_curso'],
                "nombre" => $row['curso_nombre']
            ] : null,
            "adscrito" => $row['id_adscrito'] ? [
                "id_adscrito" => (int)$row['id_adscrito'],
                "id_usuario" => (int)$row['adscrito_id_usuario'],
                "nombre" => $row['adscrito_nombre'],
                "apellido" => $row['adscrito_apellido']
            ] : null
        ];
    }

    Respuestas::enviarOk($grupos, $con);
}
?>

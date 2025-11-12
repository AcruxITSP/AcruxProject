<?php
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    // Comprueba si el usuario está logueado
    $estasLogeado = isset($_SESSION['id_usuario']);

    $con = connectDb();

    // Obtenemos todos los usuarios y determinamos su cargo según relaciones:
    // - Si existe en Profesor => "Profesor"
    // - Else si existe en Adscrito => "Adscrito"
    // - Else => "Usuario"
    $sql = "
        SELECT
            u.id_usuario,
            u.nombre,
            u.apellido,
            CASE
                WHEN p.id_profesor IS NOT NULL THEN 'Profesor'
                WHEN a.id_adscrito IS NOT NULL THEN 'Adscrito'
                ELSE 'Usuario'
            END AS cargo,
            u.ci,
            u.email
        FROM Usuario u
        LEFT JOIN Profesor p ON u.id_usuario = p.id_usuario
        LEFT JOIN Adscrito a ON u.id_usuario = a.id_usuario
        ORDER BY u.apellido ASC, u.nombre ASC
    ";

    $result = SQL::valueQuery($con, $sql, "");
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    $usuarios = [];
    while($row = $result->fetch_assoc())
    {
        $usuario = [
            "nombre" => $row['nombre'],
            "apellido" => $row['apellido'],
            "cargo" => $row['cargo']
        ];

        // Solo añadimos datos sensibles si está logueado
        if($estasLogeado)
        {
            $usuario["ci"] = $row['ci'];
            $usuario["email"] = $row['email'];
        }

        $usuarios[] = $usuario;
    }

    Respuestas::enviarOk($usuarios, $con);
}
?>

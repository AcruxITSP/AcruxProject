<?php
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';

// PUEDE TIRAR LOS CODIGOS DE ERROR:
// - NECESITA_LOGIN
if($_SERVER['REQUEST_METHOD'] == 'GET')
{

    $con = connectDb();

    $sql = "
        SELECT 
            u.id_usuario,
            u.nombre,
            u.apellido,
            u.ci,
            u.email,
            p.id_profesor
        FROM Profesor p
        JOIN Usuario u ON u.id_usuario = p.id_usuario
        ORDER BY u.apellido, u.nombre
    ";

    $result = SQL::valueQuery($con, $sql, "");
    if($result instanceof ErrorDB)
        Respuestas::enviarError($result, $con);

    $profesores = [];
    while($row = $result->fetch_assoc())
        $profesores[] = $row;

    Respuestas::enviarOk($profesores, $con);
}
?>

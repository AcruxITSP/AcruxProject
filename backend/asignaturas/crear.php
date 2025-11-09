<?php
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';
require_once dirname(__FILE__).'/../util/strings.php';

// PUEDE TIRAR LOS CODIGOS DE ERROR:
// - NECESITA_LOGIN
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if(!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");

    if(!isset($_POST['nombre'])) Respuestas::enviarError("NECESITA_NOMBRE");
    if(!isset($_POST['id_profesores'])) Respuestas::enviarError("NECESITA_ID_PROFESORES");

    $nombre = $_POST['nombre'];
    $idProfesores = $_POST['id_profesores'];

    $con = connectDb();
    $con->begin_transaction();

    $sql = "INSERT INTO materia(nombre) VALUES (?)";
    $result = SQL::actionQuery($con, $sql, "s", $nombre);
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    $idMateria = $con->insert_id;

    foreach($idProfesores as $idProfesor)
    {
        $sql = "INSERT INTO clase(id_materia, id_profesor) VALUES (?, ?)";
        $result = SQL::actionQuery($con, $sql, "ii", $idMateria, $idProfesor);
        if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
    }

    Respuestas::enviarOk(null, $con);
}
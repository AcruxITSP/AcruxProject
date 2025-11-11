<?php
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';
require_once dirname(__FILE__).'/../util/strings.php';

// PUEDE TIRAR LOS CÓDIGOS DE ERROR:
// - NECESITA_LOGIN
// - NECESITA_CI
// - NECESITA_NOMBRE
// - NECESITA_APELLIDO
// - NECESITA_CONTRASENA
// - NECESITA_EMAIL
// - USUARIO_EXISTE
// - NECESITA_MATERIAS

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if(!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");

    if(!isset($_POST['ci'])) Respuestas::enviarError("NECESITA_CI");
    if(!isset($_POST['nombre'])) Respuestas::enviarError("NECESITA_NOMBRE");
    if(!isset($_POST['apellido'])) Respuestas::enviarError("NECESITA_APELLIDO");
    if(!isset($_POST['contrasena'])) Respuestas::enviarError("NECESITA_CONTRASENA");
    if(!isset($_POST['email'])) Respuestas::enviarError("NECESITA_EMAIL");

    $ci = $_POST['ci'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $contrasena = $_POST['contrasena'];
    $email = $_POST['email'];
    $materias = $_POST['materias'] ?? [];

    // Encripta la contraseña
    $hash = password_hash($contrasena, PASSWORD_BCRYPT);

    $con = connectDb();
    $con->begin_transaction();

    // Verificar existencia previa
    $sql = "SELECT id_usuario FROM Usuario WHERE ci = ? OR email = ?";
    $check = SQL::valueQuery($con, $sql, "ss", $ci, $email);
    if($check instanceof ErrorDB) Respuestas::enviarError($check, $con);

    if($check->num_rows > 0)
        Respuestas::enviarError("USUARIO_EXISTE", $con);

    // Crear usuario
    $sql = "INSERT INTO Usuario(ci, nombre, apellido, contrasena, email) VALUES (?, ?, ?, ?, ?)";
    $result = SQL::actionQuery($con, $sql, "sssss", $ci, $nombre, $apellido, $hash, $email);
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    $id_usuario = $con->insert_id;

    // Crear profesor
    $sql = "INSERT INTO Profesor(id_usuario) VALUES (?)";
    $result = SQL::actionQuery($con, $sql, "i", $id_usuario);
    if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

    $id_profesor = $con->insert_id;

    // Crear relaciones con materias
    foreach($materias as $id_materia)
    {
        if(!is_numeric($id_materia)) continue;

        $sql = "INSERT INTO Clase(id_profesor, id_materia) VALUES (?, ?)";
        $res = SQL::actionQuery($con, $sql, "ii", $id_profesor, intval($id_materia));
        if($res instanceof ErrorDB) Respuestas::enviarError($res, $con);
    }

    Respuestas::enviarOk(["id_usuario" => $id_usuario, "id_profesor" => $id_profesor], $con);
}
?>

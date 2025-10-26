<?php
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';

if(!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");
$con = connectDb();
$idRecurso = (int)$_POST['id'];
$result = SQL::actionQuery($con, "DELETE FROM recurso WHERE id_recurso = ?", "i", $idRecurso);
Respuestas::enviar($result);
?>
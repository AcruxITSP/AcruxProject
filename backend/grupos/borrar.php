<?php
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';

if(!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");
$con = connectDb();
$idGrupo = (int)$_POST['id'];
$sql = "DELETE FROM grupo WHERE id_grupo = ?";
$result = SQL::actionQuery($con, $sql, "i", $idGrupo);
Respuestas::enviar($result);
?>
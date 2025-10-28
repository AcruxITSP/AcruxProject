<?php
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';

if(!isset($_SESSION['id_adscripto'])) Respuestas::enviarError("NECESITA_LOGIN_ADSCRIPTO");
$con = connectDb();
$idCurso = (int)$_POST['id'];
$result = SQL::actionQuery($con, "DELETE FROM curso WHERE id_curso = ?", "i", $idCurso);
Respuestas::enviar($result);
?>
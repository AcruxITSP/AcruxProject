<?php
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';
require_once dirname(__FILE__).'/../util/timing.php';
require_once dirname(__FILE__).'/../util/reserva_recursos.php';


if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    unset($_SESSION);
    session_destroy();
    Respuestas::enviarOk();
}

?>
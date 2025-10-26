<?php
@session_start();

function estaLogeado()
{
    return isset($_SESSION['id_usuario']);
}

function esAdscripto()
{
    return isset($_SESSION['id_adscripto']);
}

function esProfesor()
{
    return isset($_SESSION['id_profesor']);
}

?>
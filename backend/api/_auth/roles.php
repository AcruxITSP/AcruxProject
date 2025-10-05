<?php
function isLoginAdministrador(): bool
{
    @session_start();
    if (!isset($_SESSION["rol"])) return false;
    return $_SESSION['rol'] === 'administrador';
}

function isLoginAdscripta(): bool
{
    @session_start();
    if (!isset($_SESSION["rol"])) return false;
    return $_SESSION['rol'] === 'adscripta';
}

function isLoginSecretario(): bool
{
    @session_start();
    if (!isset($_SESSION["rol"])) return false;
    return $_SESSION['rol'] === 'secretario';
}

function isLoginProfesor(): bool
{
    @session_start();
    if (!isset($_SESSION["rol"])) return false;
    return $_SESSION['rol'] === 'profesor';
}

function isLoginAuxiliar(): bool
{
    @session_start();
    if (!isset($_SESSION["rol"])) return false;
    return $_SESSION['rol'] === 'auxiliar';
}

function isLoginEstudiante(): bool
{
    @session_start();
    if (!isset($_SESSION["rol"])) return false;
    return $_SESSION['rol'] === 'estudiante';
}
?>
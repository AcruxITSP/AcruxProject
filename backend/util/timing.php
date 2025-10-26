<?php
date_default_timezone_set('America/Montevideo');
function obtenerHoraActual() { return date('H:i:s'); }
function obtenerFechaActual() { return date('Y-m-d'); }
function obtenerNombreDiaActual()
{
    $timestamp = time();
    $numeroDiaSemana = date('w', $timestamp);
    return ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'][$numeroDiaSemana];
}

function obtenerNumeroDiaActual()
{
    $timestamp = time();
    return (int)date('w', $timestamp);
}

function obtenerNumeroDiaPorNombre($nombre)
{
    $timestamp = time();
    return 1+array_search($nombre, ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo']);
}
?>
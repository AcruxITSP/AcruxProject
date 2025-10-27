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

function obtenerFechaPrimerDiaSemanaActual() : string
{
    $fechaActual = obtenerFechaActual(); // Y-m-d
    $numeroDiaDeSemanaActual = obtenerNumeroDiaActual(); // Si hoy es lunes = 1

    // Calcula cuántos días hay que restar para llegar al lunes
    $diasARestar = $numeroDiaDeSemanaActual - 1;

    // Crea un objeto DateTime y resta los días necesarios
    $fecha = new DateTime($fechaActual);
    if ($diasARestar > 0) {
        $fecha->modify("-{$diasARestar} days");
    }

    return $fecha->format("Y-m-d");
}

function obtenerFechaEnSemanaActual($numeroDia) : string
{
    $fechaPrimerDiaSemanaActual = obtenerFechaPrimerDiaSemanaActual();
    $diasASumar = $numeroDia - 1;

    $fecha = new DateTime($fechaPrimerDiaSemanaActual);
    if ($diasASumar > 0) {
        $fecha->modify("+{$diasASumar} days");
    }

    return $fecha->format("Y-m-d");
}


?>
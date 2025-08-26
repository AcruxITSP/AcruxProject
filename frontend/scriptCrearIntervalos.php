<?php
// Aca comienza el codigo
include 'globalFunctions.php';

$conn = iniciarConexion();

if (hayRegistros("Intervalo")) {
    $conn->close();
    exit("ERROR: Ya hay un horario registrado");
    // Se le debe dar la opcion al usuario de sobrescribir los registros ya existentes
}

// Reinicia los valores de las claves primarias para que empiecen a contar desde 1
resetAutoIncrement("Intervalo");

// Tomar los valores del formulario
$horaIn = $_POST["horaIn"];
$minsIn = $_POST["minsIn"];
$horaFin = $_POST["horaFin"];
$minsFin = $_POST["minsFin"];
$claseDur = $_POST["claseDuracion"];
$recreo = $_POST["recreo"];

// Pasar las horas a su valor en minutos 
$horaExactaIn = $horaIn * 60 + $minsIn;
$horaExactaFin = $horaFin * 60 + $minsFin;

// Calcular la variacion de tiempo en minutos 
$varTiempo = $horaExactaFin - $horaExactaIn;

if ($claseDur > $varTiempo) {
    exit("ERROR: El horario no puede tener menos de una clase al día");
}

// Verificar que el horario sea divisible segun los valores ingresados 
$resto = ($varTiempo + $recreo) % ($claseDur + $recreo);

if (!($resto == 0)) {
    echo "El horario no es divisible D:";

    /* DARLE LA OPCION DE QUITAR EL EXCEDENTE AL USUARIO */

} else {
    // Calcular cuantos intervalos caben en un dia
    $cantHoras = ($varTiempo + $recreo) / ($claseDur + $recreo);
    echo "El horario tendra $cantHoras horas <br>";

    $horaEntrada = $horaIn;
    $minEntrada = $minsIn;

    $horaSalida = $horaIn;
    $minSalida = $minsIn;

    // Ingresar los intervalos a la BD (Se excluyen los tiempos de recreo)
    for ($i = 1; $i <= $cantHoras; $i++) {
        // Se calcula a qué hora terminará el intervalo
        sumarMinutos($claseDur);

        // Junta las horas con los minutos en un String
        $timeEntrada = toTime($horaEntrada, $minEntrada);
        $timeSalida = toTime($horaSalida, $minSalida);

        // Ingresa el registro en la base de datos
        insertIntervalo();

        // Devuelve las horas y minutos a su valor numerico
        $horaSalida = (int) $horaSalida;
        $minSalida = (int) $minSalida;

        // Se agrega el tiempo de recreo a la hora de salida
        sumarMinutos($recreo);

        // La hora de salida del intervalo anterior se convierte en la hora de entrada del siguiente
        $horaEntrada = $horaSalida;
        $minEntrada = $minSalida;
    }
}

relacionarHorario();


function sumarMinutos($cantidad)
{
    global $minSalida, $horaSalida;

    $minSalida += $cantidad;

    // Cuando se alcanzan los 60 minutos, se suma 1 hora
    while ($minSalida >= 60) {
        $horaSalida += 1;
        $minSalida -= 60;
    }
}

function addZero($numero)
{
    // "str_pad()" permite agregarle caracteres a un string hasta que se alcanze una cierta longitud
    $numeroTexto = str_pad($numero, 2, "0");
    return $numeroTexto;
}

function toTime($hora, $minuto)
{
    addZero($minuto);
    $hora = (string) $hora;

    return "$hora:$minuto";
}

function insertIntervalo()
{
    global $conn, $timeEntrada, $timeSalida;

    $query = $conn->prepare("INSERT INTO Intervalo (Entrada, Salida) VALUES (?, ?);");
    $query->bind_param("ss", $timeEntrada, $timeSalida);
    $query->execute();
}
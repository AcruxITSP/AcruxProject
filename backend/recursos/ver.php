<?php
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';
require_once dirname(__FILE__).'/../util/periodos.php';
require_once dirname(__FILE__).'/../util/reserva_recursos.php';
require_once dirname(__FILE__).'/../util/espacios.php';

/*
$respuesta = [
    "recursos_externos" => [
        ["id_recurso" => 1, "tipo" => "Control", "id_espacio" => 1, "cantidad_libre" => 1, "cantidad_ocupado" => 2],
        ["id_recurso" => 2, "tipo" => "HDMI", "id_espacio" => null, "cantidad_libre" => 7, "cantidad_ocupado" => 5]
    ],
    "recursos_internos" => [
        ["id_recurso" => 3, "tipo" => "TV", "cantidad_en_espacios" => [["id_espacio" => 1, "cantidad" => 2], ["id_espacio" => 2, "cantidad" => 3] ]],
        ["id_recurso" => 4, "tipo" => "AIRE", "cantidad_en_espacios" => [["id_espacio" => 2, "cantidad" => 1] ]],
    ],
    "espacios" => [
        1 => ["id_espacio" => 1, "tipo" => "Aula", "numero" => 3, "libre" => false],
        2 => ["id_espacio" => 2, "tipo" => "Aula", "numero" => 5, "libre" => true],
    ]
];
*/

// Variable que contiene los datos a enviar.
$respuesta = [
    "recursos_externos" => [],
    "recursos_internos" => [],
    "espacios" => []
];

$con = connectDb();

// Obtener la fecha y periodo actua.
$fechaActual = obtenerFechaActual();
$periodoCercano = obtenerPeriodoCercanoOFuturo($con);
$periodoCercano_horaInicio = $periodoCercano['entrada'];
$periodoCercano_horaFinal = $periodoCercano['salida'];

// Obten los recursos externos
$sql = "SELECT *
        FROM recursoexterno, recurso 
        WHERE recursoexterno.id_recurso = recurso.id_recurso";
$resultRecursoExterno = SQL::valueQuery($con, $sql, "");
if($resultRecursoExterno instanceof ErrorDB) Respuestas::enviarError($resultRecursoExterno);

// Guardar los datos de cada recurso externo en la respuesta.
while($recursoExterno = $resultRecursoExterno->fetch_assoc())
{
    $datosRecursoExternoAEnviar = [];

    // Indica que se enviaran ciertos atributos
    $datosRecursoExternoAEnviar['id_recurso'] = $recursoExterno['id_recurso'];
    $datosRecursoExternoAEnviar['tipo'] = $recursoExterno['tipo'];
    $datosRecursoExternoAEnviar['id_espacio'] = $recursoExterno['id_espacio'];

    // Calcula la cantidad total, libre y icupada
    $idRecursoExterno = (int)$recursoExterno['id_recurso_externo'];
    $idRecurso = (int)$recursoExterno['id_recurso'];
    $cantidadTotal = (int)$recursoExterno['cantidad_total'];
    $cantidadLibre = obtenerCantidadLibrePorAhora($con, $idRecurso);
    $cantidadOcupado = $cantidadTotal - $cantidadLibre;
    
    $datosRecursoExternoAEnviar['cantidad_ocupado'] = $cantidadOcupado;
    $datosRecursoExternoAEnviar['cantidad_libre'] = $cantidadLibre;

    // Agrega los datos calculados de este recurso externo a la respuesta.
    $respuesta["recursos_externos"][] = $datosRecursoExternoAEnviar;
}

// Obten los recursos internos
$sql = "SELECT *
        FROM recursointerno, recurso 
        WHERE recursointerno.id_recurso = recurso.id_recurso";
$resultRecursoInterno = SQL::valueQuery($con, $sql, "");
if($resultRecursoInterno instanceof ErrorDB) Respuestas::enviarError($resultRecursoInterno);

// Guardar los datos de cada recurso interno en la respuesta.
while($recursoInterno = $resultRecursoInterno->fetch_assoc())
{
    $datosRecursoInternoAEnviar = $recursoInterno;
    $datosRecursoInternoAEnviar['cantidad_en_espacios'] = [];

    $sql = "SELECT eri.id_espacio, eri.cantidad
            FROM espacio_recursointerno as eri
            WHERE eri.id_recurso_interno = ?";
    $resultEspaciosRecursoInterno = SQL::valueQuery($con, $sql, "i", $recursoInterno['id_recurso_interno']);
    if($resultEspaciosRecursoInterno instanceof ErrorDB) Respuestas::enviarError($resultEspaciosRecursoInterno);
    while($espacio = $resultEspaciosRecursoInterno->fetch_assoc())
    {
        $datosRecursoInternoAEnviar['cantidad_en_espacios'][] = [
            "id_espacio" => $espacio['id_espacio'],
            "cantidad" => $espacio['cantidad']
        ];
    }


    $respuesta["recursos_internos"][] = $datosRecursoInternoAEnviar;
}

// Guardar todos los espacios existentes en la respuesta
$sql = "SELECT * FROM espacio";
$resultEspacio = SQL::valueQuery($con, $sql, "");
if($resultEspacio instanceof ErrorDB) Respuestas::enviarError($resultEspacio);
while($espacio = $resultEspacio->fetch_assoc())
{
    $idEspacio = $espacio['id_espacio'];
    $respuesta['espacios_por_id'][$idEspacio] = $espacio;
    $respuesta['espacios_por_id'][$idEspacio]['libre'] = espacioEstaLibreAhora($con, $idEspacio);
}

// Enviar la respuesta como OK.
Respuestas::enviarOk($respuesta);
?>
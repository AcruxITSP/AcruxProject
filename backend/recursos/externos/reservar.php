<?php
@session_start();
require_once dirname(__FILE__).'/../../other/connection.php';
require_once dirname(__FILE__).'/../../other/sql.php';
require_once dirname(__FILE__).'/../../other/time.php';
require_once dirname(__FILE__).'/../../other/respuestas.php';
require_once dirname(__FILE__).'/../../other/db_errors.php';
require_once dirname(__FILE__).'/../../util/timing.php';
require_once dirname(__FILE__).'/../../util/reserva_recursos.php';

// - HORAS_NO_ESPECIFICADAS
// - CANTIDAD_NO_ESPECIFICADA
// - NECESITA_LOGIN
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if(!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");
    $idUsuario = $_SESSION['id_usuario'];

    // si no se han seleccionado horas a reservar, error.
    if(!isset($_POST['horas'])) Respuestas::enviarError("HORAS_NO_ESPECIFICADAS");
    if(!isset($_POST['cantidad-a-reservar'])) Respuestas::enviarError("CANTIDAD_NO_ESPECIFICADA");


    // La id del recurso base
    $idRecurso = $_POST['id_recurso'];
    // La cantidad del recurso a reservar
    $cantidadAReservar = $_POST['cantidad-a-reservar'];
    // El numero de los periodos por los cuales el recurso sera reservado
    // Ejemplo: [1, 3, 4] -> 7:00-7:45, 8:40-9:25 y 10:20-11:05
    $numerosPeriodos = $_POST['horas'];

    $con = connectDb();

    // Buscar la id del recurso externo a partir del id del recurso base
    $sql = "SELECT id_recurso_externo
            FROM Recurso, RecursoExterno
            WHERE Recurso.id_recurso = ?
            AND Recurso.id_recurso = RecursoExterno.id_recurso";
    $result = SQL::valueQuery($con, $sql, "i", $idRecurso);
    if($result instanceof ErrorDB) Respuestas::enviarError($result);
    $idRecursoExterno = (int)$result->fetch_assoc()['id_recurso_externo'];

    // Trae los periodos por los cuales se reservara el recurso

    // genera un string -> ?, ?, ? dependiendo de la cantidad de numeros
    $placeholders = implode(', ', array_fill(0, count(value: $numerosPeriodos), '?'));
    // genera un string -> iii dependiendo de la cantidad de numeros
    $types = implode(array_fill(0, count($numerosPeriodos), 'i'));
    // Ahora si, traemos los periodos seleccionados
    $sql = "SELECT * FROM Periodo WHERE numero IN ($placeholders)";
    $result = SQL::valueQuery($con, $sql, $types, ...$numerosPeriodos);
    if($result instanceof ErrorDB) Respuestas::enviarError($result);

    $periodos = [];
    while($periodo = $result->fetch_assoc())
    {
        $periodos[] = $periodo;
    }

    // Busca cual es la hora final de la reserva
    $horaFinal = $periodos[count($periodos)-1]['salida'];

    // Insertamos la reserva
    $fechaActual = obtenerFechaActual();
    $sql = "INSERT INTO ReservaRecurso (id_usuario, id_recurso_externo, fecha, cantidad, hora_final)
            VALUES (?, ?, ?, ?, ?)";
    $result = SQL::actionQuery($con, $sql, "iisis", $idUsuario, $idRecursoExterno, $fechaActual, $cantidadAReservar, $horaFinal);
    if($result instanceof ErrorDB) Respuestas::enviarError($result);
    $idReserva = $con->insert_id;

    // Insertamos los periodos de la reserva

    // Extraemos las id de los periodos a partir de los numeros de los periodos
    foreach ($periodos as $periodo)
    {
        $idPeriodo = $periodo['id_periodo'];
        $sql = "INSERT INTO PeriodoReservaRecurso (id_reserva, id_periodo)
                VALUES (?, ?)";
        $result = SQL::actionQuery($con, $sql, "ii", $idReserva, $idPeriodo);
        if($result instanceof ErrorDB) Respuestas::enviarError($result);
    }

    Respuestas::enviarOk();
}

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $idRecurso = (int)($_GET['id_recurso'] ?? 0);

    /*
    $datos = [
        "intervalos" => [
            ["numero_intervalo" => 1, "inicio" => "7:00", "final" => "7:45"],
            ["numero_intervalo" => 2, "inicio" => "7:50", "final" => "8:40"]
        ],
        "recurso" => [
            "tipo" => "HDMI",
            "espacio" => ["tipo" => "Aula", "numero" => 5],
            // "espacio" => null
            "cantidades_por_numero_intervalo" =>
            [
                1 => 5,
                2 => 2
            ]
        ]
    ];
    */
    $con = connectDb();

    $sql = "SELECT numero, entrada, salida FROM Periodo ORDER BY numero";
    $result = SQL::valueQuery($con, $sql, "");
    if($result instanceof ErrorDB) Respuestas::enviarError($result);

    while ($row = $result->fetch_assoc())
    {
        $intervalos[] = [
            "numero_intervalo" => (int)$row["numero"],
            "inicio" => substr($row["entrada"], 0, 5),
            "final" => substr($row["salida"], 0, 5)
        ];
    }

    $sql = "SELECT R.id_recurso, R.tipo, RE.cantidad_total, E.tipo AS tipo_espacio, E.numero AS numero_espacio
            FROM Recurso R, RecursoExterno RE
            LEFT JOIN Espacio E ON E.id_espacio = RE.id_espacio
            WHERE R.id_recurso = ?
            AND R.id_recurso = RE.id_recurso
            LIMIT 1";
    $resultRecurso = SQL::valueQuery($con, $sql, "i", $idRecurso);
    if($resultRecurso instanceof ErrorDB) Respuestas::enviarError($resultRecurso);
    $recursoRow = $resultRecurso->fetch_assoc();

    $cantidades = [];
    foreach ($intervalos as $intervalo) {
        $cantidades[$intervalo["numero_intervalo"]] = obtenerCantidadLibrePorPeriodo($con, $idRecurso, $intervalo["numero_intervalo"]);
    }

    $datos = [
        "intervalos" => $intervalos,
        "recurso" => [
            "tipo" => $recursoRow["tipo"],
            "espacio" => $recursoRow["tipo_espacio"]
                ? [
                    "tipo" => $recursoRow["tipo_espacio"],
                    "numero" => $recursoRow["numero_espacio"] !== null ? (int)$recursoRow["numero_espacio"] : null
                ]
                : null,
            "cantidades_por_numero_intervalo" => $cantidades
        ]
    ];

    Respuestas::enviarOk($datos);
}

?>
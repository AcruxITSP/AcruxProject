<?php
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';
require_once dirname(__FILE__).'/../util/strings.php';

// PUEDE TIRAR LOS CODIGOS DE ERROR:
// - NECESITA_LOGIN
// - LOCALIDAD_INVALIDA
// - TIPO_EN_USO
// - TIPO_QUIZAS_EN_USO
// - ESPACIO_REQUERIDO
// - CANTIDAD_INVALIDA
// - ESPACIO_NO_EXISTE
// - LOCALIDAD_NO_ESPECIFICADA
// - TIPO_NO_ESPECIFICADO
// - CANTIDAD_NO_ESPECIFICADA
// - ESPACIO_NO_ESPECIFICADO
//
// - ESPACIOS_NO_ESPECIFICADOS
// - CANTIDADES_NO_ESPECIFICADAS
// - ESPACIO_YA_ESPECIFICADO
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    if(!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");
    // Validar que se haya especificado la localidad
    if(!isset($_POST['localidad'])) Respuestas::enviarError("LOCALIDAD_NO_ESPECIFICADA");

    // Validar que se haya especificado el tipo
    if(!isset($_POST['tipo'])) Respuestas::enviarError("TIPO_NO_ESPECIFICADO");
    $tipo = trim($_POST['tipo']);
    if(esVacioOEspacios($tipo)) Respuestas::enviarError("TIPO_NO_ESPECIFICADO");

    if($_POST['localidad'] == 'externo')
    {
        // Validad la cantidad total del recurso a registrar
        // Ejemplo:
        //      5 -> Se insertara un registro del recurso con la columna 'cantidad_total' indicando cuantos
        //      recursos de este tipo hay fisicamente.
        if(!isset($_POST['cantidad_total'])) Respuestas::enviarError("CANTIDAD_NO_ESPECIFICADA");
        $cantidadTotal = (int)($_POST['cantidad_total'] ?? 1);
        
        // La id del espacio a la cual este recurso externo es especifico
        // Ejemplo:
        //      Escoba           -> null
        //      Control Salon 5  -> (id del espacio Salon 5)
        //      Control Salon    -> (id del espacio Salon5)
        $idEspacio = $_POST['id_espacio'] ?? null;
        if($idEspacio == 0) $idEspacio = null;

        // Conectar a la base de datos y comenzar una transaccion
        $con = connectDb();
        $con->begin_transaction();

        // Enviar error TIPO_EN_USO si ya existe un recurso de este tipo.
        if(existeRecursoDelTipo($con, $tipo)) Respuestas::enviarError("TIPO_EN_USO", $con);

        // Enviar error ESPACIO_NO_EXISTE si el espacio no existe.
        if($idEspacio != null && !existeEspacio($con, $idEspacio)) 
            Respuestas::enviarError("ESPACIO_NO_EXISTE", $con);

        // Validar que cantidad total sea mayor a 0.
        if($cantidadTotal <= 0) Respuestas::enviarError("CANTIDAD_INVALIDA", $con);

        // Insertar 'recurso'
        $sql = "INSERT INTO recurso (tipo) VALUES (?)";
        $result = SQL::actionQuery($con, $sql, "s", $tipo);
        if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
        // Obtener la PK del recurso insertado
        $idRecurso = $con->insert_id;

        // Insertar 'recursoexterno'
        $sql = "INSERT INTO recursoexterno (id_recurso, id_espacio, cantidad_total) VALUES (?, ?, ?)";
        $result = SQL::actionQuery($con, $sql, "iii", $idRecurso, $idEspacio, $cantidadTotal);
        if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
        
        // Enviar ok, no enviar ningun valor y pasamos la coneccion para terminar
        // la transaccion y coneccion
        $con->commit();
        Respuestas::enviarOk(null, $con);
    }
    elseif($_POST['localidad'] == 'interno')
    {
        $id_espacios = $_POST['id_espacios'] ?? null;
        if($id_espacios == null) Respuestas::enviarError('ESPACIOS_NO_ESPECIFICADOS');

        $cantidades = $_POST['cantidades'] ?? null;
        if($cantidades == null) Respuestas::enviarError('CANTIDADES_NO_ESPECIFICADAS');

        // Conectar a la base de datos y comenzar una transaccion
        $con = connectDb();
        $con->begin_transaction();

        // Enviar error TIPO_EN_USO si ya existe un recurso de este tipo.
        if(existeRecursoDelTipo($con, $tipo)) Respuestas::enviarError("TIPO_EN_USO", $con);

        // Insertar 'recurso'
        $sql = "INSERT INTO recurso (tipo) VALUES (?)";
        $result = SQL::actionQuery($con, $sql, "s", $tipo);
        if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
        $idRecurso = $con->insert_id;

        // Insertar 'recursointerno'
        $sql = "INSERT INTO recursointerno (id_recurso) VALUES (?)";
        $result = SQL::actionQuery($con, $sql, "i", $idRecurso);
        if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
        $idRecursoInterno = $con->insert_id;

        // Relacionar el recurso interno con los espacios
        $idEspaciosYaEspecificados = [];
        for($index = 0; $index < count($id_espacios); ++$index)
        {
            $idEspacio = $id_espacios[$index];
            $cantidad = $cantidades[$index];

            if(in_array($idEspacio, $idEspaciosYaEspecificados))
                Respuestas::enviarError('ESPACIO_YA_ESPECIFICADO', $con);    

            $sql = "INSERT INTO espacio_recursointerno (id_recurso_interno, id_espacio, cantidad) VALUES (?, ?, ?)";
            $result = SQL::actionQuery($con, $sql, "iii", $idRecursoInterno, $idEspacio, $cantidad);
            if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

            // Indicar que este espacio ya fue agregado
            $idEspaciosYaEspecificados[] = $idEspacio;
        }

        // Enviar ok, no enviar ningun valor y pasamos la coneccion para terminar
        // la transaccion y coneccion
        $con->commit();
        Respuestas::enviarOk(null, $con);
    }
    else
    {
        Respuestas::enviarError('LOCALIDAD_INVALIDA');
    }
}

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $con = connectDb();

    $sql = "SELECT tipo FROM recurso";
    $result = SQL::valueQuery($con, $sql, "");
    if($result instanceof ErrorDB) Respuestas::enviarError($result);

    $tipos = [];
    while($row = $result->fetch_assoc()) $tipos[] = $row['tipo'];

    $sql = "SELECT * FROM espacio";
    $result = SQL::valueQuery($con, $sql, "");
    if($result instanceof ErrorDB) Respuestas::enviarError($result);

    $espacios = [];
    while($row = $result->fetch_assoc()) $espacios[] = $row;

    Respuestas::enviarOk([
        "tipos_recursos" => $tipos,
        "espacios" => $espacios
    ]);
}

/**
 * Verifica si ya existe un recurso del tipo $tipo.
 * Devuelve un bool o directamente envia un error.
 * Los codigo de errores son:
 * - TIPO_QUIZAS_EN_USO
 * @param mixed $tipo
 * @return void
 */
function existeRecursoDelTipo($con, $tipo) : bool
{
    $sql = "SELECT COUNT(tipo) as cantidad FROM recurso WHERE tipo = ?";
    $result = SQL::valueQuery($con, $sql, "s", $tipo);
    if($result instanceof ErrorDB) Respuestas::enviarError("TIPO_QUIZAS_EN_USO");
    $cantidad = (int)$result->fetch_assoc()['cantidad'];
    return $cantidad > 0;
}

/**
 * Verifica si el espacio existe.
 * Devuelve un bool o directamente envia un error.
 * Los codigo de errores son:
 * - ESPACIO_QUIZAS_NO_EXISTE
 * @param mixed $tipo
 * @return void
 */
function existeEspacio($con, $idEspacio) : bool
{
    $sql = "SELECT COUNT(tipo) as cantidad FROM espacio WHERE id_espacio = ?";
    $result = SQL::valueQuery($con, $sql, "i", $idEspacio);
    if($result instanceof ErrorDB) Respuestas::enviarError("ESPACIO_QUIZAS_NO_EXISTE");
    $cantidad = (int)$result->fetch_assoc()['cantidad'];
    return $cantidad > 0;
}
?>
<?php
@session_start();
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';
require_once dirname(__FILE__).'/../util/timing.php';
require_once dirname(__FILE__).'/../util/reserva_recursos.php';

// - NECESITA_LOGIN
// - FALTA_ID_MODULO
// - FALTA_MATERIA
// - FALTA_PROFESOR
// - FALTA_ESPACIO
// - FALTA_NUMERO_INTERVALO
// - FALTA_NOMBRE_DIA
// - HORA_INSUFICIENTE
// - CLASE_NO_EXISTE
// - MODULO_NO_EXISTE
// - ESPACIO_NO_EXISTE

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	if(!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");

	if(!isset($_POST['id_modulo'])) Respuestas::enviarError("FALTA_ID_MODULO");
	if(!isset($_POST['materia'])) Respuestas::enviarError("FALTA_MATERIA");
	if(!isset($_POST['profesor'])) Respuestas::enviarError("FALTA_PROFESOR");
	if(!isset($_POST['espacio'])) Respuestas::enviarError("FALTA_ESPACIO");

	$idModulo = $_POST['id_modulo'];
	$idMateria = $_POST['materia'];
	$idProfesor = $_POST['profesor'];
	$idEspacio = $_POST['espacio'];

	$con = connectDb();
	$con->begin_transaction();

	// Validar que el módulo exista
	$sql = "SELECT COUNT(*) AS c FROM Modulo WHERE id_modulo = ?";
	$result = SQL::valueQuery($con, $sql, "i", $idModulo);
	if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
	if(intval($result->fetch_assoc()['c']) == 0) Respuestas::enviarError("MODULO_NO_EXISTE", $con);

	// Validar que el espacio exista
	$sql = "SELECT COUNT(*) AS c FROM Espacio WHERE id_espacio = ?";
	$result = SQL::valueQuery($con, $sql, "i", $idEspacio);
	if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
	if(intval($result->fetch_assoc()['c']) == 0) Respuestas::enviarError("ESPACIO_NO_EXISTE", $con);

	// Obtener id de clase
	$sql = "SELECT id_clase FROM Clase WHERE id_profesor = ? AND id_materia = ?";
	$result = SQL::valueQuery($con, $sql, "ii", $idProfesor, $idMateria);
	if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
	if($result->num_rows == 0) Respuestas::enviarError("CLASE_NO_EXISTE", $con);

	$idClase = $result->fetch_assoc()['id_clase'];

	// Actualizar módulo existente
	$sql = "UPDATE Modulo SET id_clase = ?, id_espacio = ? WHERE id_modulo = ?";
	$result = SQL::actionQuery($con, $sql, "iii", $idClase, $idEspacio, $idModulo);
	if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);

	Respuestas::enviarOk(null, $con);
}

// GET — obtener el estado actual de un módulo
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_SESSION['id_usuario'])) Respuestas::enviarError("NECESITA_LOGIN");
	if(!isset($_GET['id_modulo'])) Respuestas::enviarError("FALTA_ID_MODULO");

	$idModulo = $_GET['id_modulo'];
	$con = connectDb();

	$sql = "
		SELECT 
			m.id_modulo,
			cl.id_materia,
			cl.id_profesor,
			e.id_espacio,
			CONCAT(e.tipo, IF(e.numero IS NOT NULL, CONCAT(' ', e.numero), '')) AS nombre_espacio,
			d.nombre AS nombre_dia,
			p.numero AS numero_intervalo
		FROM Modulo m
		INNER JOIN Clase cl ON m.id_clase = cl.id_clase
		INNER JOIN Espacio e ON m.id_espacio = e.id_espacio
		INNER JOIN Hora h ON m.id_hora = h.id_hora
		INNER JOIN Dia d ON h.id_dia = d.id_dia
		INNER JOIN Periodo p ON h.id_periodo = p.id_periodo
		WHERE m.id_modulo = ?
	";

	$result = SQL::valueQuery($con, $sql, "i", $idModulo);
	if($result instanceof ErrorDB) Respuestas::enviarError($result, $con);
	if($result->num_rows == 0) Respuestas::enviarError("MODULO_NO_EXISTE", $con);

	$row = $result->fetch_assoc();

	$data = [
		"id_modulo" => intval($row["id_modulo"]),
		"id_materia" => intval($row["id_materia"]),
		"id_profesor" => intval($row["id_profesor"]),
		"id_espacio" => intval($row["id_espacio"]),
		"nombre_espacio" => $row["nombre_espacio"],
		"nombre_dia" => $row["nombre_dia"],
		"numero_intervalo" => intval($row["numero_intervalo"])
	];

	Respuestas::enviarOk($data);
}
?>

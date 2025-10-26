<?php
require_once dirname(__FILE__).'/../other/connection.php';
require_once dirname(__FILE__).'/../other/sql.php';
require_once dirname(__FILE__).'/../other/time.php';
require_once dirname(__FILE__).'/../other/respuestas.php';
require_once dirname(__FILE__).'/../other/db_errors.php';
require_once dirname(__FILE__).'/../util/timing.php';
require_once dirname(__FILE__).'/../util/reserva_recursos.php';

// Agrega un modulo
// - HORA_INSUFICIENTE
if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	// Obten los datos necesarios para registrar la hora
	$idMateria = $_POST['materia'];
	$idProfesor = $_POST['profesor'];
	$idEspacio = $_POST['espacio'];
	$numeroIntervalo = $_POST['numero_intervalo'];
	$nombreDia = $_POST['nombre_dia'];
	$idGrupo = $_POST['grupo'];

	// Conectar a la bd
	$con = connectDb();

	// Obtener id de hora (relacion de dia y periodo)
	// a partir del numero de periodo y nombre del dia
	$sql = "SELECT id_hora FROM Hora
			WHERE Hora.id_periodo = (SELECT id_periodo FROM Periodo WHERE numero = ?)
			AND Hora.id_dia = (SELECT id_dia FROM Dia WHERE nombre = ?)";
	// Ejecutamos la consulta
	$result = SQL::valueQuery($con, $sql, "is", $numeroIntervalo, $nombreDia);
	// Si la consulta fallo, enviamos el error y terminamos el php
	if($result instanceof ErrorDB) Respuestas::enviarError($result);
	// Si no existe ninguna hora
	if($result->num_rows == 0)
	{
		Respuestas::enviarError('HORA_INSUFICIENTE');
	}
	// Ahora si tenemos el id de la hora
	$idHora = $result->fetch_assoc()['id_hora'];

	// Obtener id de clase
	$sql = "SELECT id_clase FROM Clase
			WHERE Clase.id_profesor = ?
			AND Clase.id_materia = ?";
	$result = SQL::valueQuery($con, $sql, "ii", $idProfesor, $idMateria);
	if($result instanceof ErrorDB) Respuestas::enviarError($result);
	$idClase = $result->fetch_assoc()['id_clase'];

	// Ahora creamos el modulo con el id_hora, id_clase, id_espacio e id_grupo
	$sql = "INSERT INTO Modulo (id_hora, id_clase, id_espacio, id_grupo)
			VALUES (?, ?, ?, ?)";
	$result = SQL::actionQuery($con, $sql, "iiii", $idHora, $idClase, $idEspacio, $idGrupo);
	if($result instanceof ErrorDB) Respuestas::enviarError($result);

	// Enviar que la operacion termino correctamente
	return Respuestas::enviarOk();

}
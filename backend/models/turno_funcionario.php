<?php
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum TurnoFuncionarioError : string
{
    case NOT_FOUND = "TURNOFUNCIONARIO_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "TURNOFUNCIONARIO_UNKNOWN_DUPLICATE";
	
}

/**
* Los atributos correspondientes a las columnas binarias de la tabla (como cualquier tipo de BLOB)
* almacenan los datos codificados en base64 en lugar del binario sin procesar.
*
* Cualquier operación que implique devolver un blob devolverá un binario codificado en base64.
*
* Cualquier operación que acepte un blob como entrada esperará un binario sin procesar.
*/
class TurnoFuncionario extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "01999b0e-df0b-7857-b66c-96174c301501";

    protected mysqli $con;
	public int $idFuncionario;
	public int $idTurno;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, int $idFuncionario, int $idTurno)
	{
	    $this->con = $con;
		$this->idFuncionario = $idFuncionario;
		$this->idTurno = $idTurno;
	}

	#region CREATE
	/** 
	* Crea un nuevo TurnoFuncionario en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, int $idFuncionario, int $idTurno) : TurnoFuncionario|TurnoFuncionarioError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Id_funcionario' para la consulta de inserción SQL.
		$columns[] = "Id_funcionario";
		$placeholders[] = "?";
		$values[] = $idFuncionario;
		$types .= 'i';
		
		// Añade 'Id_turno' para la consulta de inserción SQL.
		$columns[] = "Id_turno";
		$placeholders[] = "?";
		$values[] = $idTurno;
		$types .= 'i';
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::NO_VALUES;
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Turno_Funcionario (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $stmt->bind_param($types, ...$values);
		
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    // TurnoFuncionario se ha creado exitosamente.
	    $stmt->close();
	    return self::getByFKs($con, $idFuncionario, $idTurno);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un 'TurnoFuncionario' de la base de datos identificando 'TurnoFuncionario' por sus claves externas como una clave compuesta. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getByFKs(mysqli $con, int $idFuncionario, int $idTurno) : TurnoFuncionario|TurnoFuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT * FROM Turno_Funcionario WHERE Id_funcionario = ? AND Id_turno = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $stmt->bind_param("ii", $idFuncionario, $idTurno);
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    // Resultado
	    $result = $stmt->get_result();
	    if(!$result) return ErrorDB::RESULT;
	    if($result->num_rows === 0)
	    {
	        $result->free();
	        return TurnoFuncionarioError::NOT_FOUND;
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self (
			$con,
			(int)($row['Id_funcionario']),
			(int)($row['Id_turno'])
	    );
	    
	    $stmt->close();
	    $result->free();
	    return $instance;
	}
	#endregion GET
	
	
	
	
	#region SET — IdFuncionario
	/**
	* Establece el valor Id_funcionario de TurnoFuncionario a partir de la base de datos que identifica TurnoFuncionario mediante sus claves externas como clave compuesta. 
	*/
	public static function setIdFuncionarioByFKs(mysqli $con, int $idFuncionario, int $idTurno, int $newIdFuncionario) : true|TurnoFuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Turno_Funcionario SET Id_funcionario = ? WHERE Id_funcionario = ? AND Id_turno = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("iii", $newIdFuncionario, $idFuncionario, $idTurno);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — IdFuncionario
	
	
	#region SET — IdTurno
	/**
	* Establece el valor Id_turno de TurnoFuncionario a partir de la base de datos que identifica TurnoFuncionario mediante sus claves externas como clave compuesta. 
	*/
	public static function setIdTurnoByFKs(mysqli $con, int $idFuncionario, int $idTurno, int $newIdTurno) : true|TurnoFuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Turno_Funcionario SET Id_turno = ? WHERE Id_funcionario = ? AND Id_turno = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("iii", $newIdTurno, $idFuncionario, $idTurno);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — IdTurno
}
?>
<?php
include_once '../error_base.php';
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum TurnoGrupoErrorType : string
{
    case NOT_FOUND = "TURNOGRUPO_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "TURNOGRUPO_UNKNOWN_DUPLICATE";
	
}

abstract class TurnoGrupoError extends ErrorBase
{
    public static function notFound() : TurnoGrupoError
    {
        return new self(TurnoGrupoErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : TurnoGrupoError
    {
        return new self(TurnoGrupoErrorType::UNKNOWN_DUPLICATE, $pair);
    }


}

/**
* Los atributos correspondientes a las columnas binarias de la tabla (como cualquier tipo de BLOB)
* almacenan los datos codificados en base64 en lugar del binario sin procesar.
*
* Cualquier operación que implique devolver un blob devolverá un binario codificado en base64.
*
* Cualquier operación que acepte un blob como entrada esperará un binario sin procesar.
*/
class TurnoGrupo extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b13b-f10a-7f5d-af23-d0988ecc39fd";

    protected mysqli $con;
	public int $idTurno;
	public int $idGrupo;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, int $idTurno, int $idGrupo)
	{
	    $this->con = $con;
		$this->idTurno = $idTurno;
		$this->idGrupo = $idGrupo;
	}

	#region CREATE
	/** 
	* Crea un nuevo TurnoGrupo en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, int $idTurno, int $idGrupo) : TurnoGrupo|TurnoGrupoError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Id_turno' para la consulta de inserción SQL.
		$columns[] = "Id_turno";
		$placeholders[] = "?";
		$values[] = $idTurno;
		$types .= 'i';
		
		// Añade 'Id_grupo' para la consulta de inserción SQL.
		$columns[] = "Id_grupo";
		$placeholders[] = "?";
		$values[] = $idGrupo;
		$types .= 'i';
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Turno_Grupo');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Turno_Grupo (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param($types, ...$values);
		
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {
	        // Gestionar entradas duplicadas
	        if($stmt->errno == 1062)
	        {
	            $msg = $stmt->error;
	            if (preg_match("/Duplicate entry '(.+)' for key '(.+)'/", $msg, $matches))
	            {
	                $duplicateValue = $matches[1];
	                $duplicateKey   = $matches[2];
					
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return TurnoGrupoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // TurnoGrupo se ha creado exitosamente.
	    $stmt->close();
	    return self::getByFKs($con, $idTurno, $idGrupo);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un 'TurnoGrupo' de la base de datos identificando 'TurnoGrupo' por sus claves externas como una clave compuesta. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getByFKs(mysqli $con, int $idTurno, int $idGrupo) : TurnoGrupo|TurnoGrupoError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT * FROM Turno_Grupo WHERE Id_turno = ? AND Id_grupo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("ii", $idTurno, $idGrupo);
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Resultado
	    $result = $stmt->get_result();
	    if(!$result) return ErrorDB::result($sql);
	    if($result->num_rows === 0)
	    {
	        $result->free();
	        return TurnoGrupoError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self (
			$con,
			(int)($row['Id_turno']),
			(int)($row['Id_grupo'])
	    );
	    
	    $stmt->close();
	    $result->free();
	    return $instance;
	}
	#endregion GET
	
	
	
	
	#region SET — IdTurno
	/**
	* Establece el valor Id_turno de TurnoGrupo a partir de la base de datos que identifica TurnoGrupo mediante sus claves externas como clave compuesta. 
	*/
	public static function setIdTurnoByFKs(mysqli $con, int $idTurno, int $idGrupo, int $newIdTurno) : true|TurnoGrupoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Turno_Grupo SET Id_turno = ? WHERE Id_turno = ? AND Id_grupo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("iii", $newIdTurno, $idTurno, $idGrupo);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {
	        // Gestionar entradas duplicadas
	        if($stmt->errno == 1062)
	        {
	            $msg = $stmt->error;
	            if (preg_match("/Duplicate entry '(.+)' for key '(.+)'/", $msg, $matches))
	            {
	                $duplicateValue = $matches[1];
	                $duplicateKey   = $matches[2];
					
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return TurnoGrupoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdTurno
	
	
	#region SET — IdGrupo
	/**
	* Establece el valor Id_grupo de TurnoGrupo a partir de la base de datos que identifica TurnoGrupo mediante sus claves externas como clave compuesta. 
	*/
	public static function setIdGrupoByFKs(mysqli $con, int $idTurno, int $idGrupo, int $newIdGrupo) : true|TurnoGrupoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Turno_Grupo SET Id_grupo = ? WHERE Id_turno = ? AND Id_grupo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("iii", $newIdGrupo, $idTurno, $idGrupo);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {
	        // Gestionar entradas duplicadas
	        if($stmt->errno == 1062)
	        {
	            $msg = $stmt->error;
	            if (preg_match("/Duplicate entry '(.+)' for key '(.+)'/", $msg, $matches))
	            {
	                $duplicateValue = $matches[1];
	                $duplicateKey   = $matches[2];
					
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return TurnoGrupoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdGrupo
	
	
	
	
	#region DELETE
	/**
	* Borra un 'TurnoGrupo' de la base de datos identificando 'TurnoGrupo' por sus claves externas como una clave compuesta. [OPTIONAL_BLOB_RETURN_COMMENT]
	*/
	public static function deleteByFKs(mysqli $con, int $idTurno, int $idGrupo) : true|TurnoGrupoError|ErrorDB
	{
	    // Preparacion
	    $sql = "DELETE FROM Turno_Grupo WHERE Id_turno = ? AND Id_grupo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("ii", $idTurno, $idGrupo);
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    $stmt->close();
	    return true;
	}
	#endregion DELETE
}
?>
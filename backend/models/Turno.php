<?php
require_once dirname(__FILE__).'/../error_base.php';
require_once dirname(__FILE__).'/../db/db_errors.php';
require_once dirname(__FILE__).'/base_model.php';

enum TurnoErrorType : string
{
    case NOT_FOUND = "TURNO_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "TURNO_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_TURNO = "TURNO_DUPLICATE_ID_TURNO";
}

class TurnoError extends ErrorBase
{
    private function __construct(mixed $type, mixed $data)
    {
        parent::__construct($type, $data);
    }

    public static function notFound() : TurnoError
    {
        return new self(TurnoErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : TurnoError
    {
        return new self(TurnoErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdTurno(mixed $pair) : TurnoError
    {
        return new self(TurnoErrorType::DUPLICATE_ID_TURNO, $pair);
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
class Turno extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199bae7-2cfc-7d50-ba15-f7b38b2e26ff";

    protected mysqli $con;
	public int $idTurno;
	public string $turno;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, int $idTurno, string $turno)
	{
	    $this->con = $con;
		$this->idTurno = $idTurno;
		$this->turno = $turno;
	}

	#region CREATE
	/** 
	* Crea un nuevo Turno en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string|int $idTurno = self::SQL_DEFAULT, string $turno = self::SQL_DEFAULT) : Turno|TurnoError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Id_turno' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idTurno !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_turno";
		    $placeholders[] = "?";
		    $values[] = $idTurno;
		    $types .= 'i';
		}
		
		// Añade 'Turno' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($turno !== self::SQL_DEFAULT)
		{
		    $columns[] = "Turno";
		    $placeholders[] = "?";
		    $values[] = $turno;
		    $types .= 's';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Turno');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Turno (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Id_turno')
					{
					    $stmt->close();
					    return TurnoError::duplicateIdTurno(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return TurnoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Turno se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Turno de la base de datos identificando Turno por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idTurno) : Turno|TurnoError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Turno WHERE Id_turno = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idTurno);
	
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
	        $stmt->close();
	        return TurnoError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(int)($row['Id_turno']),
			(string)($row['Turno'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — Turno
	/**
	* Obtiene el valor Turno de Turno de la base de datos identificando Turno por su clave primaria única. 
	*/
	public static function getTurnoById(mysqli $con, int $idTurno) : string|TurnoError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Turno FROM Turno WHERE Id_turno = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idTurno);
	
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
	        $stmt->close();
	        return TurnoError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Turno']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Turno
	
	
	
	
	#region SET — Turno
	/**
	* Establece el valor Turno de Turno a partir de la base de datos que identifica Turno por su clave principal. 
	*/
	public static function setTurnoById(mysqli $con, int $idTurno, string $newTurno) : true|TurnoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Turno SET Turno = ? WHERE Id_turno = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newTurno, $idTurno);
	
	
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
	                return TurnoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Turno
	
	
	
	
	#region DELETE
	//  [OPTIONAL_BLOB_RETURN_COMMENT]
	
	/**
	* Borra un Turno de la base de datos identificando Turno por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idTurno) : true|TurnoError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Turno WHERE Id_turno = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idTurno);
	
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
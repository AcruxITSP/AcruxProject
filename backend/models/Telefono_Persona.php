<?php
include_once '../error_base.php';
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum TelefonoPersonaErrorType : string
{
    case NOT_FOUND = "TELEFONOPERSONA_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "TELEFONOPERSONA_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_TEL = "TELEFONOPERSONA_DUPLICATE_ID_TEL";
	case DUPLICATE_TELEFONO = "TELEFONOPERSONA_DUPLICATE_TELEFONO";
}

class TelefonoPersonaError extends ErrorBase
{
    private function __construct(mixed $type, mixed $data)
    {
        parent::__construct($type, $data);
    }

    public static function notFound() : TelefonoPersonaError
    {
        return new self(TelefonoPersonaErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : TelefonoPersonaError
    {
        return new self(TelefonoPersonaErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdTel(mixed $pair) : TelefonoPersonaError
    {
        return new self(TelefonoPersonaErrorType::DUPLICATE_ID_TEL, $pair);
    }
    

    public static function duplicateTelefono(mixed $pair) : TelefonoPersonaError
    {
        return new self(TelefonoPersonaErrorType::DUPLICATE_TELEFONO, $pair);
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
class TelefonoPersona extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b22e-e0ae-754c-ad4f-6c8a47eaa900";

    protected mysqli $con;
	public int $idTel;
	public string $telefono;
	public int $idPersona;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $telefono, int $idPersona, int $idTel)
	{
	    $this->con = $con;
		$this->idTel = $idTel;
		$this->telefono = $telefono;
		$this->idPersona = $idPersona;
	}

	#region CREATE
	/** 
	* Crea un nuevo TelefonoPersona en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $telefono, int $idPersona, string|int $idTel = self::SQL_DEFAULT) : TelefonoPersona|TelefonoPersonaError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Telefono' para la consulta de inserción SQL.
		$columns[] = "Telefono";
		$placeholders[] = "?";
		$values[] = $telefono;
		$types .= 's';
		
		// Añade 'Id_persona' para la consulta de inserción SQL.
		$columns[] = "Id_persona";
		$placeholders[] = "?";
		$values[] = $idPersona;
		$types .= 'i';
		
		// Añade 'Id_tel' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idTel !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_tel";
		    $placeholders[] = "?";
		    $values[] = $idTel;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Telefono_Persona');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Telefono_Persona (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Telefono')
					{
					    $stmt->close();
					    return TelefonoPersonaError::duplicateTelefono(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
					if($duplicateKey == 'Id_tel')
					{
					    $stmt->close();
					    return TelefonoPersonaError::duplicateIdTel(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return TelefonoPersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // TelefonoPersona se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un TelefonoPersona de la base de datos identificando TelefonoPersona por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idTel) : TelefonoPersona|TelefonoPersonaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Telefono_Persona WHERE Id_tel = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idTel);
	
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
	        return TelefonoPersonaError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Telefono']),
			(int)($row['Id_persona']),
			(int)($row['Id_tel'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	
	/**
	* Obtiene un TelefonoPersona de la base de datos identificando TelefonoPersona por su Telefono único. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getByTelefono(mysqli $con, string $telefono) : TelefonoPersona|TelefonoPersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT * FROM Telefono_Persona WHERE Telefono = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $telefono);
	
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
	        $result->close();
	        $stmt->close();
	        return TelefonoPersonaError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Telefono']),
			(int)($row['Id_persona']),
			(int)($row['Id_tel'])
	    );
	
	    $result->close();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — IdTel
	/**
	* Obtiene el valor Id_tel de TelefonoPersona de la base de datos identificando TelefonoPersona por su Telefono único. 
	*/
	public static function getIdTelByTelefono(mysqli $con, string $telefono) : int|TelefonoPersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Id_tel FROM Telefono_Persona WHERE Telefono = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $telefono);
	
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
	        $result->close();
	        $stmt->close();
	        return TelefonoPersonaError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_tel']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdTel
	
	
	#region GET — Telefono
	/**
	* Obtiene el valor Telefono de TelefonoPersona de la base de datos identificando TelefonoPersona por su clave primaria única. 
	*/
	public static function getTelefonoById(mysqli $con, int $idTel) : string|TelefonoPersonaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Telefono FROM Telefono_Persona WHERE Id_tel = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idTel);
	
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
	        return TelefonoPersonaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Telefono']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Telefono de TelefonoPersona de la base de datos identificando TelefonoPersona por su Telefono único. 
	*/
	public static function getTelefonoByTelefono(mysqli $con, string $telefono) : string|TelefonoPersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Telefono FROM Telefono_Persona WHERE Telefono = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $telefono);
	
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
	        $result->close();
	        $stmt->close();
	        return TelefonoPersonaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Telefono']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Telefono
	
	
	#region GET — IdPersona
	/**
	* Obtiene el valor Id_persona de TelefonoPersona de la base de datos identificando TelefonoPersona por su clave primaria única. 
	*/
	public static function getIdPersonaById(mysqli $con, int $idTel) : int|TelefonoPersonaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_persona FROM Telefono_Persona WHERE Id_tel = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idTel);
	
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
	        return TelefonoPersonaError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_persona']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Id_persona de TelefonoPersona de la base de datos identificando TelefonoPersona por su Telefono único. 
	*/
	public static function getIdPersonaByTelefono(mysqli $con, string $telefono) : int|TelefonoPersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Id_persona FROM Telefono_Persona WHERE Telefono = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $telefono);
	
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
	        $result->close();
	        $stmt->close();
	        return TelefonoPersonaError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_persona']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdPersona
	
	
	
	
	#region SET — Telefono
	/**
	* Establece el valor Telefono de TelefonoPersona a partir de la base de datos que identifica TelefonoPersona por su clave principal. 
	*/
	public static function setTelefonoById(mysqli $con, int $idTel, string $newTelefono) : true|TelefonoPersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Telefono_Persona SET Telefono = ? WHERE Id_tel = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newTelefono, $idTel);
	
	
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
					if($duplicateKey == 'Telefono')
					{
					    $stmt->close();
					    return TelefonoPersonaError::duplicateTelefono(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return TelefonoPersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Telefono de TelefonoPersona desde la base de datos identificando TelefonoPersona por su Telefono único. 
	*/
	public static function setTelefonoByTelefono(mysqli $con, string $telefono, string $newTelefono) : true|TelefonoPersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Telefono_Persona SET Telefono = ? WHERE Telefono = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newTelefono, $telefono);
	
	
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
					if($duplicateKey == 'Telefono')
					{
					    $stmt->close();
					    return TelefonoPersonaError::duplicateTelefono(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return TelefonoPersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Telefono
	
	
	#region SET — IdPersona
	/**
	* Establece el valor Id_persona de TelefonoPersona a partir de la base de datos que identifica TelefonoPersona por su clave principal. 
	*/
	public static function setIdPersonaById(mysqli $con, int $idTel, int $newIdPersona) : true|TelefonoPersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Telefono_Persona SET Id_persona = ? WHERE Id_tel = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdPersona, $idTel);
	
	
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
	                return TelefonoPersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Id_persona de TelefonoPersona desde la base de datos identificando TelefonoPersona por su Telefono único. 
	*/
	public static function setIdPersonaByTelefono(mysqli $con, string $telefono, int $newIdPersona) : true|TelefonoPersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Telefono_Persona SET Id_persona = ? WHERE Telefono = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("is", $newIdPersona, $telefono);
	
	
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
	                return TelefonoPersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdPersona
	
	
	
	
	#region DELETE
	//  [OPTIONAL_BLOB_RETURN_COMMENT]
	
	/**
	* Borra un TelefonoPersona de la base de datos identificando TelefonoPersona por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idTel) : true|TelefonoPersonaError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Telefono_Persona WHERE Id_tel = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idTel);
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    $stmt->close();
	    return true;
	}
	
	/**
	* Borra un TelefonoPersona de la base de datos identificando TelefonoPersona por su Telefono único.
	*/
	public static function deleteByTelefono(mysqli $con, string $telefono) : true|TelefonoPersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "DELETE FROM Telefono_Persona WHERE Telefono = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $telefono);
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion DELETE
}
?>
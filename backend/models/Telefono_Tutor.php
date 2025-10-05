<?php
require_once dirname(__FILE__).'/../error_base.php';
require_once dirname(__FILE__).'/../db/db_errors.php';
require_once dirname(__FILE__).'/base_model.php';

enum TelefonoTutorErrorType : string
{
    case NOT_FOUND = "TELEFONOTUTOR_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "TELEFONOTUTOR_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_TEL = "TELEFONOTUTOR_DUPLICATE_ID_TEL";
	case DUPLICATE_TELEFONO = "TELEFONOTUTOR_DUPLICATE_TELEFONO";
}

class TelefonoTutorError extends ErrorBase
{
    private function __construct(mixed $type, mixed $data)
    {
        parent::__construct($type, $data);
    }

    public static function notFound() : TelefonoTutorError
    {
        return new self(TelefonoTutorErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : TelefonoTutorError
    {
        return new self(TelefonoTutorErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdTel(mixed $pair) : TelefonoTutorError
    {
        return new self(TelefonoTutorErrorType::DUPLICATE_ID_TEL, $pair);
    }
    

    public static function duplicateTelefono(mixed $pair) : TelefonoTutorError
    {
        return new self(TelefonoTutorErrorType::DUPLICATE_TELEFONO, $pair);
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
class TelefonoTutor extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b599-f654-76c4-b0e1-c29433a08cc3";

    protected mysqli $con;
	public int $idTel;
	public string $telefono;
	public string $nombretutor;
	public int $idEstudiante;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $telefono, string $nombretutor, int $idEstudiante, int $idTel)
	{
	    $this->con = $con;
		$this->idTel = $idTel;
		$this->telefono = $telefono;
		$this->nombretutor = $nombretutor;
		$this->idEstudiante = $idEstudiante;
	}

	#region CREATE
	/** 
	* Crea un nuevo TelefonoTutor en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $telefono, string $nombretutor, int $idEstudiante, string|int $idTel = self::SQL_DEFAULT) : TelefonoTutor|TelefonoTutorError|ErrorDB
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
		
		// Añade 'NombreTutor' para la consulta de inserción SQL.
		$columns[] = "NombreTutor";
		$placeholders[] = "?";
		$values[] = $nombretutor;
		$types .= 's';
		
		// Añade 'Id_estudiante' para la consulta de inserción SQL.
		$columns[] = "Id_estudiante";
		$placeholders[] = "?";
		$values[] = $idEstudiante;
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
	        return ErrorDB::noValues('Telefono_Tutor');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Telefono_Tutor (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					    return TelefonoTutorError::duplicateTelefono(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
					if($duplicateKey == 'Id_tel')
					{
					    $stmt->close();
					    return TelefonoTutorError::duplicateIdTel(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return TelefonoTutorError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // TelefonoTutor se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un TelefonoTutor de la base de datos identificando TelefonoTutor por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idTel) : TelefonoTutor|TelefonoTutorError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Telefono_Tutor WHERE Id_tel = ?";
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
	        return TelefonoTutorError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Telefono']),
			(string)($row['NombreTutor']),
			(int)($row['Id_estudiante']),
			(int)($row['Id_tel'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	
	/**
	* Obtiene un TelefonoTutor de la base de datos identificando TelefonoTutor por su Telefono único. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getByTelefono(mysqli $con, string $telefono) : TelefonoTutor|TelefonoTutorError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT * FROM Telefono_Tutor WHERE Telefono = ?";
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
	        return TelefonoTutorError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Telefono']),
			(string)($row['NombreTutor']),
			(int)($row['Id_estudiante']),
			(int)($row['Id_tel'])
	    );
	
	    $result->close();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — IdTel
	/**
	* Obtiene el valor Id_tel de TelefonoTutor de la base de datos identificando TelefonoTutor por su Telefono único. 
	*/
	public static function getIdTelByTelefono(mysqli $con, string $telefono) : int|TelefonoTutorError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Id_tel FROM Telefono_Tutor WHERE Telefono = ?";
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
	        return TelefonoTutorError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_tel']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdTel
	
	
	#region GET — Telefono
	/**
	* Obtiene el valor Telefono de TelefonoTutor de la base de datos identificando TelefonoTutor por su clave primaria única. 
	*/
	public static function getTelefonoById(mysqli $con, int $idTel) : string|TelefonoTutorError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Telefono FROM Telefono_Tutor WHERE Id_tel = ?";
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
	        return TelefonoTutorError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Telefono']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Telefono de TelefonoTutor de la base de datos identificando TelefonoTutor por su Telefono único. 
	*/
	public static function getTelefonoByTelefono(mysqli $con, string $telefono) : string|TelefonoTutorError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Telefono FROM Telefono_Tutor WHERE Telefono = ?";
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
	        return TelefonoTutorError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Telefono']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Telefono
	
	
	#region GET — Nombretutor
	/**
	* Obtiene el valor NombreTutor de TelefonoTutor de la base de datos identificando TelefonoTutor por su clave primaria única. 
	*/
	public static function getNombretutorById(mysqli $con, int $idTel) : string|TelefonoTutorError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT NombreTutor FROM Telefono_Tutor WHERE Id_tel = ?";
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
	        return TelefonoTutorError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['NombreTutor']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor NombreTutor de TelefonoTutor de la base de datos identificando TelefonoTutor por su Telefono único. 
	*/
	public static function getNombretutorByTelefono(mysqli $con, string $telefono) : string|TelefonoTutorError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT NombreTutor FROM Telefono_Tutor WHERE Telefono = ?";
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
	        return TelefonoTutorError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['NombreTutor']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Nombretutor
	
	
	#region GET — IdEstudiante
	/**
	* Obtiene el valor Id_estudiante de TelefonoTutor de la base de datos identificando TelefonoTutor por su clave primaria única. 
	*/
	public static function getIdEstudianteById(mysqli $con, int $idTel) : int|TelefonoTutorError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_estudiante FROM Telefono_Tutor WHERE Id_tel = ?";
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
	        return TelefonoTutorError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_estudiante']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Id_estudiante de TelefonoTutor de la base de datos identificando TelefonoTutor por su Telefono único. 
	*/
	public static function getIdEstudianteByTelefono(mysqli $con, string $telefono) : int|TelefonoTutorError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Id_estudiante FROM Telefono_Tutor WHERE Telefono = ?";
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
	        return TelefonoTutorError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_estudiante']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdEstudiante
	
	
	
	
	#region SET — Telefono
	/**
	* Establece el valor Telefono de TelefonoTutor a partir de la base de datos que identifica TelefonoTutor por su clave principal. 
	*/
	public static function setTelefonoById(mysqli $con, int $idTel, string $newTelefono) : true|TelefonoTutorError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Telefono_Tutor SET Telefono = ? WHERE Id_tel = ?";
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
					    return TelefonoTutorError::duplicateTelefono(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return TelefonoTutorError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Telefono de TelefonoTutor desde la base de datos identificando TelefonoTutor por su Telefono único. 
	*/
	public static function setTelefonoByTelefono(mysqli $con, string $telefono, string $newTelefono) : true|TelefonoTutorError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Telefono_Tutor SET Telefono = ? WHERE Telefono = ?";
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
					    return TelefonoTutorError::duplicateTelefono(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return TelefonoTutorError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Telefono
	
	
	#region SET — Nombretutor
	/**
	* Establece el valor NombreTutor de TelefonoTutor a partir de la base de datos que identifica TelefonoTutor por su clave principal. 
	*/
	public static function setNombretutorById(mysqli $con, int $idTel, string $newNombretutor) : true|TelefonoTutorError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Telefono_Tutor SET NombreTutor = ? WHERE Id_tel = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newNombretutor, $idTel);
	
	
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
	                return TelefonoTutorError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor NombreTutor de TelefonoTutor desde la base de datos identificando TelefonoTutor por su Telefono único. 
	*/
	public static function setNombretutorByTelefono(mysqli $con, string $telefono, string $newNombretutor) : true|TelefonoTutorError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Telefono_Tutor SET NombreTutor = ? WHERE Telefono = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newNombretutor, $telefono);
	
	
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
	                return TelefonoTutorError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Nombretutor
	
	
	#region SET — IdEstudiante
	/**
	* Establece el valor Id_estudiante de TelefonoTutor a partir de la base de datos que identifica TelefonoTutor por su clave principal. 
	*/
	public static function setIdEstudianteById(mysqli $con, int $idTel, int $newIdEstudiante) : true|TelefonoTutorError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Telefono_Tutor SET Id_estudiante = ? WHERE Id_tel = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdEstudiante, $idTel);
	
	
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
	                return TelefonoTutorError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Id_estudiante de TelefonoTutor desde la base de datos identificando TelefonoTutor por su Telefono único. 
	*/
	public static function setIdEstudianteByTelefono(mysqli $con, string $telefono, int $newIdEstudiante) : true|TelefonoTutorError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Telefono_Tutor SET Id_estudiante = ? WHERE Telefono = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("is", $newIdEstudiante, $telefono);
	
	
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
	                return TelefonoTutorError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdEstudiante
	
	
	
	
	#region DELETE
	//  [OPTIONAL_BLOB_RETURN_COMMENT]
	
	/**
	* Borra un TelefonoTutor de la base de datos identificando TelefonoTutor por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idTel) : true|TelefonoTutorError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Telefono_Tutor WHERE Id_tel = ?";
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
	* Borra un TelefonoTutor de la base de datos identificando TelefonoTutor por su Telefono único.
	*/
	public static function deleteByTelefono(mysqli $con, string $telefono) : true|TelefonoTutorError|ErrorDB
	{
	    // Preparacion
	    $sql = "DELETE FROM Telefono_Tutor WHERE Telefono = ?";
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
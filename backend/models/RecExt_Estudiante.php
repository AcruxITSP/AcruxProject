<?php
require_once dirname(__FILE__).'/../error_base.php';
require_once dirname(__FILE__).'/../db/db_errors.php';
require_once dirname(__FILE__).'/base_model.php';

enum RecextEstudianteErrorType : string
{
    case NOT_FOUND = "RECEXTESTUDIANTE_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "RECEXTESTUDIANTE_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_REGISTRO = "RECEXTESTUDIANTE_DUPLICATE_ID_REGISTRO";
}

class RecextEstudianteError extends ErrorBase
{
    private function __construct(mixed $type, mixed $data)
    {
        parent::__construct($type, $data);
    }

    public static function notFound() : RecextEstudianteError
    {
        return new self(RecextEstudianteErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : RecextEstudianteError
    {
        return new self(RecextEstudianteErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdRegistro(mixed $pair) : RecextEstudianteError
    {
        return new self(RecextEstudianteErrorType::DUPLICATE_ID_REGISTRO, $pair);
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
class RecextEstudiante extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b599-f67d-7313-a852-07c92c615fa8";

    protected mysqli $con;
	public int $idRegistro;
	public string $accion;
	public string $fechaHora;
	public int $idRecursoEx;
	public ?int $idSecretario;
	public int $idEstudiante;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $accion, string $fechaHora, int $idRecursoEx, ?int $idSecretario, int $idEstudiante, int $idRegistro)
	{
	    $this->con = $con;
		$this->idRegistro = $idRegistro;
		$this->accion = $accion;
		$this->fechaHora = $fechaHora;
		$this->idRecursoEx = $idRecursoEx;
		$this->idSecretario = $idSecretario;
		$this->idEstudiante = $idEstudiante;
	}

	#region CREATE
	/** 
	* Crea un nuevo RecextEstudiante en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $accion, string $fechaHora, int $idRecursoEx, ?int $idSecretario, int $idEstudiante, string|int $idRegistro = self::SQL_DEFAULT) : RecextEstudiante|RecextEstudianteError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Accion' para la consulta de inserción SQL.
		$columns[] = "Accion";
		$placeholders[] = "?";
		$values[] = $accion;
		$types .= 's';
		
		// Añade 'Fecha_Hora' para la consulta de inserción SQL.
		$columns[] = "Fecha_Hora";
		$placeholders[] = "?";
		$values[] = $fechaHora;
		$types .= 's';
		
		// Añade 'Id_recursoEx' para la consulta de inserción SQL.
		$columns[] = "Id_recursoEx";
		$placeholders[] = "?";
		$values[] = $idRecursoEx;
		$types .= 'i';
		
		// Añade 'Id_secretario' para la consulta de inserción SQL.
		$columns[] = "Id_secretario";
		$placeholders[] = "?";
		$values[] = $idSecretario;
		$types .= 'i';
		
		// Añade 'Id_estudiante' para la consulta de inserción SQL.
		$columns[] = "Id_estudiante";
		$placeholders[] = "?";
		$values[] = $idEstudiante;
		$types .= 'i';
		
		// Añade 'Id_registro' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idRegistro !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_registro";
		    $placeholders[] = "?";
		    $values[] = $idRegistro;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('RecExt_Estudiante');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO RecExt_Estudiante (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Id_registro')
					{
					    $stmt->close();
					    return RecextEstudianteError::duplicateIdRegistro(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return RecextEstudianteError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // RecextEstudiante se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un RecextEstudiante de la base de datos identificando RecextEstudiante por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idRegistro) : RecextEstudiante|RecextEstudianteError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM RecExt_Estudiante WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRegistro);
	
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
	        return RecextEstudianteError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Accion']),
			(string)($row['Fecha_Hora']),
			(int)($row['Id_recursoEx']),
			(int)($row['Id_secretario']),
			(int)($row['Id_estudiante']),
			(int)($row['Id_registro'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — Accion
	/**
	* Obtiene el valor Accion de RecextEstudiante de la base de datos identificando RecextEstudiante por su clave primaria única. 
	*/
	public static function getAccionById(mysqli $con, int $idRegistro) : string|RecextEstudianteError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Accion FROM RecExt_Estudiante WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRegistro);
	
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
	        return RecextEstudianteError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Accion']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Accion
	
	
	#region GET — FechaHora
	/**
	* Obtiene el valor Fecha_Hora de RecextEstudiante de la base de datos identificando RecextEstudiante por su clave primaria única. 
	*/
	public static function getFechaHoraById(mysqli $con, int $idRegistro) : string|RecextEstudianteError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Fecha_Hora FROM RecExt_Estudiante WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRegistro);
	
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
	        return RecextEstudianteError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Fecha_Hora']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — FechaHora
	
	
	#region GET — IdRecursoEx
	/**
	* Obtiene el valor Id_recursoEx de RecextEstudiante de la base de datos identificando RecextEstudiante por su clave primaria única. 
	*/
	public static function getIdRecursoExById(mysqli $con, int $idRegistro) : int|RecextEstudianteError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_recursoEx FROM RecExt_Estudiante WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRegistro);
	
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
	        return RecextEstudianteError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_recursoEx']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdRecursoEx
	
	
	#region GET — IdSecretario
	/**
	* Obtiene el valor Id_secretario de RecextEstudiante de la base de datos identificando RecextEstudiante por su clave primaria única. 
	*/
	public static function getIdSecretarioById(mysqli $con, int $idRegistro) : int|RecextEstudianteError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_secretario FROM RecExt_Estudiante WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRegistro);
	
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
	        return RecextEstudianteError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_secretario']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdSecretario
	
	
	#region GET — IdEstudiante
	/**
	* Obtiene el valor Id_estudiante de RecextEstudiante de la base de datos identificando RecextEstudiante por su clave primaria única. 
	*/
	public static function getIdEstudianteById(mysqli $con, int $idRegistro) : int|RecextEstudianteError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_estudiante FROM RecExt_Estudiante WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRegistro);
	
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
	        return RecextEstudianteError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_estudiante']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdEstudiante
	
	
	
	
	#region SET — Accion
	/**
	* Establece el valor Accion de RecextEstudiante a partir de la base de datos que identifica RecextEstudiante por su clave principal. 
	*/
	public static function setAccionById(mysqli $con, int $idRegistro, string $newAccion) : true|RecextEstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE RecExt_Estudiante SET Accion = ? WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newAccion, $idRegistro);
	
	
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
	                return RecextEstudianteError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Accion
	
	
	#region SET — FechaHora
	/**
	* Establece el valor Fecha_Hora de RecextEstudiante a partir de la base de datos que identifica RecextEstudiante por su clave principal. 
	*/
	public static function setFechaHoraById(mysqli $con, int $idRegistro, string $newFechaHora) : true|RecextEstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE RecExt_Estudiante SET Fecha_Hora = ? WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newFechaHora, $idRegistro);
	
	
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
	                return RecextEstudianteError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — FechaHora
	
	
	#region SET — IdRecursoEx
	/**
	* Establece el valor Id_recursoEx de RecextEstudiante a partir de la base de datos que identifica RecextEstudiante por su clave principal. 
	*/
	public static function setIdRecursoExById(mysqli $con, int $idRegistro, int $newIdRecursoEx) : true|RecextEstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE RecExt_Estudiante SET Id_recursoEx = ? WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdRecursoEx, $idRegistro);
	
	
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
	                return RecextEstudianteError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdRecursoEx
	
	
	#region SET — IdSecretario
	/**
	* Establece el valor Id_secretario de RecextEstudiante a partir de la base de datos que identifica RecextEstudiante por su clave principal. 
	*/
	public static function setIdSecretarioById(mysqli $con, int $idRegistro, int $newIdSecretario) : true|RecextEstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE RecExt_Estudiante SET Id_secretario = ? WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdSecretario, $idRegistro);
	
	
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
	                return RecextEstudianteError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdSecretario
	
	
	#region SET — IdEstudiante
	/**
	* Establece el valor Id_estudiante de RecextEstudiante a partir de la base de datos que identifica RecextEstudiante por su clave principal. 
	*/
	public static function setIdEstudianteById(mysqli $con, int $idRegistro, int $newIdEstudiante) : true|RecextEstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE RecExt_Estudiante SET Id_estudiante = ? WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdEstudiante, $idRegistro);
	
	
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
	                return RecextEstudianteError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
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
	* Borra un RecextEstudiante de la base de datos identificando RecextEstudiante por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idRegistro) : true|RecextEstudianteError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM RecExt_Estudiante WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRegistro);
	
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
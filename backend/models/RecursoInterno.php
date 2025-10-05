<?php
require_once dirname(__FILE__).'/../error_base.php';
require_once dirname(__FILE__).'/../db/db_errors.php';
require_once dirname(__FILE__).'/base_model.php';

enum RecursointernoErrorType : string
{
    case NOT_FOUND = "RECURSOINTERNO_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "RECURSOINTERNO_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_RECURSOIN = "RECURSOINTERNO_DUPLICATE_ID_RECURSOIN";
}

class RecursointernoError extends ErrorBase
{
    private function __construct(mixed $type, mixed $data)
    {
        parent::__construct($type, $data);
    }

    public static function notFound() : RecursointernoError
    {
        return new self(RecursointernoErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : RecursointernoError
    {
        return new self(RecursointernoErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdRecursoIn(mixed $pair) : RecursointernoError
    {
        return new self(RecursointernoErrorType::DUPLICATE_ID_RECURSOIN, $pair);
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
class Recursointerno extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b279-8eca-715d-9106-d932b4571e3b";

    protected mysqli $con;
	public int $idRecursoIn;
	public string $tipo;
	public string $estado;
	public string $problema;
	public int $idAula;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $tipo, int $idAula, int $idRecursoIn, string $estado, string $problema)
	{
	    $this->con = $con;
		$this->idRecursoIn = $idRecursoIn;
		$this->tipo = $tipo;
		$this->estado = $estado;
		$this->problema = $problema;
		$this->idAula = $idAula;
	}

	#region CREATE
	/** 
	* Crea un nuevo Recursointerno en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $tipo, int $idAula, string|int $idRecursoIn = self::SQL_DEFAULT, string $estado = self::SQL_DEFAULT, string $problema = self::SQL_DEFAULT) : Recursointerno|RecursointernoError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Tipo' para la consulta de inserción SQL.
		$columns[] = "Tipo";
		$placeholders[] = "?";
		$values[] = $tipo;
		$types .= 's';
		
		// Añade 'Id_aula' para la consulta de inserción SQL.
		$columns[] = "Id_aula";
		$placeholders[] = "?";
		$values[] = $idAula;
		$types .= 'i';
		
		// Añade 'Id_recursoIn' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idRecursoIn !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_recursoIn";
		    $placeholders[] = "?";
		    $values[] = $idRecursoIn;
		    $types .= 'i';
		}
		
		// Añade 'Estado' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($estado !== self::SQL_DEFAULT)
		{
		    $columns[] = "Estado";
		    $placeholders[] = "?";
		    $values[] = $estado;
		    $types .= 's';
		}
		
		// Añade 'Problema' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($problema !== self::SQL_DEFAULT)
		{
		    $columns[] = "Problema";
		    $placeholders[] = "?";
		    $values[] = $problema;
		    $types .= 's';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('RecursoInterno');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO RecursoInterno (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Id_recursoIn')
					{
					    $stmt->close();
					    return RecursointernoError::duplicateIdRecursoIn(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return RecursointernoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Recursointerno se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Recursointerno de la base de datos identificando Recursointerno por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idRecursoIn) : Recursointerno|RecursointernoError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM RecursoInterno WHERE Id_recursoIn = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRecursoIn);
	
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
	        return RecursointernoError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Tipo']),
			(int)($row['Id_aula']),
			(int)($row['Id_recursoIn']),
			(string)($row['Estado']),
			(string)($row['Problema'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — Tipo
	/**
	* Obtiene el valor Tipo de Recursointerno de la base de datos identificando Recursointerno por su clave primaria única. 
	*/
	public static function getTipoById(mysqli $con, int $idRecursoIn) : string|RecursointernoError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Tipo FROM RecursoInterno WHERE Id_recursoIn = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRecursoIn);
	
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
	        return RecursointernoError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Tipo']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Tipo
	
	
	#region GET — Estado
	/**
	* Obtiene el valor Estado de Recursointerno de la base de datos identificando Recursointerno por su clave primaria única. 
	*/
	public static function getEstadoById(mysqli $con, int $idRecursoIn) : string|RecursointernoError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Estado FROM RecursoInterno WHERE Id_recursoIn = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRecursoIn);
	
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
	        return RecursointernoError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Estado']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Estado
	
	
	#region GET — Problema
	/**
	* Obtiene el valor Problema de Recursointerno de la base de datos identificando Recursointerno por su clave primaria única. 
	*/
	public static function getProblemaById(mysqli $con, int $idRecursoIn) : string|RecursointernoError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Problema FROM RecursoInterno WHERE Id_recursoIn = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRecursoIn);
	
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
	        return RecursointernoError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Problema']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Problema
	
	
	#region GET — IdAula
	/**
	* Obtiene el valor Id_aula de Recursointerno de la base de datos identificando Recursointerno por su clave primaria única. 
	*/
	public static function getIdAulaById(mysqli $con, int $idRecursoIn) : int|RecursointernoError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_aula FROM RecursoInterno WHERE Id_recursoIn = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRecursoIn);
	
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
	        return RecursointernoError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_aula']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdAula
	
	
	
	
	#region SET — Tipo
	/**
	* Establece el valor Tipo de Recursointerno a partir de la base de datos que identifica Recursointerno por su clave principal. 
	*/
	public static function setTipoById(mysqli $con, int $idRecursoIn, string $newTipo) : true|RecursointernoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE RecursoInterno SET Tipo = ? WHERE Id_recursoIn = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newTipo, $idRecursoIn);
	
	
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
	                return RecursointernoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Tipo
	
	
	#region SET — Estado
	/**
	* Establece el valor Estado de Recursointerno a partir de la base de datos que identifica Recursointerno por su clave principal. 
	*/
	public static function setEstadoById(mysqli $con, int $idRecursoIn, string $newEstado) : true|RecursointernoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE RecursoInterno SET Estado = ? WHERE Id_recursoIn = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newEstado, $idRecursoIn);
	
	
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
	                return RecursointernoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Estado
	
	
	#region SET — Problema
	/**
	* Establece el valor Problema de Recursointerno a partir de la base de datos que identifica Recursointerno por su clave principal. 
	*/
	public static function setProblemaById(mysqli $con, int $idRecursoIn, string $newProblema) : true|RecursointernoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE RecursoInterno SET Problema = ? WHERE Id_recursoIn = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newProblema, $idRecursoIn);
	
	
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
	                return RecursointernoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Problema
	
	
	#region SET — IdAula
	/**
	* Establece el valor Id_aula de Recursointerno a partir de la base de datos que identifica Recursointerno por su clave principal. 
	*/
	public static function setIdAulaById(mysqli $con, int $idRecursoIn, int $newIdAula) : true|RecursointernoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE RecursoInterno SET Id_aula = ? WHERE Id_recursoIn = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdAula, $idRecursoIn);
	
	
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
	                return RecursointernoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdAula
	
	
	
	
	#region DELETE
	//  [OPTIONAL_BLOB_RETURN_COMMENT]
	
	/**
	* Borra un Recursointerno de la base de datos identificando Recursointerno por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idRecursoIn) : true|RecursointernoError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM RecursoInterno WHERE Id_recursoIn = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRecursoIn);
	
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
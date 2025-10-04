<?php
include_once '../error_base.php';
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum RecursoexternoErrorType : string
{
    case NOT_FOUND = "RECURSOEXTERNO_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "RECURSOEXTERNO_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_RECURSOEX = "RECURSOEXTERNO_DUPLICATE_ID_RECURSOEX";
}

abstract class RecursoexternoError extends ErrorBase
{
    public static function notFound() : RecursoexternoError
    {
        return new self(RecursoexternoErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : RecursoexternoError
    {
        return new self(RecursoexternoErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdRecursoEx(mixed $pair) : RecursoexternoError
    {
        return new self(RecursoexternoErrorType::DUPLICATE_ID_RECURSOEX, $pair);
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
class Recursoexterno extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b13b-f154-7c45-a15d-bf482af08d01";

    protected mysqli $con;
	public int $idRecursoEx;
	public string $tipo;
	public bool $disponible;
	public int $idAula;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $tipo, int $idAula, int $idRecursoEx, bool $disponible)
	{
	    $this->con = $con;
		$this->idRecursoEx = $idRecursoEx;
		$this->tipo = $tipo;
		$this->disponible = $disponible;
		$this->idAula = $idAula;
	}

	#region CREATE
	/** 
	* Crea un nuevo Recursoexterno en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $tipo, int $idAula, string|int $idRecursoEx = self::SQL_DEFAULT, string|bool $disponible = self::SQL_DEFAULT) : Recursoexterno|RecursoexternoError|ErrorDB
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
		
		// Añade 'Id_recursoEx' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idRecursoEx !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_recursoEx";
		    $placeholders[] = "?";
		    $values[] = $idRecursoEx;
		    $types .= 'i';
		}
		
		// Añade 'Disponible' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($disponible !== self::SQL_DEFAULT)
		{
		    $columns[] = "Disponible";
		    $placeholders[] = "?";
		    $values[] = $disponible;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('RecursoExterno');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO RecursoExterno (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Id_recursoEx')
					{
					    $stmt->close();
					    return RecursoexternoError::duplicateIdRecursoEx(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return RecursoexternoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Recursoexterno se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Recursoexterno de la base de datos identificando Recursoexterno por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idRecursoEx) : Recursoexterno|RecursoexternoError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM RecursoExterno WHERE Id_recursoEx = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRecursoEx);
	
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
	        return RecursoexternoError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Tipo']),
			(int)($row['Id_aula']),
			(int)($row['Id_recursoEx']),
			(bool)($row['Disponible'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — Tipo
	/**
	* Obtiene el valor Tipo de Recursoexterno de la base de datos identificando Recursoexterno por su clave primaria única. 
	*/
	public static function getTipoById(mysqli $con, int $idRecursoEx) : string|RecursoexternoError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Tipo FROM RecursoExterno WHERE Id_recursoEx = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRecursoEx);
	
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
	        return RecursoexternoError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Tipo']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Tipo
	
	
	#region GET — Disponible
	/**
	* Obtiene el valor Disponible de Recursoexterno de la base de datos identificando Recursoexterno por su clave primaria única. 
	*/
	public static function getDisponibleById(mysqli $con, int $idRecursoEx) : bool|RecursoexternoError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Disponible FROM RecursoExterno WHERE Id_recursoEx = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRecursoEx);
	
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
	        return RecursoexternoError::notFound();
	    }
	
	    $attributeValue = ((bool)($result->fetch_assoc()['Disponible']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Disponible
	
	
	#region GET — IdAula
	/**
	* Obtiene el valor Id_aula de Recursoexterno de la base de datos identificando Recursoexterno por su clave primaria única. 
	*/
	public static function getIdAulaById(mysqli $con, int $idRecursoEx) : int|RecursoexternoError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_aula FROM RecursoExterno WHERE Id_recursoEx = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRecursoEx);
	
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
	        return RecursoexternoError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_aula']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdAula
	
	
	
	
	#region SET — Tipo
	/**
	* Establece el valor Tipo de Recursoexterno a partir de la base de datos que identifica Recursoexterno por su clave principal. 
	*/
	public static function setTipoById(mysqli $con, int $idRecursoEx, string $newTipo) : true|RecursoexternoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE RecursoExterno SET Tipo = ? WHERE Id_recursoEx = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newTipo, $idRecursoEx);
	
	
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
	                return RecursoexternoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Tipo
	
	
	#region SET — Disponible
	/**
	* Establece el valor Disponible de Recursoexterno a partir de la base de datos que identifica Recursoexterno por su clave principal. 
	*/
	public static function setDisponibleById(mysqli $con, int $idRecursoEx, bool $newDisponible) : true|RecursoexternoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE RecursoExterno SET Disponible = ? WHERE Id_recursoEx = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newDisponible, $idRecursoEx);
	
	
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
	                return RecursoexternoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Disponible
	
	
	#region SET — IdAula
	/**
	* Establece el valor Id_aula de Recursoexterno a partir de la base de datos que identifica Recursoexterno por su clave principal. 
	*/
	public static function setIdAulaById(mysqli $con, int $idRecursoEx, int $newIdAula) : true|RecursoexternoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE RecursoExterno SET Id_aula = ? WHERE Id_recursoEx = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdAula, $idRecursoEx);
	
	
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
	                return RecursoexternoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
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
	* Borra un Recursoexterno de la base de datos identificando Recursoexterno por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idRecursoEx) : true|RecursoexternoError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM RecursoExterno WHERE Id_recursoEx = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRecursoEx);
	
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
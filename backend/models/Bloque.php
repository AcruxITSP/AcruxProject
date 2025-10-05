<?php
require_once dirname(__FILE__).'/../error_base.php';
require_once dirname(__FILE__).'/../db/db_errors.php';
require_once dirname(__FILE__).'/base_model.php';

enum BloqueErrorType : string
{
    case NOT_FOUND = "BLOQUE_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "BLOQUE_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_BLOQUE = "BLOQUE_DUPLICATE_ID_BLOQUE";
}

class BloqueError extends ErrorBase
{
    private function __construct(mixed $type, mixed $data)
    {
        parent::__construct($type, $data);
    }

    public static function notFound() : BloqueError
    {
        return new self(BloqueErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : BloqueError
    {
        return new self(BloqueErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdBloque(mixed $pair) : BloqueError
    {
        return new self(BloqueErrorType::DUPLICATE_ID_BLOQUE, $pair);
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
class Bloque extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b279-8eb6-7b43-ad01-dadf66da6d94";

    protected mysqli $con;
	public int $idBloque;
	public int $idGrupo;
	public int $idClase;
	public int $idAula;
	public int $idHora;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, int $idGrupo, int $idClase, int $idAula, int $idHora, int $idBloque)
	{
	    $this->con = $con;
		$this->idBloque = $idBloque;
		$this->idGrupo = $idGrupo;
		$this->idClase = $idClase;
		$this->idAula = $idAula;
		$this->idHora = $idHora;
	}

	#region CREATE
	/** 
	* Crea un nuevo Bloque en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, int $idGrupo, int $idClase, int $idAula, int $idHora, string|int $idBloque = self::SQL_DEFAULT) : Bloque|BloqueError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Id_grupo' para la consulta de inserción SQL.
		$columns[] = "Id_grupo";
		$placeholders[] = "?";
		$values[] = $idGrupo;
		$types .= 'i';
		
		// Añade 'Id_clase' para la consulta de inserción SQL.
		$columns[] = "Id_clase";
		$placeholders[] = "?";
		$values[] = $idClase;
		$types .= 'i';
		
		// Añade 'Id_aula' para la consulta de inserción SQL.
		$columns[] = "Id_aula";
		$placeholders[] = "?";
		$values[] = $idAula;
		$types .= 'i';
		
		// Añade 'Id_hora' para la consulta de inserción SQL.
		$columns[] = "Id_hora";
		$placeholders[] = "?";
		$values[] = $idHora;
		$types .= 'i';
		
		// Añade 'Id_bloque' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idBloque !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_bloque";
		    $placeholders[] = "?";
		    $values[] = $idBloque;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Bloque');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Bloque (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Id_bloque')
					{
					    $stmt->close();
					    return BloqueError::duplicateIdBloque(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return BloqueError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Bloque se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Bloque de la base de datos identificando Bloque por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idBloque) : Bloque|BloqueError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Bloque WHERE Id_bloque = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idBloque);
	
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
	        return BloqueError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(int)($row['Id_grupo']),
			(int)($row['Id_clase']),
			(int)($row['Id_aula']),
			(int)($row['Id_hora']),
			(int)($row['Id_bloque'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — IdGrupo
	/**
	* Obtiene el valor Id_grupo de Bloque de la base de datos identificando Bloque por su clave primaria única. 
	*/
	public static function getIdGrupoById(mysqli $con, int $idBloque) : int|BloqueError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_grupo FROM Bloque WHERE Id_bloque = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idBloque);
	
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
	        return BloqueError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_grupo']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdGrupo
	
	
	#region GET — IdClase
	/**
	* Obtiene el valor Id_clase de Bloque de la base de datos identificando Bloque por su clave primaria única. 
	*/
	public static function getIdClaseById(mysqli $con, int $idBloque) : int|BloqueError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_clase FROM Bloque WHERE Id_bloque = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idBloque);
	
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
	        return BloqueError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_clase']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdClase
	
	
	#region GET — IdAula
	/**
	* Obtiene el valor Id_aula de Bloque de la base de datos identificando Bloque por su clave primaria única. 
	*/
	public static function getIdAulaById(mysqli $con, int $idBloque) : int|BloqueError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_aula FROM Bloque WHERE Id_bloque = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idBloque);
	
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
	        return BloqueError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_aula']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdAula
	
	
	#region GET — IdHora
	/**
	* Obtiene el valor Id_hora de Bloque de la base de datos identificando Bloque por su clave primaria única. 
	*/
	public static function getIdHoraById(mysqli $con, int $idBloque) : int|BloqueError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_hora FROM Bloque WHERE Id_bloque = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idBloque);
	
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
	        return BloqueError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_hora']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdHora
	
	
	
	
	#region SET — IdGrupo
	/**
	* Establece el valor Id_grupo de Bloque a partir de la base de datos que identifica Bloque por su clave principal. 
	*/
	public static function setIdGrupoById(mysqli $con, int $idBloque, int $newIdGrupo) : true|BloqueError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Bloque SET Id_grupo = ? WHERE Id_bloque = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdGrupo, $idBloque);
	
	
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
	                return BloqueError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdGrupo
	
	
	#region SET — IdClase
	/**
	* Establece el valor Id_clase de Bloque a partir de la base de datos que identifica Bloque por su clave principal. 
	*/
	public static function setIdClaseById(mysqli $con, int $idBloque, int $newIdClase) : true|BloqueError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Bloque SET Id_clase = ? WHERE Id_bloque = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdClase, $idBloque);
	
	
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
	                return BloqueError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdClase
	
	
	#region SET — IdAula
	/**
	* Establece el valor Id_aula de Bloque a partir de la base de datos que identifica Bloque por su clave principal. 
	*/
	public static function setIdAulaById(mysqli $con, int $idBloque, int $newIdAula) : true|BloqueError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Bloque SET Id_aula = ? WHERE Id_bloque = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdAula, $idBloque);
	
	
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
	                return BloqueError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdAula
	
	
	#region SET — IdHora
	/**
	* Establece el valor Id_hora de Bloque a partir de la base de datos que identifica Bloque por su clave principal. 
	*/
	public static function setIdHoraById(mysqli $con, int $idBloque, int $newIdHora) : true|BloqueError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Bloque SET Id_hora = ? WHERE Id_bloque = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdHora, $idBloque);
	
	
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
	                return BloqueError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdHora
	
	
	
	
	#region DELETE
	//  [OPTIONAL_BLOB_RETURN_COMMENT]
	
	/**
	* Borra un Bloque de la base de datos identificando Bloque por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idBloque) : true|BloqueError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Bloque WHERE Id_bloque = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idBloque);
	
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
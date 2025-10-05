<?php
require_once dirname(__FILE__).'/../error_base.php';
require_once dirname(__FILE__).'/../db/db_errors.php';
require_once dirname(__FILE__).'/base_model.php';

enum SoftwareErrorType : string
{
    case NOT_FOUND = "SOFTWARE_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "SOFTWARE_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_SOFTWARE = "SOFTWARE_DUPLICATE_ID_SOFTWARE";
	case DUPLICATE_NOMBRE = "SOFTWARE_DUPLICATE_NOMBRE";
}

class SoftwareError extends ErrorBase
{
    private function __construct(mixed $type, mixed $data)
    {
        parent::__construct($type, $data);
    }

    public static function notFound() : SoftwareError
    {
        return new self(SoftwareErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : SoftwareError
    {
        return new self(SoftwareErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdSoftware(mixed $pair) : SoftwareError
    {
        return new self(SoftwareErrorType::DUPLICATE_ID_SOFTWARE, $pair);
    }
    

    public static function duplicateNombre(mixed $pair) : SoftwareError
    {
        return new self(SoftwareErrorType::DUPLICATE_NOMBRE, $pair);
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
class Software extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b279-8ec3-7e4b-ba6e-501aad351bb0";

    protected mysqli $con;
	public int $idSoftware;
	public string $nombre;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $nombre, int $idSoftware)
	{
	    $this->con = $con;
		$this->idSoftware = $idSoftware;
		$this->nombre = $nombre;
	}

	#region CREATE
	/** 
	* Crea un nuevo Software en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $nombre, string|int $idSoftware = self::SQL_DEFAULT) : Software|SoftwareError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Nombre' para la consulta de inserción SQL.
		$columns[] = "Nombre";
		$placeholders[] = "?";
		$values[] = $nombre;
		$types .= 's';
		
		// Añade 'Id_software' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idSoftware !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_software";
		    $placeholders[] = "?";
		    $values[] = $idSoftware;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Software');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Software (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Nombre')
					{
					    $stmt->close();
					    return SoftwareError::duplicateNombre(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
					if($duplicateKey == 'Id_software')
					{
					    $stmt->close();
					    return SoftwareError::duplicateIdSoftware(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return SoftwareError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Software se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Software de la base de datos identificando Software por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idSoftware) : Software|SoftwareError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Software WHERE Id_software = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idSoftware);
	
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
	        return SoftwareError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Nombre']),
			(int)($row['Id_software'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	
	/**
	* Obtiene un Software de la base de datos identificando Software por su Nombre único. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getByNombre(mysqli $con, string $nombre) : Software|SoftwareError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT * FROM Software WHERE Nombre = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $nombre);
	
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
	        return SoftwareError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Nombre']),
			(int)($row['Id_software'])
	    );
	
	    $result->close();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — IdSoftware
	/**
	* Obtiene el valor Id_software de Software de la base de datos identificando Software por su Nombre único. 
	*/
	public static function getIdSoftwareByNombre(mysqli $con, string $nombre) : int|SoftwareError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Id_software FROM Software WHERE Nombre = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $nombre);
	
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
	        return SoftwareError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_software']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdSoftware
	
	
	#region GET — Nombre
	/**
	* Obtiene el valor Nombre de Software de la base de datos identificando Software por su clave primaria única. 
	*/
	public static function getNombreById(mysqli $con, int $idSoftware) : string|SoftwareError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Nombre FROM Software WHERE Id_software = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idSoftware);
	
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
	        return SoftwareError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Nombre']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Nombre de Software de la base de datos identificando Software por su Nombre único. 
	*/
	public static function getNombreByNombre(mysqli $con, string $nombre) : string|SoftwareError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Nombre FROM Software WHERE Nombre = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $nombre);
	
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
	        return SoftwareError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Nombre']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Nombre
	
	
	
	
	#region SET — Nombre
	/**
	* Establece el valor Nombre de Software a partir de la base de datos que identifica Software por su clave principal. 
	*/
	public static function setNombreById(mysqli $con, int $idSoftware, string $newNombre) : true|SoftwareError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Software SET Nombre = ? WHERE Id_software = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newNombre, $idSoftware);
	
	
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
					if($duplicateKey == 'Nombre')
					{
					    $stmt->close();
					    return SoftwareError::duplicateNombre(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return SoftwareError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Nombre de Software desde la base de datos identificando Software por su Nombre único. 
	*/
	public static function setNombreByNombre(mysqli $con, string $nombre, string $newNombre) : true|SoftwareError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Software SET Nombre = ? WHERE Nombre = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newNombre, $nombre);
	
	
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
					if($duplicateKey == 'Nombre')
					{
					    $stmt->close();
					    return SoftwareError::duplicateNombre(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return SoftwareError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Nombre
	
	
	
	
	#region DELETE
	//  [OPTIONAL_BLOB_RETURN_COMMENT]
	
	/**
	* Borra un Software de la base de datos identificando Software por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idSoftware) : true|SoftwareError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Software WHERE Id_software = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idSoftware);
	
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
	* Borra un Software de la base de datos identificando Software por su Nombre único.
	*/
	public static function deleteByNombre(mysqli $con, string $nombre) : true|SoftwareError|ErrorDB
	{
	    // Preparacion
	    $sql = "DELETE FROM Software WHERE Nombre = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $nombre);
	
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
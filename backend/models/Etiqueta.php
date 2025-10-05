<?php
require_once dirname(__FILE__).'/../error_base.php';
require_once dirname(__FILE__).'/../db/db_errors.php';
require_once dirname(__FILE__).'/base_model.php';

enum EtiquetaErrorType : string
{
    case NOT_FOUND = "ETIQUETA_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "ETIQUETA_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_ETIQUETA = "ETIQUETA_DUPLICATE_ID_ETIQUETA";
	case DUPLICATE_NOMBRE = "ETIQUETA_DUPLICATE_NOMBRE";
}

class EtiquetaError extends ErrorBase
{
    private function __construct(mixed $type, mixed $data)
    {
        parent::__construct($type, $data);
    }

    public static function notFound() : EtiquetaError
    {
        return new self(EtiquetaErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : EtiquetaError
    {
        return new self(EtiquetaErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdEtiqueta(mixed $pair) : EtiquetaError
    {
        return new self(EtiquetaErrorType::DUPLICATE_ID_ETIQUETA, $pair);
    }
    

    public static function duplicateNombre(mixed $pair) : EtiquetaError
    {
        return new self(EtiquetaErrorType::DUPLICATE_NOMBRE, $pair);
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
class Etiqueta extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b599-f649-7391-b91a-483f485edb8d";

    protected mysqli $con;
	public int $idEtiqueta;
	public string $nombre;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $nombre, int $idEtiqueta)
	{
	    $this->con = $con;
		$this->idEtiqueta = $idEtiqueta;
		$this->nombre = $nombre;
	}

	#region CREATE
	/** 
	* Crea un nuevo Etiqueta en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $nombre, string|int $idEtiqueta = self::SQL_DEFAULT) : Etiqueta|EtiquetaError|ErrorDB
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
		
		// Añade 'Id_etiqueta' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idEtiqueta !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_etiqueta";
		    $placeholders[] = "?";
		    $values[] = $idEtiqueta;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Etiqueta');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Etiqueta (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					    return EtiquetaError::duplicateNombre(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
					if($duplicateKey == 'Id_etiqueta')
					{
					    $stmt->close();
					    return EtiquetaError::duplicateIdEtiqueta(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return EtiquetaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Etiqueta se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Etiqueta de la base de datos identificando Etiqueta por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idEtiqueta) : Etiqueta|EtiquetaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Etiqueta WHERE Id_etiqueta = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idEtiqueta);
	
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
	        return EtiquetaError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Nombre']),
			(int)($row['Id_etiqueta'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	
	/**
	* Obtiene un Etiqueta de la base de datos identificando Etiqueta por su Nombre único. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getByNombre(mysqli $con, string $nombre) : Etiqueta|EtiquetaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT * FROM Etiqueta WHERE Nombre = ?";
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
	        return EtiquetaError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Nombre']),
			(int)($row['Id_etiqueta'])
	    );
	
	    $result->close();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — IdEtiqueta
	/**
	* Obtiene el valor Id_etiqueta de Etiqueta de la base de datos identificando Etiqueta por su Nombre único. 
	*/
	public static function getIdEtiquetaByNombre(mysqli $con, string $nombre) : int|EtiquetaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Id_etiqueta FROM Etiqueta WHERE Nombre = ?";
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
	        return EtiquetaError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_etiqueta']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdEtiqueta
	
	
	#region GET — Nombre
	/**
	* Obtiene el valor Nombre de Etiqueta de la base de datos identificando Etiqueta por su clave primaria única. 
	*/
	public static function getNombreById(mysqli $con, int $idEtiqueta) : string|EtiquetaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Nombre FROM Etiqueta WHERE Id_etiqueta = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idEtiqueta);
	
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
	        return EtiquetaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Nombre']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Nombre de Etiqueta de la base de datos identificando Etiqueta por su Nombre único. 
	*/
	public static function getNombreByNombre(mysqli $con, string $nombre) : string|EtiquetaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Nombre FROM Etiqueta WHERE Nombre = ?";
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
	        return EtiquetaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Nombre']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Nombre
	
	
	
	
	#region SET — Nombre
	/**
	* Establece el valor Nombre de Etiqueta a partir de la base de datos que identifica Etiqueta por su clave principal. 
	*/
	public static function setNombreById(mysqli $con, int $idEtiqueta, string $newNombre) : true|EtiquetaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Etiqueta SET Nombre = ? WHERE Id_etiqueta = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newNombre, $idEtiqueta);
	
	
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
					    return EtiquetaError::duplicateNombre(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return EtiquetaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Nombre de Etiqueta desde la base de datos identificando Etiqueta por su Nombre único. 
	*/
	public static function setNombreByNombre(mysqli $con, string $nombre, string $newNombre) : true|EtiquetaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Etiqueta SET Nombre = ? WHERE Nombre = ?";
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
					    return EtiquetaError::duplicateNombre(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return EtiquetaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
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
	* Borra un Etiqueta de la base de datos identificando Etiqueta por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idEtiqueta) : true|EtiquetaError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Etiqueta WHERE Id_etiqueta = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idEtiqueta);
	
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
	* Borra un Etiqueta de la base de datos identificando Etiqueta por su Nombre único.
	*/
	public static function deleteByNombre(mysqli $con, string $nombre) : true|EtiquetaError|ErrorDB
	{
	    // Preparacion
	    $sql = "DELETE FROM Etiqueta WHERE Nombre = ?";
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
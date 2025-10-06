<?php
require_once dirname(__FILE__).'/../error_base.php';
require_once dirname(__FILE__).'/../db/db_errors.php';
require_once dirname(__FILE__).'/base_model.php';

enum ComputadoraSoftwareErrorType : string
{
    case NOT_FOUND = "COMPUTADORASOFTWARE_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "COMPUTADORASOFTWARE_UNKNOWN_DUPLICATE";
	
}

class ComputadoraSoftwareError extends ErrorBase
{
    private function __construct(mixed $type, mixed $data)
    {
        parent::__construct($type, $data);
    }

    public static function notFound() : ComputadoraSoftwareError
    {
        return new self(ComputadoraSoftwareErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : ComputadoraSoftwareError
    {
        return new self(ComputadoraSoftwareErrorType::UNKNOWN_DUPLICATE, $pair);
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
class ComputadoraSoftware extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199bae7-2d50-718e-991f-f96827b56d13";

    protected mysqli $con;
	public int $idCompu;
	public int $idSoftware;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, int $idCompu, int $idSoftware)
	{
	    $this->con = $con;
		$this->idCompu = $idCompu;
		$this->idSoftware = $idSoftware;
	}

	#region CREATE
	/** 
	* Crea un nuevo ComputadoraSoftware en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, int $idCompu, int $idSoftware) : ComputadoraSoftware|ComputadoraSoftwareError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Id_compu' para la consulta de inserción SQL.
		$columns[] = "Id_compu";
		$placeholders[] = "?";
		$values[] = $idCompu;
		$types .= 'i';
		
		// Añade 'Id_software' para la consulta de inserción SQL.
		$columns[] = "Id_software";
		$placeholders[] = "?";
		$values[] = $idSoftware;
		$types .= 'i';
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Computadora_Software');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Computadora_Software (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
	                return ComputadoraSoftwareError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // ComputadoraSoftware se ha creado exitosamente.
	    $stmt->close();
	    return self::getByFKs($con, $idCompu, $idSoftware);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un 'ComputadoraSoftware' de la base de datos identificando 'ComputadoraSoftware' por sus claves externas como una clave compuesta. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getByFKs(mysqli $con, int $idCompu, int $idSoftware) : ComputadoraSoftware|ComputadoraSoftwareError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT * FROM Computadora_Software WHERE Id_compu = ? AND Id_software = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("ii", $idCompu, $idSoftware);
	
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
	        return ComputadoraSoftwareError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self (
			$con,
			(int)($row['Id_compu']),
			(int)($row['Id_software'])
	    );
	    
	    $stmt->close();
	    $result->free();
	    return $instance;
	}
	#endregion GET
	
	
	
	
	#region SET — IdCompu
	/**
	* Establece el valor Id_compu de ComputadoraSoftware a partir de la base de datos que identifica ComputadoraSoftware mediante sus claves externas como clave compuesta. 
	*/
	public static function setIdCompuByFKs(mysqli $con, int $idCompu, int $idSoftware, int $newIdCompu) : true|ComputadoraSoftwareError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Computadora_Software SET Id_compu = ? WHERE Id_compu = ? AND Id_software = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("iii", $newIdCompu, $idCompu, $idSoftware);
	
	
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
	                return ComputadoraSoftwareError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdCompu
	
	
	#region SET — IdSoftware
	/**
	* Establece el valor Id_software de ComputadoraSoftware a partir de la base de datos que identifica ComputadoraSoftware mediante sus claves externas como clave compuesta. 
	*/
	public static function setIdSoftwareByFKs(mysqli $con, int $idCompu, int $idSoftware, int $newIdSoftware) : true|ComputadoraSoftwareError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Computadora_Software SET Id_software = ? WHERE Id_compu = ? AND Id_software = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("iii", $newIdSoftware, $idCompu, $idSoftware);
	
	
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
	                return ComputadoraSoftwareError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdSoftware
	
	
	
	
	#region DELETE
	/**
	* Borra un 'ComputadoraSoftware' de la base de datos identificando 'ComputadoraSoftware' por sus claves externas como una clave compuesta. [OPTIONAL_BLOB_RETURN_COMMENT]
	*/
	public static function deleteByFKs(mysqli $con, int $idCompu, int $idSoftware) : true|ComputadoraSoftwareError|ErrorDB
	{
	    // Preparacion
	    $sql = "DELETE FROM Computadora_Software WHERE Id_compu = ? AND Id_software = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("ii", $idCompu, $idSoftware);
	
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
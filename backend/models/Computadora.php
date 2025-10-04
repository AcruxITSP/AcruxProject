<?php
include_once '../error_base.php';
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum ComputadoraErrorType : string
{
    case NOT_FOUND = "COMPUTADORA_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "COMPUTADORA_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_COMPU = "COMPUTADORA_DUPLICATE_ID_COMPU";
}

abstract class ComputadoraError extends ErrorBase
{
    public static function notFound() : ComputadoraError
    {
        return new self(ComputadoraErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : ComputadoraError
    {
        return new self(ComputadoraErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdCompu(mixed $pair) : ComputadoraError
    {
        return new self(ComputadoraErrorType::DUPLICATE_ID_COMPU, $pair);
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
class Computadora extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b13b-f142-7ab3-a057-850b263496de";

    protected mysqli $con;
	public int $idCompu;
	public string $so;
	public string $estado;
	public string $problema;
	public int $idAula;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $so, int $idAula, int $idCompu, string $estado, string $problema)
	{
	    $this->con = $con;
		$this->idCompu = $idCompu;
		$this->so = $so;
		$this->estado = $estado;
		$this->problema = $problema;
		$this->idAula = $idAula;
	}

	#region CREATE
	/** 
	* Crea un nuevo Computadora en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $so, int $idAula, string|int $idCompu = self::SQL_DEFAULT, string $estado = self::SQL_DEFAULT, string $problema = self::SQL_DEFAULT) : Computadora|ComputadoraError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'SO' para la consulta de inserción SQL.
		$columns[] = "SO";
		$placeholders[] = "?";
		$values[] = $so;
		$types .= 's';
		
		// Añade 'Id_aula' para la consulta de inserción SQL.
		$columns[] = "Id_aula";
		$placeholders[] = "?";
		$values[] = $idAula;
		$types .= 'i';
		
		// Añade 'Id_compu' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idCompu !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_compu";
		    $placeholders[] = "?";
		    $values[] = $idCompu;
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
	        return ErrorDB::noValues('Computadora');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Computadora (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Id_compu')
					{
					    $stmt->close();
					    return ComputadoraError::duplicateIdCompu(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return ComputadoraError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Computadora se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Computadora de la base de datos identificando Computadora por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idCompu) : Computadora|ComputadoraError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Computadora WHERE Id_compu = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idCompu);
	
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
	        return ComputadoraError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['SO']),
			(int)($row['Id_aula']),
			(int)($row['Id_compu']),
			(string)($row['Estado']),
			(string)($row['Problema'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — So
	/**
	* Obtiene el valor SO de Computadora de la base de datos identificando Computadora por su clave primaria única. 
	*/
	public static function getSoById(mysqli $con, int $idCompu) : string|ComputadoraError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT SO FROM Computadora WHERE Id_compu = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idCompu);
	
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
	        return ComputadoraError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['SO']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — So
	
	
	#region GET — Estado
	/**
	* Obtiene el valor Estado de Computadora de la base de datos identificando Computadora por su clave primaria única. 
	*/
	public static function getEstadoById(mysqli $con, int $idCompu) : string|ComputadoraError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Estado FROM Computadora WHERE Id_compu = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idCompu);
	
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
	        return ComputadoraError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Estado']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Estado
	
	
	#region GET — Problema
	/**
	* Obtiene el valor Problema de Computadora de la base de datos identificando Computadora por su clave primaria única. 
	*/
	public static function getProblemaById(mysqli $con, int $idCompu) : string|ComputadoraError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Problema FROM Computadora WHERE Id_compu = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idCompu);
	
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
	        return ComputadoraError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Problema']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Problema
	
	
	#region GET — IdAula
	/**
	* Obtiene el valor Id_aula de Computadora de la base de datos identificando Computadora por su clave primaria única. 
	*/
	public static function getIdAulaById(mysqli $con, int $idCompu) : int|ComputadoraError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_aula FROM Computadora WHERE Id_compu = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idCompu);
	
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
	        return ComputadoraError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_aula']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdAula
	
	
	
	
	#region SET — So
	/**
	* Establece el valor SO de Computadora a partir de la base de datos que identifica Computadora por su clave principal. 
	*/
	public static function setSoById(mysqli $con, int $idCompu, string $newSo) : true|ComputadoraError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Computadora SET SO = ? WHERE Id_compu = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newSo, $idCompu);
	
	
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
	                return ComputadoraError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — So
	
	
	#region SET — Estado
	/**
	* Establece el valor Estado de Computadora a partir de la base de datos que identifica Computadora por su clave principal. 
	*/
	public static function setEstadoById(mysqli $con, int $idCompu, string $newEstado) : true|ComputadoraError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Computadora SET Estado = ? WHERE Id_compu = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newEstado, $idCompu);
	
	
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
	                return ComputadoraError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
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
	* Establece el valor Problema de Computadora a partir de la base de datos que identifica Computadora por su clave principal. 
	*/
	public static function setProblemaById(mysqli $con, int $idCompu, string $newProblema) : true|ComputadoraError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Computadora SET Problema = ? WHERE Id_compu = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newProblema, $idCompu);
	
	
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
	                return ComputadoraError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
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
	* Establece el valor Id_aula de Computadora a partir de la base de datos que identifica Computadora por su clave principal. 
	*/
	public static function setIdAulaById(mysqli $con, int $idCompu, int $newIdAula) : true|ComputadoraError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Computadora SET Id_aula = ? WHERE Id_compu = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdAula, $idCompu);
	
	
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
	                return ComputadoraError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
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
	* Borra un Computadora de la base de datos identificando Computadora por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idCompu) : true|ComputadoraError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Computadora WHERE Id_compu = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idCompu);
	
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
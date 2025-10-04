<?php
include_once '../error_base.php';
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum PartediarioErrorType : string
{
    case NOT_FOUND = "PARTEDIARIO_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "PARTEDIARIO_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_ENTRADA = "PARTEDIARIO_DUPLICATE_ID_ENTRADA";
}

abstract class PartediarioError extends ErrorBase
{
    public static function notFound() : PartediarioError
    {
        return new self(PartediarioErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : PartediarioError
    {
        return new self(PartediarioErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdEntrada(mixed $pair) : PartediarioError
    {
        return new self(PartediarioErrorType::DUPLICATE_ID_ENTRADA, $pair);
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
class Partediario extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b13b-f114-7005-9649-3925d4243724";

    protected mysqli $con;
	public int $idEntrada;
	public string $accion;
	public string $fechaHora;
	public ?int $idAdscripta;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $accion, string $fechaHora, ?int $idAdscripta, int $idEntrada)
	{
	    $this->con = $con;
		$this->idEntrada = $idEntrada;
		$this->accion = $accion;
		$this->fechaHora = $fechaHora;
		$this->idAdscripta = $idAdscripta;
	}

	#region CREATE
	/** 
	* Crea un nuevo Partediario en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $accion, string $fechaHora, ?int $idAdscripta, string|int $idEntrada = self::SQL_DEFAULT) : Partediario|PartediarioError|ErrorDB
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
		
		// Añade 'Id_adscripta' para la consulta de inserción SQL.
		$columns[] = "Id_adscripta";
		$placeholders[] = "?";
		$values[] = $idAdscripta;
		$types .= 'i';
		
		// Añade 'Id_entrada' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idEntrada !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_entrada";
		    $placeholders[] = "?";
		    $values[] = $idEntrada;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('ParteDiario');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO ParteDiario (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Id_entrada')
					{
					    $stmt->close();
					    return PartediarioError::duplicateIdEntrada(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return PartediarioError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Partediario se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Partediario de la base de datos identificando Partediario por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idEntrada) : Partediario|PartediarioError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM ParteDiario WHERE Id_entrada = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idEntrada);
	
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
	        return PartediarioError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Accion']),
			(string)($row['Fecha_Hora']),
			(int)($row['Id_adscripta']),
			(int)($row['Id_entrada'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — Accion
	/**
	* Obtiene el valor Accion de Partediario de la base de datos identificando Partediario por su clave primaria única. 
	*/
	public static function getAccionById(mysqli $con, int $idEntrada) : string|PartediarioError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Accion FROM ParteDiario WHERE Id_entrada = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idEntrada);
	
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
	        return PartediarioError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Accion']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Accion
	
	
	#region GET — FechaHora
	/**
	* Obtiene el valor Fecha_Hora de Partediario de la base de datos identificando Partediario por su clave primaria única. 
	*/
	public static function getFechaHoraById(mysqli $con, int $idEntrada) : string|PartediarioError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Fecha_Hora FROM ParteDiario WHERE Id_entrada = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idEntrada);
	
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
	        return PartediarioError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Fecha_Hora']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — FechaHora
	
	
	#region GET — IdAdscripta
	/**
	* Obtiene el valor Id_adscripta de Partediario de la base de datos identificando Partediario por su clave primaria única. 
	*/
	public static function getIdAdscriptaById(mysqli $con, int $idEntrada) : int|PartediarioError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_adscripta FROM ParteDiario WHERE Id_entrada = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idEntrada);
	
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
	        return PartediarioError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_adscripta']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdAdscripta
	
	
	
	
	#region SET — Accion
	/**
	* Establece el valor Accion de Partediario a partir de la base de datos que identifica Partediario por su clave principal. 
	*/
	public static function setAccionById(mysqli $con, int $idEntrada, string $newAccion) : true|PartediarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE ParteDiario SET Accion = ? WHERE Id_entrada = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newAccion, $idEntrada);
	
	
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
	                return PartediarioError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
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
	* Establece el valor Fecha_Hora de Partediario a partir de la base de datos que identifica Partediario por su clave principal. 
	*/
	public static function setFechaHoraById(mysqli $con, int $idEntrada, string $newFechaHora) : true|PartediarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE ParteDiario SET Fecha_Hora = ? WHERE Id_entrada = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newFechaHora, $idEntrada);
	
	
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
	                return PartediarioError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — FechaHora
	
	
	#region SET — IdAdscripta
	/**
	* Establece el valor Id_adscripta de Partediario a partir de la base de datos que identifica Partediario por su clave principal. 
	*/
	public static function setIdAdscriptaById(mysqli $con, int $idEntrada, int $newIdAdscripta) : true|PartediarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE ParteDiario SET Id_adscripta = ? WHERE Id_entrada = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdAdscripta, $idEntrada);
	
	
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
	                return PartediarioError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdAdscripta
	
	
	
	
	#region DELETE
	//  [OPTIONAL_BLOB_RETURN_COMMENT]
	
	/**
	* Borra un Partediario de la base de datos identificando Partediario por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idEntrada) : true|PartediarioError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM ParteDiario WHERE Id_entrada = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idEntrada);
	
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
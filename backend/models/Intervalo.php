<?php
require_once dirname(__FILE__).'/../error_base.php';
require_once dirname(__FILE__).'/../db/db_errors.php';
require_once dirname(__FILE__).'/base_model.php';

enum IntervaloErrorType : string
{
    case NOT_FOUND = "INTERVALO_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "INTERVALO_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_INTERVALO = "INTERVALO_DUPLICATE_ID_INTERVALO";
	case DUPLICATE_NUMERO = "INTERVALO_DUPLICATE_NUMERO";
}

class IntervaloError extends ErrorBase
{
    private function __construct(mixed $type, mixed $data)
    {
        parent::__construct($type, $data);
    }

    public static function notFound() : IntervaloError
    {
        return new self(IntervaloErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : IntervaloError
    {
        return new self(IntervaloErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdIntervalo(mixed $pair) : IntervaloError
    {
        return new self(IntervaloErrorType::DUPLICATE_ID_INTERVALO, $pair);
    }
    

    public static function duplicateNumero(mixed $pair) : IntervaloError
    {
        return new self(IntervaloErrorType::DUPLICATE_NUMERO, $pair);
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
class Intervalo extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b59e-cd60-7e39-b8bd-94e3c78bb142";

    protected mysqli $con;
	public int $idIntervalo;
	public int $numero;
	public string $entrada;
	public string $salida;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, int $numero, string $entrada, string $salida, int $idIntervalo)
	{
	    $this->con = $con;
		$this->idIntervalo = $idIntervalo;
		$this->numero = $numero;
		$this->entrada = $entrada;
		$this->salida = $salida;
	}

	#region CREATE
	/** 
	* Crea un nuevo Intervalo en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, int $numero, string $entrada, string $salida, string|int $idIntervalo = self::SQL_DEFAULT) : Intervalo|IntervaloError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Numero' para la consulta de inserción SQL.
		$columns[] = "Numero";
		$placeholders[] = "?";
		$values[] = $numero;
		$types .= 'i';
		
		// Añade 'Entrada' para la consulta de inserción SQL.
		$columns[] = "Entrada";
		$placeholders[] = "?";
		$values[] = $entrada;
		$types .= 's';
		
		// Añade 'Salida' para la consulta de inserción SQL.
		$columns[] = "Salida";
		$placeholders[] = "?";
		$values[] = $salida;
		$types .= 's';
		
		// Añade 'Id_intervalo' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idIntervalo !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_intervalo";
		    $placeholders[] = "?";
		    $values[] = $idIntervalo;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Intervalo');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Intervalo (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Numero')
					{
					    $stmt->close();
					    return IntervaloError::duplicateNumero(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
					if($duplicateKey == 'Id_intervalo')
					{
					    $stmt->close();
					    return IntervaloError::duplicateIdIntervalo(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return IntervaloError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Intervalo se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Intervalo de la base de datos identificando Intervalo por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idIntervalo) : Intervalo|IntervaloError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Intervalo WHERE Id_intervalo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idIntervalo);
	
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
	        return IntervaloError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(int)($row['Numero']),
			(string)($row['Entrada']),
			(string)($row['Salida']),
			(int)($row['Id_intervalo'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	
	/**
	* Obtiene un Intervalo de la base de datos identificando Intervalo por su Numero único. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getByNumero(mysqli $con, int $numero) : Intervalo|IntervaloError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT * FROM Intervalo WHERE Numero = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("i", $numero);
	
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
	        return IntervaloError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(int)($row['Numero']),
			(string)($row['Entrada']),
			(string)($row['Salida']),
			(int)($row['Id_intervalo'])
	    );
	
	    $result->close();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — IdIntervalo
	/**
	* Obtiene el valor Id_intervalo de Intervalo de la base de datos identificando Intervalo por su Numero único. 
	*/
	public static function getIdIntervaloByNumero(mysqli $con, int $numero) : int|IntervaloError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Id_intervalo FROM Intervalo WHERE Numero = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("i", $numero);
	
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
	        return IntervaloError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_intervalo']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdIntervalo
	
	
	#region GET — Numero
	/**
	* Obtiene el valor Numero de Intervalo de la base de datos identificando Intervalo por su clave primaria única. 
	*/
	public static function getNumeroById(mysqli $con, int $idIntervalo) : int|IntervaloError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Numero FROM Intervalo WHERE Id_intervalo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idIntervalo);
	
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
	        return IntervaloError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Numero']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Numero de Intervalo de la base de datos identificando Intervalo por su Numero único. 
	*/
	public static function getNumeroByNumero(mysqli $con, int $numero) : int|IntervaloError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Numero FROM Intervalo WHERE Numero = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("i", $numero);
	
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
	        return IntervaloError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Numero']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Numero
	
	
	#region GET — Entrada
	/**
	* Obtiene el valor Entrada de Intervalo de la base de datos identificando Intervalo por su clave primaria única. 
	*/
	public static function getEntradaById(mysqli $con, int $idIntervalo) : string|IntervaloError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Entrada FROM Intervalo WHERE Id_intervalo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idIntervalo);
	
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
	        return IntervaloError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Entrada']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Entrada de Intervalo de la base de datos identificando Intervalo por su Numero único. 
	*/
	public static function getEntradaByNumero(mysqli $con, int $numero) : string|IntervaloError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Entrada FROM Intervalo WHERE Numero = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("i", $numero);
	
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
	        return IntervaloError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Entrada']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Entrada
	
	
	#region GET — Salida
	/**
	* Obtiene el valor Salida de Intervalo de la base de datos identificando Intervalo por su clave primaria única. 
	*/
	public static function getSalidaById(mysqli $con, int $idIntervalo) : string|IntervaloError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Salida FROM Intervalo WHERE Id_intervalo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idIntervalo);
	
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
	        return IntervaloError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Salida']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Salida de Intervalo de la base de datos identificando Intervalo por su Numero único. 
	*/
	public static function getSalidaByNumero(mysqli $con, int $numero) : string|IntervaloError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Salida FROM Intervalo WHERE Numero = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("i", $numero);
	
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
	        return IntervaloError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Salida']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Salida
	
	
	
	
	#region SET — Numero
	/**
	* Establece el valor Numero de Intervalo a partir de la base de datos que identifica Intervalo por su clave principal. 
	*/
	public static function setNumeroById(mysqli $con, int $idIntervalo, int $newNumero) : true|IntervaloError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Intervalo SET Numero = ? WHERE Id_intervalo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newNumero, $idIntervalo);
	
	
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
					if($duplicateKey == 'Numero')
					{
					    $stmt->close();
					    return IntervaloError::duplicateNumero(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return IntervaloError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Numero de Intervalo desde la base de datos identificando Intervalo por su Numero único. 
	*/
	public static function setNumeroByNumero(mysqli $con, int $numero, int $newNumero) : true|IntervaloError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Intervalo SET Numero = ? WHERE Numero = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newNumero, $numero);
	
	
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
					if($duplicateKey == 'Numero')
					{
					    $stmt->close();
					    return IntervaloError::duplicateNumero(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return IntervaloError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Numero
	
	
	#region SET — Entrada
	/**
	* Establece el valor Entrada de Intervalo a partir de la base de datos que identifica Intervalo por su clave principal. 
	*/
	public static function setEntradaById(mysqli $con, int $idIntervalo, string $newEntrada) : true|IntervaloError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Intervalo SET Entrada = ? WHERE Id_intervalo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newEntrada, $idIntervalo);
	
	
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
	                return IntervaloError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Entrada de Intervalo desde la base de datos identificando Intervalo por su Numero único. 
	*/
	public static function setEntradaByNumero(mysqli $con, int $numero, string $newEntrada) : true|IntervaloError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Intervalo SET Entrada = ? WHERE Numero = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newEntrada, $numero);
	
	
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
	                return IntervaloError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Entrada
	
	
	#region SET — Salida
	/**
	* Establece el valor Salida de Intervalo a partir de la base de datos que identifica Intervalo por su clave principal. 
	*/
	public static function setSalidaById(mysqli $con, int $idIntervalo, string $newSalida) : true|IntervaloError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Intervalo SET Salida = ? WHERE Id_intervalo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newSalida, $idIntervalo);
	
	
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
	                return IntervaloError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Salida de Intervalo desde la base de datos identificando Intervalo por su Numero único. 
	*/
	public static function setSalidaByNumero(mysqli $con, int $numero, string $newSalida) : true|IntervaloError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Intervalo SET Salida = ? WHERE Numero = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newSalida, $numero);
	
	
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
	                return IntervaloError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Salida
	
	
	
	
	#region DELETE
	//  [OPTIONAL_BLOB_RETURN_COMMENT]
	
	/**
	* Borra un Intervalo de la base de datos identificando Intervalo por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idIntervalo) : true|IntervaloError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Intervalo WHERE Id_intervalo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idIntervalo);
	
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
	* Borra un Intervalo de la base de datos identificando Intervalo por su Numero único.
	*/
	public static function deleteByNumero(mysqli $con, int $numero) : true|IntervaloError|ErrorDB
	{
	    // Preparacion
	    $sql = "DELETE FROM Intervalo WHERE Numero = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("i", $numero);
	
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
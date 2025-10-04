<?php
include_once '../error_base.php';
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum ReservaErrorType : string
{
    case NOT_FOUND = "RESERVA_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "RESERVA_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_RESERVA = "RESERVA_DUPLICATE_ID_RESERVA";
}

abstract class ReservaError extends ErrorBase
{
    public static function notFound() : ReservaError
    {
        return new self(ReservaErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : ReservaError
    {
        return new self(ReservaErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdReserva(mixed $pair) : ReservaError
    {
        return new self(ReservaErrorType::DUPLICATE_ID_RESERVA, $pair);
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
class Reserva extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b13b-f137-7d9e-95b4-16e0d5cf9fa2";

    protected mysqli $con;
	public int $idReserva;
	public int $idHora;
	public int $idAula;
	public int $idFuncionario;
	public string $fecha;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, int $idHora, int $idAula, int $idFuncionario, string $fecha, int $idReserva)
	{
	    $this->con = $con;
		$this->idReserva = $idReserva;
		$this->idHora = $idHora;
		$this->idAula = $idAula;
		$this->idFuncionario = $idFuncionario;
		$this->fecha = $fecha;
	}

	#region CREATE
	/** 
	* Crea un nuevo Reserva en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, int $idHora, int $idAula, int $idFuncionario, string $fecha, string|int $idReserva = self::SQL_DEFAULT) : Reserva|ReservaError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Id_hora' para la consulta de inserción SQL.
		$columns[] = "Id_hora";
		$placeholders[] = "?";
		$values[] = $idHora;
		$types .= 'i';
		
		// Añade 'Id_aula' para la consulta de inserción SQL.
		$columns[] = "Id_aula";
		$placeholders[] = "?";
		$values[] = $idAula;
		$types .= 'i';
		
		// Añade 'Id_funcionario' para la consulta de inserción SQL.
		$columns[] = "Id_funcionario";
		$placeholders[] = "?";
		$values[] = $idFuncionario;
		$types .= 'i';
		
		// Añade 'Fecha' para la consulta de inserción SQL.
		$columns[] = "Fecha";
		$placeholders[] = "?";
		$values[] = $fecha;
		$types .= 's';
		
		// Añade 'Id_reserva' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idReserva !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_reserva";
		    $placeholders[] = "?";
		    $values[] = $idReserva;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Reserva');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Reserva (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Id_reserva')
					{
					    $stmt->close();
					    return ReservaError::duplicateIdReserva(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return ReservaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Reserva se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Reserva de la base de datos identificando Reserva por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idReserva) : Reserva|ReservaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Reserva WHERE Id_reserva = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idReserva);
	
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
	        return ReservaError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(int)($row['Id_hora']),
			(int)($row['Id_aula']),
			(int)($row['Id_funcionario']),
			(string)($row['Fecha']),
			(int)($row['Id_reserva'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — IdHora
	/**
	* Obtiene el valor Id_hora de Reserva de la base de datos identificando Reserva por su clave primaria única. 
	*/
	public static function getIdHoraById(mysqli $con, int $idReserva) : int|ReservaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_hora FROM Reserva WHERE Id_reserva = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idReserva);
	
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
	        return ReservaError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_hora']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdHora
	
	
	#region GET — IdAula
	/**
	* Obtiene el valor Id_aula de Reserva de la base de datos identificando Reserva por su clave primaria única. 
	*/
	public static function getIdAulaById(mysqli $con, int $idReserva) : int|ReservaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_aula FROM Reserva WHERE Id_reserva = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idReserva);
	
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
	        return ReservaError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_aula']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdAula
	
	
	#region GET — IdFuncionario
	/**
	* Obtiene el valor Id_funcionario de Reserva de la base de datos identificando Reserva por su clave primaria única. 
	*/
	public static function getIdFuncionarioById(mysqli $con, int $idReserva) : int|ReservaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_funcionario FROM Reserva WHERE Id_reserva = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idReserva);
	
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
	        return ReservaError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_funcionario']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdFuncionario
	
	
	#region GET — Fecha
	/**
	* Obtiene el valor Fecha de Reserva de la base de datos identificando Reserva por su clave primaria única. 
	*/
	public static function getFechaById(mysqli $con, int $idReserva) : string|ReservaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Fecha FROM Reserva WHERE Id_reserva = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idReserva);
	
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
	        return ReservaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Fecha']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Fecha
	
	
	
	
	#region SET — IdHora
	/**
	* Establece el valor Id_hora de Reserva a partir de la base de datos que identifica Reserva por su clave principal. 
	*/
	public static function setIdHoraById(mysqli $con, int $idReserva, int $newIdHora) : true|ReservaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Reserva SET Id_hora = ? WHERE Id_reserva = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdHora, $idReserva);
	
	
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
	                return ReservaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdHora
	
	
	#region SET — IdAula
	/**
	* Establece el valor Id_aula de Reserva a partir de la base de datos que identifica Reserva por su clave principal. 
	*/
	public static function setIdAulaById(mysqli $con, int $idReserva, int $newIdAula) : true|ReservaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Reserva SET Id_aula = ? WHERE Id_reserva = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdAula, $idReserva);
	
	
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
	                return ReservaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdAula
	
	
	#region SET — IdFuncionario
	/**
	* Establece el valor Id_funcionario de Reserva a partir de la base de datos que identifica Reserva por su clave principal. 
	*/
	public static function setIdFuncionarioById(mysqli $con, int $idReserva, int $newIdFuncionario) : true|ReservaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Reserva SET Id_funcionario = ? WHERE Id_reserva = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdFuncionario, $idReserva);
	
	
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
	                return ReservaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdFuncionario
	
	
	#region SET — Fecha
	/**
	* Establece el valor Fecha de Reserva a partir de la base de datos que identifica Reserva por su clave principal. 
	*/
	public static function setFechaById(mysqli $con, int $idReserva, string $newFecha) : true|ReservaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Reserva SET Fecha = ? WHERE Id_reserva = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newFecha, $idReserva);
	
	
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
	                return ReservaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Fecha
	
	
	
	
	#region DELETE
	//  [OPTIONAL_BLOB_RETURN_COMMENT]
	
	/**
	* Borra un Reserva de la base de datos identificando Reserva por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idReserva) : true|ReservaError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Reserva WHERE Id_reserva = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idReserva);
	
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
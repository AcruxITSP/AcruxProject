<?php
include_once '../error_base.php';
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum RecextFuncionarioErrorType : string
{
    case NOT_FOUND = "RECEXTFUNCIONARIO_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "RECEXTFUNCIONARIO_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_REGISTRO = "RECEXTFUNCIONARIO_DUPLICATE_ID_REGISTRO";
}

abstract class RecextFuncionarioError extends ErrorBase
{
    public static function notFound() : RecextFuncionarioError
    {
        return new self(RecextFuncionarioErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : RecextFuncionarioError
    {
        return new self(RecextFuncionarioErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdRegistro(mixed $pair) : RecextFuncionarioError
    {
        return new self(RecextFuncionarioErrorType::DUPLICATE_ID_REGISTRO, $pair);
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
class RecextFuncionario extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b13b-f15f-774a-9280-b7e55ca12505";

    protected mysqli $con;
	public int $idRegistro;
	public string $accion;
	public string $fechaHora;
	public int $idRecursoEx;
	public ?int $idSecretario;
	public int $idFuncionario;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $accion, string $fechaHora, int $idRecursoEx, ?int $idSecretario, int $idFuncionario, int $idRegistro)
	{
	    $this->con = $con;
		$this->idRegistro = $idRegistro;
		$this->accion = $accion;
		$this->fechaHora = $fechaHora;
		$this->idRecursoEx = $idRecursoEx;
		$this->idSecretario = $idSecretario;
		$this->idFuncionario = $idFuncionario;
	}

	#region CREATE
	/** 
	* Crea un nuevo RecextFuncionario en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $accion, string $fechaHora, int $idRecursoEx, ?int $idSecretario, int $idFuncionario, string|int $idRegistro = self::SQL_DEFAULT) : RecextFuncionario|RecextFuncionarioError|ErrorDB
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
		
		// Añade 'Id_recursoEx' para la consulta de inserción SQL.
		$columns[] = "Id_recursoEx";
		$placeholders[] = "?";
		$values[] = $idRecursoEx;
		$types .= 'i';
		
		// Añade 'Id_secretario' para la consulta de inserción SQL.
		$columns[] = "Id_secretario";
		$placeholders[] = "?";
		$values[] = $idSecretario;
		$types .= 'i';
		
		// Añade 'Id_funcionario' para la consulta de inserción SQL.
		$columns[] = "Id_funcionario";
		$placeholders[] = "?";
		$values[] = $idFuncionario;
		$types .= 'i';
		
		// Añade 'Id_registro' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idRegistro !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_registro";
		    $placeholders[] = "?";
		    $values[] = $idRegistro;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('RecExt_Funcionario');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO RecExt_Funcionario (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Id_registro')
					{
					    $stmt->close();
					    return RecextFuncionarioError::duplicateIdRegistro(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return RecextFuncionarioError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // RecextFuncionario se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un RecextFuncionario de la base de datos identificando RecextFuncionario por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idRegistro) : RecextFuncionario|RecextFuncionarioError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM RecExt_Funcionario WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRegistro);
	
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
	        return RecextFuncionarioError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Accion']),
			(string)($row['Fecha_Hora']),
			(int)($row['Id_recursoEx']),
			(int)($row['Id_secretario']),
			(int)($row['Id_funcionario']),
			(int)($row['Id_registro'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — Accion
	/**
	* Obtiene el valor Accion de RecextFuncionario de la base de datos identificando RecextFuncionario por su clave primaria única. 
	*/
	public static function getAccionById(mysqli $con, int $idRegistro) : string|RecextFuncionarioError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Accion FROM RecExt_Funcionario WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRegistro);
	
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
	        return RecextFuncionarioError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Accion']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Accion
	
	
	#region GET — FechaHora
	/**
	* Obtiene el valor Fecha_Hora de RecextFuncionario de la base de datos identificando RecextFuncionario por su clave primaria única. 
	*/
	public static function getFechaHoraById(mysqli $con, int $idRegistro) : string|RecextFuncionarioError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Fecha_Hora FROM RecExt_Funcionario WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRegistro);
	
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
	        return RecextFuncionarioError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Fecha_Hora']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — FechaHora
	
	
	#region GET — IdRecursoEx
	/**
	* Obtiene el valor Id_recursoEx de RecextFuncionario de la base de datos identificando RecextFuncionario por su clave primaria única. 
	*/
	public static function getIdRecursoExById(mysqli $con, int $idRegistro) : int|RecextFuncionarioError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_recursoEx FROM RecExt_Funcionario WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRegistro);
	
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
	        return RecextFuncionarioError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_recursoEx']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdRecursoEx
	
	
	#region GET — IdSecretario
	/**
	* Obtiene el valor Id_secretario de RecextFuncionario de la base de datos identificando RecextFuncionario por su clave primaria única. 
	*/
	public static function getIdSecretarioById(mysqli $con, int $idRegistro) : int|RecextFuncionarioError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_secretario FROM RecExt_Funcionario WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRegistro);
	
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
	        return RecextFuncionarioError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_secretario']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdSecretario
	
	
	#region GET — IdFuncionario
	/**
	* Obtiene el valor Id_funcionario de RecextFuncionario de la base de datos identificando RecextFuncionario por su clave primaria única. 
	*/
	public static function getIdFuncionarioById(mysqli $con, int $idRegistro) : int|RecextFuncionarioError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_funcionario FROM RecExt_Funcionario WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRegistro);
	
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
	        return RecextFuncionarioError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_funcionario']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdFuncionario
	
	
	
	
	#region SET — Accion
	/**
	* Establece el valor Accion de RecextFuncionario a partir de la base de datos que identifica RecextFuncionario por su clave principal. 
	*/
	public static function setAccionById(mysqli $con, int $idRegistro, string $newAccion) : true|RecextFuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE RecExt_Funcionario SET Accion = ? WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newAccion, $idRegistro);
	
	
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
	                return RecextFuncionarioError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
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
	* Establece el valor Fecha_Hora de RecextFuncionario a partir de la base de datos que identifica RecextFuncionario por su clave principal. 
	*/
	public static function setFechaHoraById(mysqli $con, int $idRegistro, string $newFechaHora) : true|RecextFuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE RecExt_Funcionario SET Fecha_Hora = ? WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newFechaHora, $idRegistro);
	
	
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
	                return RecextFuncionarioError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — FechaHora
	
	
	#region SET — IdRecursoEx
	/**
	* Establece el valor Id_recursoEx de RecextFuncionario a partir de la base de datos que identifica RecextFuncionario por su clave principal. 
	*/
	public static function setIdRecursoExById(mysqli $con, int $idRegistro, int $newIdRecursoEx) : true|RecextFuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE RecExt_Funcionario SET Id_recursoEx = ? WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdRecursoEx, $idRegistro);
	
	
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
	                return RecextFuncionarioError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdRecursoEx
	
	
	#region SET — IdSecretario
	/**
	* Establece el valor Id_secretario de RecextFuncionario a partir de la base de datos que identifica RecextFuncionario por su clave principal. 
	*/
	public static function setIdSecretarioById(mysqli $con, int $idRegistro, int $newIdSecretario) : true|RecextFuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE RecExt_Funcionario SET Id_secretario = ? WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdSecretario, $idRegistro);
	
	
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
	                return RecextFuncionarioError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdSecretario
	
	
	#region SET — IdFuncionario
	/**
	* Establece el valor Id_funcionario de RecextFuncionario a partir de la base de datos que identifica RecextFuncionario por su clave principal. 
	*/
	public static function setIdFuncionarioById(mysqli $con, int $idRegistro, int $newIdFuncionario) : true|RecextFuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE RecExt_Funcionario SET Id_funcionario = ? WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdFuncionario, $idRegistro);
	
	
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
	                return RecextFuncionarioError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdFuncionario
	
	
	
	
	#region DELETE
	//  [OPTIONAL_BLOB_RETURN_COMMENT]
	
	/**
	* Borra un RecextFuncionario de la base de datos identificando RecextFuncionario por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idRegistro) : true|RecextFuncionarioError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM RecExt_Funcionario WHERE Id_registro = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idRegistro);
	
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
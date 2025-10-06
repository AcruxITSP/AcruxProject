<?php
require_once dirname(__FILE__).'/../error_base.php';
require_once dirname(__FILE__).'/../db/db_errors.php';
require_once dirname(__FILE__).'/base_model.php';

enum ProfesorErrorType : string
{
    case NOT_FOUND = "PROFESOR_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "PROFESOR_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_PROFESOR = "PROFESOR_DUPLICATE_ID_PROFESOR";
}

class ProfesorError extends ErrorBase
{
    private function __construct(mixed $type, mixed $data)
    {
        parent::__construct($type, $data);
    }

    public static function notFound() : ProfesorError
    {
        return new self(ProfesorErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : ProfesorError
    {
        return new self(ProfesorErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdProfesor(mixed $pair) : ProfesorError
    {
        return new self(ProfesorErrorType::DUPLICATE_ID_PROFESOR, $pair);
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
class Profesor extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199bae7-2d1a-716c-8f13-49153845d08b";

    protected mysqli $con;
	public int $idProfesor;
	public string $fechaingreso;
	public int $idFuncionario;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $fechaingreso, int $idFuncionario, int $idProfesor)
	{
	    $this->con = $con;
		$this->idProfesor = $idProfesor;
		$this->fechaingreso = $fechaingreso;
		$this->idFuncionario = $idFuncionario;
	}

	#region CREATE
	/** 
	* Crea un nuevo Profesor en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $fechaingreso, int $idFuncionario, string|int $idProfesor = self::SQL_DEFAULT) : Profesor|ProfesorError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'FechaIngreso' para la consulta de inserción SQL.
		$columns[] = "FechaIngreso";
		$placeholders[] = "?";
		$values[] = $fechaingreso;
		$types .= 's';
		
		// Añade 'Id_funcionario' para la consulta de inserción SQL.
		$columns[] = "Id_funcionario";
		$placeholders[] = "?";
		$values[] = $idFuncionario;
		$types .= 'i';
		
		// Añade 'Id_profesor' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idProfesor !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_profesor";
		    $placeholders[] = "?";
		    $values[] = $idProfesor;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Profesor');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Profesor (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Id_profesor')
					{
					    $stmt->close();
					    return ProfesorError::duplicateIdProfesor(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return ProfesorError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Profesor se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Profesor de la base de datos identificando Profesor por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idProfesor) : Profesor|ProfesorError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Profesor WHERE Id_profesor = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idProfesor);
	
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
	        return ProfesorError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['FechaIngreso']),
			(int)($row['Id_funcionario']),
			(int)($row['Id_profesor'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — Fechaingreso
	/**
	* Obtiene el valor FechaIngreso de Profesor de la base de datos identificando Profesor por su clave primaria única. 
	*/
	public static function getFechaingresoById(mysqli $con, int $idProfesor) : string|ProfesorError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT FechaIngreso FROM Profesor WHERE Id_profesor = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idProfesor);
	
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
	        return ProfesorError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['FechaIngreso']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Fechaingreso
	
	
	#region GET — IdFuncionario
	/**
	* Obtiene el valor Id_funcionario de Profesor de la base de datos identificando Profesor por su clave primaria única. 
	*/
	public static function getIdFuncionarioById(mysqli $con, int $idProfesor) : int|ProfesorError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_funcionario FROM Profesor WHERE Id_profesor = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idProfesor);
	
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
	        return ProfesorError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_funcionario']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdFuncionario
	
	
	
	
	#region SET — Fechaingreso
	/**
	* Establece el valor FechaIngreso de Profesor a partir de la base de datos que identifica Profesor por su clave principal. 
	*/
	public static function setFechaingresoById(mysqli $con, int $idProfesor, string $newFechaingreso) : true|ProfesorError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Profesor SET FechaIngreso = ? WHERE Id_profesor = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newFechaingreso, $idProfesor);
	
	
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
	                return ProfesorError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Fechaingreso
	
	
	#region SET — IdFuncionario
	/**
	* Establece el valor Id_funcionario de Profesor a partir de la base de datos que identifica Profesor por su clave principal. 
	*/
	public static function setIdFuncionarioById(mysqli $con, int $idProfesor, int $newIdFuncionario) : true|ProfesorError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Profesor SET Id_funcionario = ? WHERE Id_profesor = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdFuncionario, $idProfesor);
	
	
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
	                return ProfesorError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
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
	* Borra un Profesor de la base de datos identificando Profesor por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idProfesor) : true|ProfesorError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Profesor WHERE Id_profesor = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idProfesor);
	
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
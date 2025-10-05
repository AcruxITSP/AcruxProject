<?php
include_once '../error_base.php';
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum FuncionarioErrorType : string
{
    case NOT_FOUND = "FUNCIONARIO_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "FUNCIONARIO_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_FUNCIONARIO = "FUNCIONARIO_DUPLICATE_ID_FUNCIONARIO";
}

class FuncionarioError extends ErrorBase
{
    private function __construct(mixed $type, mixed $data)
    {
        parent::__construct($type, $data);
    }

    public static function notFound() : FuncionarioError
    {
        return new self(FuncionarioErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : FuncionarioError
    {
        return new self(FuncionarioErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdFuncionario(mixed $pair) : FuncionarioError
    {
        return new self(FuncionarioErrorType::DUPLICATE_ID_FUNCIONARIO, $pair);
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
class Funcionario extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b22e-e0ab-7567-9d3c-b66c91f03dfd";

    protected mysqli $con;
	public int $idFuncionario;
	public int $idPersona;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, int $idPersona, int $idFuncionario)
	{
	    $this->con = $con;
		$this->idFuncionario = $idFuncionario;
		$this->idPersona = $idPersona;
	}

	#region CREATE
	/** 
	* Crea un nuevo Funcionario en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, int $idPersona, string|int $idFuncionario = self::SQL_DEFAULT) : Funcionario|FuncionarioError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Id_persona' para la consulta de inserción SQL.
		$columns[] = "Id_persona";
		$placeholders[] = "?";
		$values[] = $idPersona;
		$types .= 'i';
		
		// Añade 'Id_funcionario' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idFuncionario !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_funcionario";
		    $placeholders[] = "?";
		    $values[] = $idFuncionario;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Funcionario');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Funcionario (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Id_funcionario')
					{
					    $stmt->close();
					    return FuncionarioError::duplicateIdFuncionario(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return FuncionarioError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Funcionario se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Funcionario de la base de datos identificando Funcionario por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idFuncionario) : Funcionario|FuncionarioError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Funcionario WHERE Id_funcionario = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idFuncionario);
	
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
	        return FuncionarioError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(int)($row['Id_persona']),
			(int)($row['Id_funcionario'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — IdPersona
	/**
	* Obtiene el valor Id_persona de Funcionario de la base de datos identificando Funcionario por su clave primaria única. 
	*/
	public static function getIdPersonaById(mysqli $con, int $idFuncionario) : int|FuncionarioError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_persona FROM Funcionario WHERE Id_funcionario = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idFuncionario);
	
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
	        return FuncionarioError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_persona']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdPersona
	
	
	
	
	#region SET — IdPersona
	/**
	* Establece el valor Id_persona de Funcionario a partir de la base de datos que identifica Funcionario por su clave principal. 
	*/
	public static function setIdPersonaById(mysqli $con, int $idFuncionario, int $newIdPersona) : true|FuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Funcionario SET Id_persona = ? WHERE Id_funcionario = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdPersona, $idFuncionario);
	
	
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
	                return FuncionarioError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdPersona
	
	
	
	
	#region DELETE
	//  [OPTIONAL_BLOB_RETURN_COMMENT]
	
	/**
	* Borra un Funcionario de la base de datos identificando Funcionario por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idFuncionario) : true|FuncionarioError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Funcionario WHERE Id_funcionario = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idFuncionario);
	
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
<?php
include_once '../error_base.php';
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum SecretarioErrorType : string
{
    case NOT_FOUND = "SECRETARIO_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "SECRETARIO_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_SECRETARIO = "SECRETARIO_DUPLICATE_ID_SECRETARIO";
}

abstract class SecretarioError extends ErrorBase
{
    public static function notFound() : SecretarioError
    {
        return new self(SecretarioErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : SecretarioError
    {
        return new self(SecretarioErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdSecretario(mixed $pair) : SecretarioError
    {
        return new self(SecretarioErrorType::DUPLICATE_ID_SECRETARIO, $pair);
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
class Secretario extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b13b-f14d-78d6-bb4c-08515ea2f088";

    protected mysqli $con;
	public int $idSecretario;
	public int $idFuncionario;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, int $idFuncionario, int $idSecretario)
	{
	    $this->con = $con;
		$this->idSecretario = $idSecretario;
		$this->idFuncionario = $idFuncionario;
	}

	#region CREATE
	/** 
	* Crea un nuevo Secretario en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, int $idFuncionario, string|int $idSecretario = self::SQL_DEFAULT) : Secretario|SecretarioError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Id_funcionario' para la consulta de inserción SQL.
		$columns[] = "Id_funcionario";
		$placeholders[] = "?";
		$values[] = $idFuncionario;
		$types .= 'i';
		
		// Añade 'Id_secretario' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idSecretario !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_secretario";
		    $placeholders[] = "?";
		    $values[] = $idSecretario;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Secretario');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Secretario (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Id_secretario')
					{
					    $stmt->close();
					    return SecretarioError::duplicateIdSecretario(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return SecretarioError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Secretario se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Secretario de la base de datos identificando Secretario por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idSecretario) : Secretario|SecretarioError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Secretario WHERE Id_secretario = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idSecretario);
	
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
	        return SecretarioError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(int)($row['Id_funcionario']),
			(int)($row['Id_secretario'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — IdFuncionario
	/**
	* Obtiene el valor Id_funcionario de Secretario de la base de datos identificando Secretario por su clave primaria única. 
	*/
	public static function getIdFuncionarioById(mysqli $con, int $idSecretario) : int|SecretarioError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_funcionario FROM Secretario WHERE Id_secretario = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idSecretario);
	
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
	        return SecretarioError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_funcionario']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdFuncionario
	
	
	
	
	#region SET — IdFuncionario
	/**
	* Establece el valor Id_funcionario de Secretario a partir de la base de datos que identifica Secretario por su clave principal. 
	*/
	public static function setIdFuncionarioById(mysqli $con, int $idSecretario, int $newIdFuncionario) : true|SecretarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Secretario SET Id_funcionario = ? WHERE Id_secretario = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdFuncionario, $idSecretario);
	
	
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
	                return SecretarioError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
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
	* Borra un Secretario de la base de datos identificando Secretario por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idSecretario) : true|SecretarioError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Secretario WHERE Id_secretario = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idSecretario);
	
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
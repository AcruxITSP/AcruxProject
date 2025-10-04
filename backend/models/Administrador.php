<?php
include_once '../error_base.php';
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum AdministradorErrorType : string
{
    case NOT_FOUND = "ADMINISTRADOR_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "ADMINISTRADOR_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_ADMINISTRADOR = "ADMINISTRADOR_DUPLICATE_ID_ADMINISTRADOR";
}

abstract class AdministradorError extends ErrorBase
{
    public static function notFound() : AdministradorError
    {
        return new self(AdministradorErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : AdministradorError
    {
        return new self(AdministradorErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdAdministrador(mixed $pair) : AdministradorError
    {
        return new self(AdministradorErrorType::DUPLICATE_ID_ADMINISTRADOR, $pair);
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
class Administrador extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b13b-f14f-7fb7-a547-7035509adea3";

    protected mysqli $con;
	public int $idAdministrador;
	public int $idFuncionario;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, int $idFuncionario, int $idAdministrador)
	{
	    $this->con = $con;
		$this->idAdministrador = $idAdministrador;
		$this->idFuncionario = $idFuncionario;
	}

	#region CREATE
	/** 
	* Crea un nuevo Administrador en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, int $idFuncionario, string|int $idAdministrador = self::SQL_DEFAULT) : Administrador|AdministradorError|ErrorDB
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
		
		// Añade 'Id_administrador' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idAdministrador !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_administrador";
		    $placeholders[] = "?";
		    $values[] = $idAdministrador;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Administrador');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Administrador (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Id_administrador')
					{
					    $stmt->close();
					    return AdministradorError::duplicateIdAdministrador(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return AdministradorError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Administrador se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Administrador de la base de datos identificando Administrador por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idAdministrador) : Administrador|AdministradorError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Administrador WHERE Id_administrador = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idAdministrador);
	
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
	        return AdministradorError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(int)($row['Id_funcionario']),
			(int)($row['Id_administrador'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — IdFuncionario
	/**
	* Obtiene el valor Id_funcionario de Administrador de la base de datos identificando Administrador por su clave primaria única. 
	*/
	public static function getIdFuncionarioById(mysqli $con, int $idAdministrador) : int|AdministradorError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_funcionario FROM Administrador WHERE Id_administrador = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idAdministrador);
	
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
	        return AdministradorError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_funcionario']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdFuncionario
	
	
	
	
	#region SET — IdFuncionario
	/**
	* Establece el valor Id_funcionario de Administrador a partir de la base de datos que identifica Administrador por su clave principal. 
	*/
	public static function setIdFuncionarioById(mysqli $con, int $idAdministrador, int $newIdFuncionario) : true|AdministradorError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Administrador SET Id_funcionario = ? WHERE Id_administrador = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdFuncionario, $idAdministrador);
	
	
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
	                return AdministradorError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
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
	* Borra un Administrador de la base de datos identificando Administrador por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idAdministrador) : true|AdministradorError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Administrador WHERE Id_administrador = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idAdministrador);
	
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
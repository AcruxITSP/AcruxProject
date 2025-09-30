<?php
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum AdministradorError : string
{
    case NOT_FOUND = "ADMINISTRADOR_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "ADMINISTRADOR_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_ADMINISTRADOR = "ADMINISTRADOR_DUPLICATE_ID_ADMINISTRADOR";
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
    const SQL_DEFAULT = "01999b0e-df50-705b-b8bb-1ed0c3fcf510";

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
	        return ErrorDB::NO_VALUES;
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Administrador (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
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
					    return AdministradorError::DUPLICATE_ID_ADMINISTRADOR;
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return AdministradorError::UNKNOWN_DUPLICATE;
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::EXECUTE;
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
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idAdministrador);
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    // Resultado
	    $result = $stmt->get_result();
	    if(!$result) return ErrorDB::RESULT;
	    if($result->num_rows === 0)
	    {
	        $result->free();
	        $stmt->close();
	        return AdministradorError::NOT_FOUND;
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
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idAdministrador);
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    // Resultado
	    $result = $stmt->get_result();
	    if(!$result) return ErrorDB::RESULT;
	    if($result->num_rows === 0)
	    {
	        $result->free();
	        $stmt->close();
	        return AdministradorError::NOT_FOUND;
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
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdFuncionario, $idAdministrador);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — IdFuncionario
}
?>
<?php
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum TelefonoTutorError : string
{
    case NOT_FOUND = "TELEFONOTUTOR_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "TELEFONOTUTOR_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_TEL = "TELEFONOTUTOR_DUPLICATE_ID_TEL";
}

/**
* Los atributos correspondientes a las columnas binarias de la tabla (como cualquier tipo de BLOB)
* almacenan los datos codificados en base64 en lugar del binario sin procesar.
*
* Cualquier operación que implique devolver un blob devolverá un binario codificado en base64.
*
* Cualquier operación que acepte un blob como entrada esperará un binario sin procesar.
*/
class TelefonoTutor extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "01999b0e-df37-75c4-aef2-c33eda35fe5c";

    protected mysqli $con;
	public int $idTel;
	public string $telefono;
	public string $nombretutor;
	public int $idEstudiante;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $telefono, string $nombretutor, int $idEstudiante, int $idTel)
	{
	    $this->con = $con;
		$this->idTel = $idTel;
		$this->telefono = $telefono;
		$this->nombretutor = $nombretutor;
		$this->idEstudiante = $idEstudiante;
	}

	#region CREATE
	/** 
	* Crea un nuevo TelefonoTutor en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $telefono, string $nombretutor, int $idEstudiante, string|int $idTel = self::SQL_DEFAULT) : TelefonoTutor|TelefonoTutorError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Telefono' para la consulta de inserción SQL.
		$columns[] = "Telefono";
		$placeholders[] = "?";
		$values[] = $telefono;
		$types .= 's';
		
		// Añade 'NombreTutor' para la consulta de inserción SQL.
		$columns[] = "NombreTutor";
		$placeholders[] = "?";
		$values[] = $nombretutor;
		$types .= 's';
		
		// Añade 'Id_estudiante' para la consulta de inserción SQL.
		$columns[] = "Id_estudiante";
		$placeholders[] = "?";
		$values[] = $idEstudiante;
		$types .= 'i';
		
		// Añade 'Id_tel' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idTel !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_tel";
		    $placeholders[] = "?";
		    $values[] = $idTel;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::NO_VALUES;
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Telefono_Tutor (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Id_tel')
					{
					    $stmt->close();
					    return TelefonoTutorError::DUPLICATE_ID_TEL;
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return TelefonoTutorError::UNKNOWN_DUPLICATE;
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    // TelefonoTutor se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un TelefonoTutor de la base de datos identificando TelefonoTutor por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idTel) : TelefonoTutor|TelefonoTutorError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Telefono_Tutor WHERE Id_tel = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idTel);
	
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
	        return TelefonoTutorError::NOT_FOUND;
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Telefono']),
			(string)($row['NombreTutor']),
			(int)($row['Id_estudiante']),
			(int)($row['Id_tel'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — Telefono
	/**
	* Obtiene el valor Telefono de TelefonoTutor de la base de datos identificando TelefonoTutor por su clave primaria única. 
	*/
	public static function getTelefonoById(mysqli $con, int $idTel) : string|TelefonoTutorError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Telefono FROM Telefono_Tutor WHERE Id_tel = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idTel);
	
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
	        return TelefonoTutorError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Telefono']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Telefono
	
	
	#region GET — Nombretutor
	/**
	* Obtiene el valor NombreTutor de TelefonoTutor de la base de datos identificando TelefonoTutor por su clave primaria única. 
	*/
	public static function getNombretutorById(mysqli $con, int $idTel) : string|TelefonoTutorError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT NombreTutor FROM Telefono_Tutor WHERE Id_tel = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idTel);
	
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
	        return TelefonoTutorError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['NombreTutor']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Nombretutor
	
	
	#region GET — IdEstudiante
	/**
	* Obtiene el valor Id_estudiante de TelefonoTutor de la base de datos identificando TelefonoTutor por su clave primaria única. 
	*/
	public static function getIdEstudianteById(mysqli $con, int $idTel) : int|TelefonoTutorError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_estudiante FROM Telefono_Tutor WHERE Id_tel = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idTel);
	
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
	        return TelefonoTutorError::NOT_FOUND;
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_estudiante']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdEstudiante
	
	
	
	
	#region SET — Telefono
	/**
	* Establece el valor Telefono de TelefonoTutor a partir de la base de datos que identifica TelefonoTutor por su clave principal. 
	*/
	public static function setTelefonoById(mysqli $con, int $idTel, string $newTelefono) : true|TelefonoTutorError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Telefono_Tutor SET Telefono = ? WHERE Id_tel = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newTelefono, $idTel);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — Telefono
	
	
	#region SET — Nombretutor
	/**
	* Establece el valor NombreTutor de TelefonoTutor a partir de la base de datos que identifica TelefonoTutor por su clave principal. 
	*/
	public static function setNombretutorById(mysqli $con, int $idTel, string $newNombretutor) : true|TelefonoTutorError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Telefono_Tutor SET NombreTutor = ? WHERE Id_tel = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newNombretutor, $idTel);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — Nombretutor
	
	
	#region SET — IdEstudiante
	/**
	* Establece el valor Id_estudiante de TelefonoTutor a partir de la base de datos que identifica TelefonoTutor por su clave principal. 
	*/
	public static function setIdEstudianteById(mysqli $con, int $idTel, int $newIdEstudiante) : true|TelefonoTutorError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Telefono_Tutor SET Id_estudiante = ? WHERE Id_tel = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdEstudiante, $idTel);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — IdEstudiante
}
?>
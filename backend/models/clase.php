<?php
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum ClaseError : string
{
    case NOT_FOUND = "CLASE_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "CLASE_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_CLASE = "CLASE_DUPLICATE_ID_CLASE";
}

/**
* Los atributos correspondientes a las columnas binarias de la tabla (como cualquier tipo de BLOB)
* almacenan los datos codificados en base64 en lugar del binario sin procesar.
*
* Cualquier operación que implique devolver un blob devolverá un binario codificado en base64.
*
* Cualquier operación que acepte un blob como entrada esperará un binario sin procesar.
*/
class Clase extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "01999b0e-df1a-71ab-aca7-19ca75569424";

    protected mysqli $con;
	public int $idClase;
	public int $idProfesor;
	public int $idMateria;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, int $idProfesor, int $idMateria, int $idClase)
	{
	    $this->con = $con;
		$this->idClase = $idClase;
		$this->idProfesor = $idProfesor;
		$this->idMateria = $idMateria;
	}

	#region CREATE
	/** 
	* Crea un nuevo Clase en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, int $idProfesor, int $idMateria, string|int $idClase = self::SQL_DEFAULT) : Clase|ClaseError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Id_profesor' para la consulta de inserción SQL.
		$columns[] = "Id_profesor";
		$placeholders[] = "?";
		$values[] = $idProfesor;
		$types .= 'i';
		
		// Añade 'Id_materia' para la consulta de inserción SQL.
		$columns[] = "Id_materia";
		$placeholders[] = "?";
		$values[] = $idMateria;
		$types .= 'i';
		
		// Añade 'Id_clase' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idClase !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_clase";
		    $placeholders[] = "?";
		    $values[] = $idClase;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::NO_VALUES;
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Clase (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Id_clase')
					{
					    $stmt->close();
					    return ClaseError::DUPLICATE_ID_CLASE;
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return ClaseError::UNKNOWN_DUPLICATE;
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    // Clase se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Clase de la base de datos identificando Clase por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idClase) : Clase|ClaseError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Clase WHERE Id_clase = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idClase);
	
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
	        return ClaseError::NOT_FOUND;
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(int)($row['Id_profesor']),
			(int)($row['Id_materia']),
			(int)($row['Id_clase'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — IdProfesor
	/**
	* Obtiene el valor Id_profesor de Clase de la base de datos identificando Clase por su clave primaria única. 
	*/
	public static function getIdProfesorById(mysqli $con, int $idClase) : int|ClaseError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_profesor FROM Clase WHERE Id_clase = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idClase);
	
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
	        return ClaseError::NOT_FOUND;
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_profesor']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdProfesor
	
	
	#region GET — IdMateria
	/**
	* Obtiene el valor Id_materia de Clase de la base de datos identificando Clase por su clave primaria única. 
	*/
	public static function getIdMateriaById(mysqli $con, int $idClase) : int|ClaseError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_materia FROM Clase WHERE Id_clase = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idClase);
	
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
	        return ClaseError::NOT_FOUND;
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_materia']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdMateria
	
	
	
	
	#region SET — IdProfesor
	/**
	* Establece el valor Id_profesor de Clase a partir de la base de datos que identifica Clase por su clave principal. 
	*/
	public static function setIdProfesorById(mysqli $con, int $idClase, int $newIdProfesor) : true|ClaseError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Clase SET Id_profesor = ? WHERE Id_clase = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdProfesor, $idClase);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — IdProfesor
	
	
	#region SET — IdMateria
	/**
	* Establece el valor Id_materia de Clase a partir de la base de datos que identifica Clase por su clave principal. 
	*/
	public static function setIdMateriaById(mysqli $con, int $idClase, int $newIdMateria) : true|ClaseError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Clase SET Id_materia = ? WHERE Id_clase = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdMateria, $idClase);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — IdMateria
}
?>
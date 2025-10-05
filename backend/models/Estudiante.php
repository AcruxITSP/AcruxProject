<?php
require_once dirname(__FILE__).'/../error_base.php';
require_once dirname(__FILE__).'/../db/db_errors.php';
require_once dirname(__FILE__).'/base_model.php';

enum EstudianteErrorType : string
{
    case NOT_FOUND = "ESTUDIANTE_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "ESTUDIANTE_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_ESTUDIANTE = "ESTUDIANTE_DUPLICATE_ID_ESTUDIANTE";
}

class EstudianteError extends ErrorBase
{
    private function __construct(mixed $type, mixed $data)
    {
        parent::__construct($type, $data);
    }

    public static function notFound() : EstudianteError
    {
        return new self(EstudianteErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : EstudianteError
    {
        return new self(EstudianteErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdEstudiante(mixed $pair) : EstudianteError
    {
        return new self(EstudianteErrorType::DUPLICATE_ID_ESTUDIANTE, $pair);
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
class Estudiante extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b599-f652-748a-aaea-2c54b9e8b523";

    protected mysqli $con;
	public int $idEstudiante;
	public string $reputacion;
	public int $idGrupo;
	public int $idPersona;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, int $idGrupo, int $idPersona, int $idEstudiante, string $reputacion)
	{
	    $this->con = $con;
		$this->idEstudiante = $idEstudiante;
		$this->reputacion = $reputacion;
		$this->idGrupo = $idGrupo;
		$this->idPersona = $idPersona;
	}

	#region CREATE
	/** 
	* Crea un nuevo Estudiante en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, int $idGrupo, int $idPersona, string|int $idEstudiante = self::SQL_DEFAULT, string $reputacion = self::SQL_DEFAULT) : Estudiante|EstudianteError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Id_grupo' para la consulta de inserción SQL.
		$columns[] = "Id_grupo";
		$placeholders[] = "?";
		$values[] = $idGrupo;
		$types .= 'i';
		
		// Añade 'Id_persona' para la consulta de inserción SQL.
		$columns[] = "Id_persona";
		$placeholders[] = "?";
		$values[] = $idPersona;
		$types .= 'i';
		
		// Añade 'Id_estudiante' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idEstudiante !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_estudiante";
		    $placeholders[] = "?";
		    $values[] = $idEstudiante;
		    $types .= 'i';
		}
		
		// Añade 'Reputacion' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($reputacion !== self::SQL_DEFAULT)
		{
		    $columns[] = "Reputacion";
		    $placeholders[] = "?";
		    $values[] = $reputacion;
		    $types .= 's';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Estudiante');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Estudiante (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Id_estudiante')
					{
					    $stmt->close();
					    return EstudianteError::duplicateIdEstudiante(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return EstudianteError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Estudiante se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Estudiante de la base de datos identificando Estudiante por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idEstudiante) : Estudiante|EstudianteError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Estudiante WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idEstudiante);
	
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
	        return EstudianteError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(int)($row['Id_grupo']),
			(int)($row['Id_persona']),
			(int)($row['Id_estudiante']),
			(string)($row['Reputacion'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — Reputacion
	/**
	* Obtiene el valor Reputacion de Estudiante de la base de datos identificando Estudiante por su clave primaria única. 
	*/
	public static function getReputacionById(mysqli $con, int $idEstudiante) : string|EstudianteError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Reputacion FROM Estudiante WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idEstudiante);
	
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
	        return EstudianteError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Reputacion']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Reputacion
	
	
	#region GET — IdGrupo
	/**
	* Obtiene el valor Id_grupo de Estudiante de la base de datos identificando Estudiante por su clave primaria única. 
	*/
	public static function getIdGrupoById(mysqli $con, int $idEstudiante) : int|EstudianteError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_grupo FROM Estudiante WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idEstudiante);
	
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
	        return EstudianteError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_grupo']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdGrupo
	
	
	#region GET — IdPersona
	/**
	* Obtiene el valor Id_persona de Estudiante de la base de datos identificando Estudiante por su clave primaria única. 
	*/
	public static function getIdPersonaById(mysqli $con, int $idEstudiante) : int|EstudianteError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_persona FROM Estudiante WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idEstudiante);
	
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
	        return EstudianteError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_persona']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdPersona
	
	
	
	
	#region SET — Reputacion
	/**
	* Establece el valor Reputacion de Estudiante a partir de la base de datos que identifica Estudiante por su clave principal. 
	*/
	public static function setReputacionById(mysqli $con, int $idEstudiante, string $newReputacion) : true|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Estudiante SET Reputacion = ? WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newReputacion, $idEstudiante);
	
	
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
	                return EstudianteError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Reputacion
	
	
	#region SET — IdGrupo
	/**
	* Establece el valor Id_grupo de Estudiante a partir de la base de datos que identifica Estudiante por su clave principal. 
	*/
	public static function setIdGrupoById(mysqli $con, int $idEstudiante, int $newIdGrupo) : true|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Estudiante SET Id_grupo = ? WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdGrupo, $idEstudiante);
	
	
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
	                return EstudianteError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdGrupo
	
	
	#region SET — IdPersona
	/**
	* Establece el valor Id_persona de Estudiante a partir de la base de datos que identifica Estudiante por su clave principal. 
	*/
	public static function setIdPersonaById(mysqli $con, int $idEstudiante, int $newIdPersona) : true|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Estudiante SET Id_persona = ? WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdPersona, $idEstudiante);
	
	
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
	                return EstudianteError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
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
	* Borra un Estudiante de la base de datos identificando Estudiante por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idEstudiante) : true|EstudianteError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Estudiante WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idEstudiante);
	
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
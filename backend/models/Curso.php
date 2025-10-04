<?php
include_once '../error_base.php';
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum CursoErrorType : string
{
    case NOT_FOUND = "CURSO_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "CURSO_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_CURSO = "CURSO_DUPLICATE_ID_CURSO";
	case DUPLICATE_NOMBRE = "CURSO_DUPLICATE_NOMBRE";
}

abstract class CursoError extends ErrorBase
{
    public static function notFound() : CursoError
    {
        return new self(CursoErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : CursoError
    {
        return new self(CursoErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdCurso(mixed $pair) : CursoError
    {
        return new self(CursoErrorType::DUPLICATE_ID_CURSO, $pair);
    }
    

    public static function duplicateNombre(mixed $pair) : CursoError
    {
        return new self(CursoErrorType::DUPLICATE_NOMBRE, $pair);
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
class Curso extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b13b-f123-7860-900d-48a138262465";

    protected mysqli $con;
	public int $idCurso;
	public string $nombre;
	public int $duracionanios;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $nombre, int $duracionanios, int $idCurso)
	{
	    $this->con = $con;
		$this->idCurso = $idCurso;
		$this->nombre = $nombre;
		$this->duracionanios = $duracionanios;
	}

	#region CREATE
	/** 
	* Crea un nuevo Curso en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $nombre, int $duracionanios, string|int $idCurso = self::SQL_DEFAULT) : Curso|CursoError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Nombre' para la consulta de inserción SQL.
		$columns[] = "Nombre";
		$placeholders[] = "?";
		$values[] = $nombre;
		$types .= 's';
		
		// Añade 'DuracionAnios' para la consulta de inserción SQL.
		$columns[] = "DuracionAnios";
		$placeholders[] = "?";
		$values[] = $duracionanios;
		$types .= 'i';
		
		// Añade 'Id_curso' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idCurso !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_curso";
		    $placeholders[] = "?";
		    $values[] = $idCurso;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Curso');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Curso (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Nombre')
					{
					    $stmt->close();
					    return CursoError::duplicateNombre(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
					if($duplicateKey == 'Id_curso')
					{
					    $stmt->close();
					    return CursoError::duplicateIdCurso(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return CursoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Curso se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Curso de la base de datos identificando Curso por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idCurso) : Curso|CursoError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Curso WHERE Id_curso = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idCurso);
	
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
	        return CursoError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Nombre']),
			(int)($row['DuracionAnios']),
			(int)($row['Id_curso'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	
	/**
	* Obtiene un Curso de la base de datos identificando Curso por su Nombre único. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getByNombre(mysqli $con, string $nombre) : Curso|CursoError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT * FROM Curso WHERE Nombre = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $nombre);
	
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
	        $result->close();
	        $stmt->close();
	        return CursoError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Nombre']),
			(int)($row['DuracionAnios']),
			(int)($row['Id_curso'])
	    );
	
	    $result->close();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — IdCurso
	/**
	* Obtiene el valor Id_curso de Curso de la base de datos identificando Curso por su Nombre único. 
	*/
	public static function getIdCursoByNombre(mysqli $con, string $nombre) : int|CursoError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Id_curso FROM Curso WHERE Nombre = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $nombre);
	
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
	        $result->close();
	        $stmt->close();
	        return CursoError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_curso']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdCurso
	
	
	#region GET — Nombre
	/**
	* Obtiene el valor Nombre de Curso de la base de datos identificando Curso por su clave primaria única. 
	*/
	public static function getNombreById(mysqli $con, int $idCurso) : string|CursoError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Nombre FROM Curso WHERE Id_curso = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idCurso);
	
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
	        return CursoError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Nombre']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Nombre de Curso de la base de datos identificando Curso por su Nombre único. 
	*/
	public static function getNombreByNombre(mysqli $con, string $nombre) : string|CursoError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Nombre FROM Curso WHERE Nombre = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $nombre);
	
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
	        $result->close();
	        $stmt->close();
	        return CursoError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Nombre']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Nombre
	
	
	#region GET — Duracionanios
	/**
	* Obtiene el valor DuracionAnios de Curso de la base de datos identificando Curso por su clave primaria única. 
	*/
	public static function getDuracionaniosById(mysqli $con, int $idCurso) : int|CursoError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT DuracionAnios FROM Curso WHERE Id_curso = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idCurso);
	
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
	        return CursoError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['DuracionAnios']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor DuracionAnios de Curso de la base de datos identificando Curso por su Nombre único. 
	*/
	public static function getDuracionaniosByNombre(mysqli $con, string $nombre) : int|CursoError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT DuracionAnios FROM Curso WHERE Nombre = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $nombre);
	
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
	        $result->close();
	        $stmt->close();
	        return CursoError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['DuracionAnios']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Duracionanios
	
	
	
	
	#region SET — Nombre
	/**
	* Establece el valor Nombre de Curso a partir de la base de datos que identifica Curso por su clave principal. 
	*/
	public static function setNombreById(mysqli $con, int $idCurso, string $newNombre) : true|CursoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Curso SET Nombre = ? WHERE Id_curso = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newNombre, $idCurso);
	
	
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
					if($duplicateKey == 'Nombre')
					{
					    $stmt->close();
					    return CursoError::duplicateNombre(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return CursoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Nombre de Curso desde la base de datos identificando Curso por su Nombre único. 
	*/
	public static function setNombreByNombre(mysqli $con, string $nombre, string $newNombre) : true|CursoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Curso SET Nombre = ? WHERE Nombre = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newNombre, $nombre);
	
	
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
					if($duplicateKey == 'Nombre')
					{
					    $stmt->close();
					    return CursoError::duplicateNombre(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return CursoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Nombre
	
	
	#region SET — Duracionanios
	/**
	* Establece el valor DuracionAnios de Curso a partir de la base de datos que identifica Curso por su clave principal. 
	*/
	public static function setDuracionaniosById(mysqli $con, int $idCurso, int $newDuracionanios) : true|CursoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Curso SET DuracionAnios = ? WHERE Id_curso = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newDuracionanios, $idCurso);
	
	
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
	                return CursoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor DuracionAnios de Curso desde la base de datos identificando Curso por su Nombre único. 
	*/
	public static function setDuracionaniosByNombre(mysqli $con, string $nombre, int $newDuracionanios) : true|CursoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Curso SET DuracionAnios = ? WHERE Nombre = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("is", $newDuracionanios, $nombre);
	
	
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
	                return CursoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Duracionanios
	
	
	
	
	#region DELETE
	//  [OPTIONAL_BLOB_RETURN_COMMENT]
	
	/**
	* Borra un Curso de la base de datos identificando Curso por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idCurso) : true|CursoError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Curso WHERE Id_curso = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idCurso);
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    $stmt->close();
	    return true;
	}
	
	/**
	* Borra un Curso de la base de datos identificando Curso por su Nombre único.
	*/
	public static function deleteByNombre(mysqli $con, string $nombre) : true|CursoError|ErrorDB
	{
	    // Preparacion
	    $sql = "DELETE FROM Curso WHERE Nombre = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $nombre);
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion DELETE
}
?>
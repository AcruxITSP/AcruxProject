<?php
include_once '../error_base.php';
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum MateriaErrorType : string
{
    case NOT_FOUND = "MATERIA_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "MATERIA_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_MATERIA = "MATERIA_DUPLICATE_ID_MATERIA";
	case DUPLICATE_NOMBRE = "MATERIA_DUPLICATE_NOMBRE";
}

abstract class MateriaError extends ErrorBase
{
    public static function notFound() : MateriaError
    {
        return new self(MateriaErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : MateriaError
    {
        return new self(MateriaErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdMateria(mixed $pair) : MateriaError
    {
        return new self(MateriaErrorType::DUPLICATE_ID_MATERIA, $pair);
    }
    

    public static function duplicateNombre(mixed $pair) : MateriaError
    {
        return new self(MateriaErrorType::DUPLICATE_NOMBRE, $pair);
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
class Materia extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b13b-f10c-71ca-bac9-e517ba9201da";

    protected mysqli $con;
	public int $idMateria;
	public string $nombre;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $nombre, int $idMateria)
	{
	    $this->con = $con;
		$this->idMateria = $idMateria;
		$this->nombre = $nombre;
	}

	#region CREATE
	/** 
	* Crea un nuevo Materia en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $nombre, string|int $idMateria = self::SQL_DEFAULT) : Materia|MateriaError|ErrorDB
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
		
		// Añade 'Id_materia' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idMateria !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_materia";
		    $placeholders[] = "?";
		    $values[] = $idMateria;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Materia');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Materia (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					    return MateriaError::duplicateNombre(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
					if($duplicateKey == 'Id_materia')
					{
					    $stmt->close();
					    return MateriaError::duplicateIdMateria(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return MateriaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Materia se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Materia de la base de datos identificando Materia por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idMateria) : Materia|MateriaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Materia WHERE Id_materia = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idMateria);
	
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
	        return MateriaError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Nombre']),
			(int)($row['Id_materia'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	
	/**
	* Obtiene un Materia de la base de datos identificando Materia por su Nombre único. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getByNombre(mysqli $con, string $nombre) : Materia|MateriaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT * FROM Materia WHERE Nombre = ?";
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
	        return MateriaError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Nombre']),
			(int)($row['Id_materia'])
	    );
	
	    $result->close();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — IdMateria
	/**
	* Obtiene el valor Id_materia de Materia de la base de datos identificando Materia por su Nombre único. 
	*/
	public static function getIdMateriaByNombre(mysqli $con, string $nombre) : int|MateriaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Id_materia FROM Materia WHERE Nombre = ?";
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
	        return MateriaError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_materia']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdMateria
	
	
	#region GET — Nombre
	/**
	* Obtiene el valor Nombre de Materia de la base de datos identificando Materia por su clave primaria única. 
	*/
	public static function getNombreById(mysqli $con, int $idMateria) : string|MateriaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Nombre FROM Materia WHERE Id_materia = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idMateria);
	
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
	        return MateriaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Nombre']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Nombre de Materia de la base de datos identificando Materia por su Nombre único. 
	*/
	public static function getNombreByNombre(mysqli $con, string $nombre) : string|MateriaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Nombre FROM Materia WHERE Nombre = ?";
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
	        return MateriaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Nombre']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Nombre
	
	
	
	
	#region SET — Nombre
	/**
	* Establece el valor Nombre de Materia a partir de la base de datos que identifica Materia por su clave principal. 
	*/
	public static function setNombreById(mysqli $con, int $idMateria, string $newNombre) : true|MateriaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Materia SET Nombre = ? WHERE Id_materia = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newNombre, $idMateria);
	
	
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
					    return MateriaError::duplicateNombre(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return MateriaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Nombre de Materia desde la base de datos identificando Materia por su Nombre único. 
	*/
	public static function setNombreByNombre(mysqli $con, string $nombre, string $newNombre) : true|MateriaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Materia SET Nombre = ? WHERE Nombre = ?";
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
					    return MateriaError::duplicateNombre(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return MateriaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Nombre
	
	
	
	
	#region DELETE
	//  [OPTIONAL_BLOB_RETURN_COMMENT]
	
	/**
	* Borra un Materia de la base de datos identificando Materia por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idMateria) : true|MateriaError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Materia WHERE Id_materia = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idMateria);
	
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
	* Borra un Materia de la base de datos identificando Materia por su Nombre único.
	*/
	public static function deleteByNombre(mysqli $con, string $nombre) : true|MateriaError|ErrorDB
	{
	    // Preparacion
	    $sql = "DELETE FROM Materia WHERE Nombre = ?";
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
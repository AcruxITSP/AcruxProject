<?php
require_once dirname(__FILE__).'/../error_base.php';
require_once dirname(__FILE__).'/../db/db_errors.php';
require_once dirname(__FILE__).'/base_model.php';

enum MateriaCursoErrorType : string
{
    case NOT_FOUND = "MATERIACURSO_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "MATERIACURSO_UNKNOWN_DUPLICATE";
	
}

class MateriaCursoError extends ErrorBase
{
    private function __construct(mixed $type, mixed $data)
    {
        parent::__construct($type, $data);
    }

    public static function notFound() : MateriaCursoError
    {
        return new self(MateriaCursoErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : MateriaCursoError
    {
        return new self(MateriaCursoErrorType::UNKNOWN_DUPLICATE, $pair);
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
class MateriaCurso extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b279-8ea6-727e-b328-401e4bc2643b";

    protected mysqli $con;
	public int $idMateria;
	public int $idCurso;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, int $idMateria, int $idCurso)
	{
	    $this->con = $con;
		$this->idMateria = $idMateria;
		$this->idCurso = $idCurso;
	}

	#region CREATE
	/** 
	* Crea un nuevo MateriaCurso en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, int $idMateria, int $idCurso) : MateriaCurso|MateriaCursoError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Id_materia' para la consulta de inserción SQL.
		$columns[] = "Id_materia";
		$placeholders[] = "?";
		$values[] = $idMateria;
		$types .= 'i';
		
		// Añade 'Id_curso' para la consulta de inserción SQL.
		$columns[] = "Id_curso";
		$placeholders[] = "?";
		$values[] = $idCurso;
		$types .= 'i';
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Materia_Curso');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Materia_Curso (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return MateriaCursoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // MateriaCurso se ha creado exitosamente.
	    $stmt->close();
	    return self::getByFKs($con, $idMateria, $idCurso);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un 'MateriaCurso' de la base de datos identificando 'MateriaCurso' por sus claves externas como una clave compuesta. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getByFKs(mysqli $con, int $idMateria, int $idCurso) : MateriaCurso|MateriaCursoError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT * FROM Materia_Curso WHERE Id_materia = ? AND Id_curso = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("ii", $idMateria, $idCurso);
	
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
	        return MateriaCursoError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self (
			$con,
			(int)($row['Id_materia']),
			(int)($row['Id_curso'])
	    );
	    
	    $stmt->close();
	    $result->free();
	    return $instance;
	}
	#endregion GET
	
	
	
	
	#region SET — IdMateria
	/**
	* Establece el valor Id_materia de MateriaCurso a partir de la base de datos que identifica MateriaCurso mediante sus claves externas como clave compuesta. 
	*/
	public static function setIdMateriaByFKs(mysqli $con, int $idMateria, int $idCurso, int $newIdMateria) : true|MateriaCursoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Materia_Curso SET Id_materia = ? WHERE Id_materia = ? AND Id_curso = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("iii", $newIdMateria, $idMateria, $idCurso);
	
	
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
	                return MateriaCursoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdMateria
	
	
	#region SET — IdCurso
	/**
	* Establece el valor Id_curso de MateriaCurso a partir de la base de datos que identifica MateriaCurso mediante sus claves externas como clave compuesta. 
	*/
	public static function setIdCursoByFKs(mysqli $con, int $idMateria, int $idCurso, int $newIdCurso) : true|MateriaCursoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Materia_Curso SET Id_curso = ? WHERE Id_materia = ? AND Id_curso = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("iii", $newIdCurso, $idMateria, $idCurso);
	
	
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
	                return MateriaCursoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdCurso
	
	
	
	
	#region DELETE
	/**
	* Borra un 'MateriaCurso' de la base de datos identificando 'MateriaCurso' por sus claves externas como una clave compuesta. [OPTIONAL_BLOB_RETURN_COMMENT]
	*/
	public static function deleteByFKs(mysqli $con, int $idMateria, int $idCurso) : true|MateriaCursoError|ErrorDB
	{
	    // Preparacion
	    $sql = "DELETE FROM Materia_Curso WHERE Id_materia = ? AND Id_curso = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("ii", $idMateria, $idCurso);
	
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
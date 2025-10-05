<?php
include_once '../error_base.php';
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum GrupoErrorType : string
{
    case NOT_FOUND = "GRUPO_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "GRUPO_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_GRUPO = "GRUPO_DUPLICATE_ID_GRUPO";
	case DUPLICATE_CODIGO = "GRUPO_DUPLICATE_CODIGO";
}

class GrupoError extends ErrorBase
{
    private function __construct(mixed $type, mixed $data)
    {
        parent::__construct($type, $data);
    }

    public static function notFound() : GrupoError
    {
        return new self(GrupoErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : GrupoError
    {
        return new self(GrupoErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdGrupo(mixed $pair) : GrupoError
    {
        return new self(GrupoErrorType::DUPLICATE_ID_GRUPO, $pair);
    }
    

    public static function duplicateCodigo(mixed $pair) : GrupoError
    {
        return new self(GrupoErrorType::DUPLICATE_CODIGO, $pair);
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
class Grupo extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b22e-e105-70d0-a8a9-0e277d34ef00";

    protected mysqli $con;
	public int $idGrupo;
	public string $codigo;
	public int $idAdscripta;
	public int $idCurso;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $codigo, int $idAdscripta, int $idCurso, int $idGrupo)
	{
	    $this->con = $con;
		$this->idGrupo = $idGrupo;
		$this->codigo = $codigo;
		$this->idAdscripta = $idAdscripta;
		$this->idCurso = $idCurso;
	}

	#region CREATE
	/** 
	* Crea un nuevo Grupo en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $codigo, int $idAdscripta, int $idCurso, string|int $idGrupo = self::SQL_DEFAULT) : Grupo|GrupoError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Codigo' para la consulta de inserción SQL.
		$columns[] = "Codigo";
		$placeholders[] = "?";
		$values[] = $codigo;
		$types .= 's';
		
		// Añade 'Id_adscripta' para la consulta de inserción SQL.
		$columns[] = "Id_adscripta";
		$placeholders[] = "?";
		$values[] = $idAdscripta;
		$types .= 'i';
		
		// Añade 'Id_curso' para la consulta de inserción SQL.
		$columns[] = "Id_curso";
		$placeholders[] = "?";
		$values[] = $idCurso;
		$types .= 'i';
		
		// Añade 'Id_grupo' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idGrupo !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_grupo";
		    $placeholders[] = "?";
		    $values[] = $idGrupo;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Grupo');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Grupo (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Codigo')
					{
					    $stmt->close();
					    return GrupoError::duplicateCodigo(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
					if($duplicateKey == 'Id_grupo')
					{
					    $stmt->close();
					    return GrupoError::duplicateIdGrupo(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return GrupoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Grupo se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Grupo de la base de datos identificando Grupo por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idGrupo) : Grupo|GrupoError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Grupo WHERE Id_grupo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idGrupo);
	
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
	        return GrupoError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Codigo']),
			(int)($row['Id_adscripta']),
			(int)($row['Id_curso']),
			(int)($row['Id_grupo'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	
	/**
	* Obtiene un Grupo de la base de datos identificando Grupo por su Codigo único. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getByCodigo(mysqli $con, string $codigo) : Grupo|GrupoError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT * FROM Grupo WHERE Codigo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $codigo);
	
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
	        return GrupoError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Codigo']),
			(int)($row['Id_adscripta']),
			(int)($row['Id_curso']),
			(int)($row['Id_grupo'])
	    );
	
	    $result->close();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — IdGrupo
	/**
	* Obtiene el valor Id_grupo de Grupo de la base de datos identificando Grupo por su Codigo único. 
	*/
	public static function getIdGrupoByCodigo(mysqli $con, string $codigo) : int|GrupoError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Id_grupo FROM Grupo WHERE Codigo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $codigo);
	
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
	        return GrupoError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_grupo']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdGrupo
	
	
	#region GET — Codigo
	/**
	* Obtiene el valor Codigo de Grupo de la base de datos identificando Grupo por su clave primaria única. 
	*/
	public static function getCodigoById(mysqli $con, int $idGrupo) : string|GrupoError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Codigo FROM Grupo WHERE Id_grupo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idGrupo);
	
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
	        return GrupoError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Codigo']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Codigo de Grupo de la base de datos identificando Grupo por su Codigo único. 
	*/
	public static function getCodigoByCodigo(mysqli $con, string $codigo) : string|GrupoError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Codigo FROM Grupo WHERE Codigo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $codigo);
	
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
	        return GrupoError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Codigo']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Codigo
	
	
	#region GET — IdAdscripta
	/**
	* Obtiene el valor Id_adscripta de Grupo de la base de datos identificando Grupo por su clave primaria única. 
	*/
	public static function getIdAdscriptaById(mysqli $con, int $idGrupo) : int|GrupoError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_adscripta FROM Grupo WHERE Id_grupo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idGrupo);
	
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
	        return GrupoError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_adscripta']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Id_adscripta de Grupo de la base de datos identificando Grupo por su Codigo único. 
	*/
	public static function getIdAdscriptaByCodigo(mysqli $con, string $codigo) : int|GrupoError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Id_adscripta FROM Grupo WHERE Codigo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $codigo);
	
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
	        return GrupoError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_adscripta']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdAdscripta
	
	
	#region GET — IdCurso
	/**
	* Obtiene el valor Id_curso de Grupo de la base de datos identificando Grupo por su clave primaria única. 
	*/
	public static function getIdCursoById(mysqli $con, int $idGrupo) : int|GrupoError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_curso FROM Grupo WHERE Id_grupo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idGrupo);
	
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
	        return GrupoError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_curso']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Id_curso de Grupo de la base de datos identificando Grupo por su Codigo único. 
	*/
	public static function getIdCursoByCodigo(mysqli $con, string $codigo) : int|GrupoError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Id_curso FROM Grupo WHERE Codigo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $codigo);
	
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
	        return GrupoError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_curso']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdCurso
	
	
	
	
	#region SET — Codigo
	/**
	* Establece el valor Codigo de Grupo a partir de la base de datos que identifica Grupo por su clave principal. 
	*/
	public static function setCodigoById(mysqli $con, int $idGrupo, string $newCodigo) : true|GrupoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Grupo SET Codigo = ? WHERE Id_grupo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newCodigo, $idGrupo);
	
	
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
					if($duplicateKey == 'Codigo')
					{
					    $stmt->close();
					    return GrupoError::duplicateCodigo(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return GrupoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Codigo de Grupo desde la base de datos identificando Grupo por su Codigo único. 
	*/
	public static function setCodigoByCodigo(mysqli $con, string $codigo, string $newCodigo) : true|GrupoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Grupo SET Codigo = ? WHERE Codigo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newCodigo, $codigo);
	
	
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
					if($duplicateKey == 'Codigo')
					{
					    $stmt->close();
					    return GrupoError::duplicateCodigo(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return GrupoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Codigo
	
	
	#region SET — IdAdscripta
	/**
	* Establece el valor Id_adscripta de Grupo a partir de la base de datos que identifica Grupo por su clave principal. 
	*/
	public static function setIdAdscriptaById(mysqli $con, int $idGrupo, int $newIdAdscripta) : true|GrupoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Grupo SET Id_adscripta = ? WHERE Id_grupo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdAdscripta, $idGrupo);
	
	
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
	                return GrupoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Id_adscripta de Grupo desde la base de datos identificando Grupo por su Codigo único. 
	*/
	public static function setIdAdscriptaByCodigo(mysqli $con, string $codigo, int $newIdAdscripta) : true|GrupoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Grupo SET Id_adscripta = ? WHERE Codigo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("is", $newIdAdscripta, $codigo);
	
	
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
	                return GrupoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdAdscripta
	
	
	#region SET — IdCurso
	/**
	* Establece el valor Id_curso de Grupo a partir de la base de datos que identifica Grupo por su clave principal. 
	*/
	public static function setIdCursoById(mysqli $con, int $idGrupo, int $newIdCurso) : true|GrupoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Grupo SET Id_curso = ? WHERE Id_grupo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdCurso, $idGrupo);
	
	
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
	                return GrupoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Id_curso de Grupo desde la base de datos identificando Grupo por su Codigo único. 
	*/
	public static function setIdCursoByCodigo(mysqli $con, string $codigo, int $newIdCurso) : true|GrupoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Grupo SET Id_curso = ? WHERE Codigo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("is", $newIdCurso, $codigo);
	
	
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
	                return GrupoError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdCurso
	
	
	
	
	#region DELETE
	//  [OPTIONAL_BLOB_RETURN_COMMENT]
	
	/**
	* Borra un Grupo de la base de datos identificando Grupo por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idGrupo) : true|GrupoError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Grupo WHERE Id_grupo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idGrupo);
	
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
	* Borra un Grupo de la base de datos identificando Grupo por su Codigo único.
	*/
	public static function deleteByCodigo(mysqli $con, string $codigo) : true|GrupoError|ErrorDB
	{
	    // Preparacion
	    $sql = "DELETE FROM Grupo WHERE Codigo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $codigo);
	
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
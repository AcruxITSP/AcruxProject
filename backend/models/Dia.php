<?php
include_once '../error_base.php';
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum DiaErrorType : string
{
    case NOT_FOUND = "DIA_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "DIA_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_DIA = "DIA_DUPLICATE_ID_DIA";
	case DUPLICATE_NOMBRE = "DIA_DUPLICATE_NOMBRE";
}

abstract class DiaError extends ErrorBase
{
    public static function notFound() : DiaError
    {
        return new self(DiaErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : DiaError
    {
        return new self(DiaErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdDia(mixed $pair) : DiaError
    {
        return new self(DiaErrorType::DUPLICATE_ID_DIA, $pair);
    }
    

    public static function duplicateNombre(mixed $pair) : DiaError
    {
        return new self(DiaErrorType::DUPLICATE_NOMBRE, $pair);
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
class Dia extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b13b-f132-7309-ba75-f48dfbaa1082";

    protected mysqli $con;
	public int $idDia;
	public string $nombre;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $nombre, int $idDia)
	{
	    $this->con = $con;
		$this->idDia = $idDia;
		$this->nombre = $nombre;
	}

	#region CREATE
	/** 
	* Crea un nuevo Dia en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $nombre, string|int $idDia = self::SQL_DEFAULT) : Dia|DiaError|ErrorDB
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
		
		// Añade 'Id_dia' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idDia !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_dia";
		    $placeholders[] = "?";
		    $values[] = $idDia;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Dia');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Dia (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					    return DiaError::duplicateNombre(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
					if($duplicateKey == 'Id_dia')
					{
					    $stmt->close();
					    return DiaError::duplicateIdDia(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return DiaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Dia se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Dia de la base de datos identificando Dia por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idDia) : Dia|DiaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Dia WHERE Id_dia = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idDia);
	
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
	        return DiaError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Nombre']),
			(int)($row['Id_dia'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	
	/**
	* Obtiene un Dia de la base de datos identificando Dia por su Nombre único. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getByNombre(mysqli $con, string $nombre) : Dia|DiaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT * FROM Dia WHERE Nombre = ?";
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
	        return DiaError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Nombre']),
			(int)($row['Id_dia'])
	    );
	
	    $result->close();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — IdDia
	/**
	* Obtiene el valor Id_dia de Dia de la base de datos identificando Dia por su Nombre único. 
	*/
	public static function getIdDiaByNombre(mysqli $con, string $nombre) : int|DiaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Id_dia FROM Dia WHERE Nombre = ?";
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
	        return DiaError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_dia']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdDia
	
	
	#region GET — Nombre
	/**
	* Obtiene el valor Nombre de Dia de la base de datos identificando Dia por su clave primaria única. 
	*/
	public static function getNombreById(mysqli $con, int $idDia) : string|DiaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Nombre FROM Dia WHERE Id_dia = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idDia);
	
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
	        return DiaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Nombre']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Nombre de Dia de la base de datos identificando Dia por su Nombre único. 
	*/
	public static function getNombreByNombre(mysqli $con, string $nombre) : string|DiaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Nombre FROM Dia WHERE Nombre = ?";
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
	        return DiaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Nombre']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Nombre
	
	
	
	
	#region SET — Nombre
	/**
	* Establece el valor Nombre de Dia a partir de la base de datos que identifica Dia por su clave principal. 
	*/
	public static function setNombreById(mysqli $con, int $idDia, string $newNombre) : true|DiaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Dia SET Nombre = ? WHERE Id_dia = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newNombre, $idDia);
	
	
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
					    return DiaError::duplicateNombre(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return DiaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Nombre de Dia desde la base de datos identificando Dia por su Nombre único. 
	*/
	public static function setNombreByNombre(mysqli $con, string $nombre, string $newNombre) : true|DiaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Dia SET Nombre = ? WHERE Nombre = ?";
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
					    return DiaError::duplicateNombre(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return DiaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
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
	* Borra un Dia de la base de datos identificando Dia por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idDia) : true|DiaError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Dia WHERE Id_dia = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idDia);
	
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
	* Borra un Dia de la base de datos identificando Dia por su Nombre único.
	*/
	public static function deleteByNombre(mysqli $con, string $nombre) : true|DiaError|ErrorDB
	{
	    // Preparacion
	    $sql = "DELETE FROM Dia WHERE Nombre = ?";
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
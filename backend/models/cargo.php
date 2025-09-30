<?php
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum CargoError : string
{
    case NOT_FOUND = "CARGO_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "CARGO_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_CARGO = "CARGO_DUPLICATE_ID_CARGO";
}

/**
* Los atributos correspondientes a las columnas binarias de la tabla (como cualquier tipo de BLOB)
* almacenan los datos codificados en base64 en lugar del binario sin procesar.
*
* Cualquier operación que implique devolver un blob devolverá un binario codificado en base64.
*
* Cualquier operación que acepte un blob como entrada esperará un binario sin procesar.
*/
class Cargo extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "01999b0e-df6f-7339-a7e4-60b87505b07a";

    protected mysqli $con;
	public int $idCargo;
	public string $nombre;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $nombre, int $idCargo)
	{
	    $this->con = $con;
		$this->idCargo = $idCargo;
		$this->nombre = $nombre;
	}

	#region CREATE
	/** 
	* Crea un nuevo Cargo en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $nombre, string|int $idCargo = self::SQL_DEFAULT) : Cargo|CargoError|ErrorDB
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
		
		// Añade 'Id_cargo' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idCargo !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_cargo";
		    $placeholders[] = "?";
		    $values[] = $idCargo;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::NO_VALUES;
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Cargo (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Id_cargo')
					{
					    $stmt->close();
					    return CargoError::DUPLICATE_ID_CARGO;
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return CargoError::UNKNOWN_DUPLICATE;
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    // Cargo se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Cargo de la base de datos identificando Cargo por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idCargo) : Cargo|CargoError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Cargo WHERE Id_cargo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idCargo);
	
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
	        return CargoError::NOT_FOUND;
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Nombre']),
			(int)($row['Id_cargo'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — Nombre
	/**
	* Obtiene el valor Nombre de Cargo de la base de datos identificando Cargo por su clave primaria única. 
	*/
	public static function getNombreById(mysqli $con, int $idCargo) : string|CargoError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Nombre FROM Cargo WHERE Id_cargo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idCargo);
	
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
	        return CargoError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Nombre']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Nombre
	
	
	
	
	#region SET — Nombre
	/**
	* Establece el valor Nombre de Cargo a partir de la base de datos que identifica Cargo por su clave principal. 
	*/
	public static function setNombreById(mysqli $con, int $idCargo, string $newNombre) : true|CargoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Cargo SET Nombre = ? WHERE Id_cargo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newNombre, $idCargo);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — Nombre
}
?>
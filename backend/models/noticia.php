<?php
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum NoticiaError : string
{
    case NOT_FOUND = "NOTICIA_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "NOTICIA_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_NOTICIA = "NOTICIA_DUPLICATE_ID_NOTICIA";
}

/**
* Los atributos correspondientes a las columnas binarias de la tabla (como cualquier tipo de BLOB)
* almacenan los datos codificados en base64 en lugar del binario sin procesar.
*
* Cualquier operación que implique devolver un blob devolverá un binario codificado en base64.
*
* Cualquier operación que acepte un blob como entrada esperará un binario sin procesar.
*/
class Noticia extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "01999b0e-df1f-7d76-a999-37e4814404ee";

    protected mysqli $con;
	public int $idNoticia;
	public string $fechaHora;
	public string $contenido;
	public ?int $idAdscripta;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $fechaHora, string $contenido, ?int $idAdscripta, int $idNoticia)
	{
	    $this->con = $con;
		$this->idNoticia = $idNoticia;
		$this->fechaHora = $fechaHora;
		$this->contenido = $contenido;
		$this->idAdscripta = $idAdscripta;
	}

	#region CREATE
	/** 
	* Crea un nuevo Noticia en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $fechaHora, string $contenido, ?int $idAdscripta, string|int $idNoticia = self::SQL_DEFAULT) : Noticia|NoticiaError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Fecha_Hora' para la consulta de inserción SQL.
		$columns[] = "Fecha_Hora";
		$placeholders[] = "?";
		$values[] = $fechaHora;
		$types .= 's';
		
		// Añade 'Contenido' para la consulta de inserción SQL.
		$columns[] = "Contenido";
		$placeholders[] = "?";
		$values[] = $contenido;
		$types .= 's';
		
		// Añade 'Id_adscripta' para la consulta de inserción SQL.
		$columns[] = "Id_adscripta";
		$placeholders[] = "?";
		$values[] = $idAdscripta;
		$types .= 'i';
		
		// Añade 'Id_noticia' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idNoticia !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_noticia";
		    $placeholders[] = "?";
		    $values[] = $idNoticia;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::NO_VALUES;
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Noticia (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Id_noticia')
					{
					    $stmt->close();
					    return NoticiaError::DUPLICATE_ID_NOTICIA;
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return NoticiaError::UNKNOWN_DUPLICATE;
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    // Noticia se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Noticia de la base de datos identificando Noticia por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idNoticia) : Noticia|NoticiaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Noticia WHERE Id_noticia = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idNoticia);
	
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
	        return NoticiaError::NOT_FOUND;
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Fecha_Hora']),
			(string)($row['Contenido']),
			(int)($row['Id_adscripta']),
			(int)($row['Id_noticia'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — FechaHora
	/**
	* Obtiene el valor Fecha_Hora de Noticia de la base de datos identificando Noticia por su clave primaria única. 
	*/
	public static function getFechaHoraById(mysqli $con, int $idNoticia) : string|NoticiaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Fecha_Hora FROM Noticia WHERE Id_noticia = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idNoticia);
	
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
	        return NoticiaError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Fecha_Hora']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — FechaHora
	
	
	#region GET — Contenido
	/**
	* Obtiene el valor Contenido de Noticia de la base de datos identificando Noticia por su clave primaria única. 
	*/
	public static function getContenidoById(mysqli $con, int $idNoticia) : string|NoticiaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Contenido FROM Noticia WHERE Id_noticia = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idNoticia);
	
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
	        return NoticiaError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Contenido']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Contenido
	
	
	#region GET — IdAdscripta
	/**
	* Obtiene el valor Id_adscripta de Noticia de la base de datos identificando Noticia por su clave primaria única. 
	*/
	public static function getIdAdscriptaById(mysqli $con, int $idNoticia) : int|NoticiaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_adscripta FROM Noticia WHERE Id_noticia = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idNoticia);
	
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
	        return NoticiaError::NOT_FOUND;
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_adscripta']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdAdscripta
	
	
	
	
	#region SET — FechaHora
	/**
	* Establece el valor Fecha_Hora de Noticia a partir de la base de datos que identifica Noticia por su clave principal. 
	*/
	public static function setFechaHoraById(mysqli $con, int $idNoticia, string $newFechaHora) : true|NoticiaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Noticia SET Fecha_Hora = ? WHERE Id_noticia = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newFechaHora, $idNoticia);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — FechaHora
	
	
	#region SET — Contenido
	/**
	* Establece el valor Contenido de Noticia a partir de la base de datos que identifica Noticia por su clave principal. 
	*/
	public static function setContenidoById(mysqli $con, int $idNoticia, string $newContenido) : true|NoticiaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Noticia SET Contenido = ? WHERE Id_noticia = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newContenido, $idNoticia);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — Contenido
	
	
	#region SET — IdAdscripta
	/**
	* Establece el valor Id_adscripta de Noticia a partir de la base de datos que identifica Noticia por su clave principal. 
	*/
	public static function setIdAdscriptaById(mysqli $con, int $idNoticia, int $newIdAdscripta) : true|NoticiaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Noticia SET Id_adscripta = ? WHERE Id_noticia = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdAdscripta, $idNoticia);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — IdAdscripta
}
?>
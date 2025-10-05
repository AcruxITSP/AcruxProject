<?php
require_once dirname(__FILE__).'/../error_base.php';
require_once dirname(__FILE__).'/../db/db_errors.php';
require_once dirname(__FILE__).'/base_model.php';

enum NoticiaEtiquetaErrorType : string
{
    case NOT_FOUND = "NOTICIAETIQUETA_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "NOTICIAETIQUETA_UNKNOWN_DUPLICATE";
	
}

class NoticiaEtiquetaError extends ErrorBase
{
    private function __construct(mixed $type, mixed $data)
    {
        parent::__construct($type, $data);
    }

    public static function notFound() : NoticiaEtiquetaError
    {
        return new self(NoticiaEtiquetaErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : NoticiaEtiquetaError
    {
        return new self(NoticiaEtiquetaErrorType::UNKNOWN_DUPLICATE, $pair);
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
class NoticiaEtiqueta extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b279-8ea0-7059-944e-579e5dcd612f";

    protected mysqli $con;
	public int $idNoticia;
	public int $idEtiqueta;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, int $idNoticia, int $idEtiqueta)
	{
	    $this->con = $con;
		$this->idNoticia = $idNoticia;
		$this->idEtiqueta = $idEtiqueta;
	}

	#region CREATE
	/** 
	* Crea un nuevo NoticiaEtiqueta en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, int $idNoticia, int $idEtiqueta) : NoticiaEtiqueta|NoticiaEtiquetaError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Id_noticia' para la consulta de inserción SQL.
		$columns[] = "Id_noticia";
		$placeholders[] = "?";
		$values[] = $idNoticia;
		$types .= 'i';
		
		// Añade 'Id_etiqueta' para la consulta de inserción SQL.
		$columns[] = "Id_etiqueta";
		$placeholders[] = "?";
		$values[] = $idEtiqueta;
		$types .= 'i';
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Noticia_Etiqueta');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Noticia_Etiqueta (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
	                return NoticiaEtiquetaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // NoticiaEtiqueta se ha creado exitosamente.
	    $stmt->close();
	    return self::getByFKs($con, $idNoticia, $idEtiqueta);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un 'NoticiaEtiqueta' de la base de datos identificando 'NoticiaEtiqueta' por sus claves externas como una clave compuesta. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getByFKs(mysqli $con, int $idNoticia, int $idEtiqueta) : NoticiaEtiqueta|NoticiaEtiquetaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT * FROM Noticia_Etiqueta WHERE Id_noticia = ? AND Id_etiqueta = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("ii", $idNoticia, $idEtiqueta);
	
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
	        return NoticiaEtiquetaError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self (
			$con,
			(int)($row['Id_noticia']),
			(int)($row['Id_etiqueta'])
	    );
	    
	    $stmt->close();
	    $result->free();
	    return $instance;
	}
	#endregion GET
	
	
	
	
	#region SET — IdNoticia
	/**
	* Establece el valor Id_noticia de NoticiaEtiqueta a partir de la base de datos que identifica NoticiaEtiqueta mediante sus claves externas como clave compuesta. 
	*/
	public static function setIdNoticiaByFKs(mysqli $con, int $idNoticia, int $idEtiqueta, int $newIdNoticia) : true|NoticiaEtiquetaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Noticia_Etiqueta SET Id_noticia = ? WHERE Id_noticia = ? AND Id_etiqueta = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("iii", $newIdNoticia, $idNoticia, $idEtiqueta);
	
	
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
	                return NoticiaEtiquetaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdNoticia
	
	
	#region SET — IdEtiqueta
	/**
	* Establece el valor Id_etiqueta de NoticiaEtiqueta a partir de la base de datos que identifica NoticiaEtiqueta mediante sus claves externas como clave compuesta. 
	*/
	public static function setIdEtiquetaByFKs(mysqli $con, int $idNoticia, int $idEtiqueta, int $newIdEtiqueta) : true|NoticiaEtiquetaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Noticia_Etiqueta SET Id_etiqueta = ? WHERE Id_noticia = ? AND Id_etiqueta = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("iii", $newIdEtiqueta, $idNoticia, $idEtiqueta);
	
	
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
	                return NoticiaEtiquetaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdEtiqueta
	
	
	
	
	#region DELETE
	/**
	* Borra un 'NoticiaEtiqueta' de la base de datos identificando 'NoticiaEtiqueta' por sus claves externas como una clave compuesta. [OPTIONAL_BLOB_RETURN_COMMENT]
	*/
	public static function deleteByFKs(mysqli $con, int $idNoticia, int $idEtiqueta) : true|NoticiaEtiquetaError|ErrorDB
	{
	    // Preparacion
	    $sql = "DELETE FROM Noticia_Etiqueta WHERE Id_noticia = ? AND Id_etiqueta = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("ii", $idNoticia, $idEtiqueta);
	
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
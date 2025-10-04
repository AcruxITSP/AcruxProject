<?php
include_once '../error_base.php';
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum HoraErrorType : string
{
    case NOT_FOUND = "HORA_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "HORA_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_HORA = "HORA_DUPLICATE_ID_HORA";
}

abstract class HoraError extends ErrorBase
{
    public static function notFound() : HoraError
    {
        return new self(HoraErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : HoraError
    {
        return new self(HoraErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdHora(mixed $pair) : HoraError
    {
        return new self(HoraErrorType::DUPLICATE_ID_HORA, $pair);
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
class Hora extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b13b-f133-79ac-927d-10f3cd22b65b";

    protected mysqli $con;
	public int $idHora;
	public int $idIntervalo;
	public int $idDia;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, int $idIntervalo, int $idDia, int $idHora)
	{
	    $this->con = $con;
		$this->idHora = $idHora;
		$this->idIntervalo = $idIntervalo;
		$this->idDia = $idDia;
	}

	#region CREATE
	/** 
	* Crea un nuevo Hora en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, int $idIntervalo, int $idDia, string|int $idHora = self::SQL_DEFAULT) : Hora|HoraError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Id_intervalo' para la consulta de inserción SQL.
		$columns[] = "Id_intervalo";
		$placeholders[] = "?";
		$values[] = $idIntervalo;
		$types .= 'i';
		
		// Añade 'Id_dia' para la consulta de inserción SQL.
		$columns[] = "Id_dia";
		$placeholders[] = "?";
		$values[] = $idDia;
		$types .= 'i';
		
		// Añade 'Id_hora' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idHora !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_hora";
		    $placeholders[] = "?";
		    $values[] = $idHora;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Hora');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Hora (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'Id_hora')
					{
					    $stmt->close();
					    return HoraError::duplicateIdHora(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return HoraError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Hora se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Hora de la base de datos identificando Hora por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idHora) : Hora|HoraError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Hora WHERE Id_hora = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idHora);
	
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
	        return HoraError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(int)($row['Id_intervalo']),
			(int)($row['Id_dia']),
			(int)($row['Id_hora'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — IdIntervalo
	/**
	* Obtiene el valor Id_intervalo de Hora de la base de datos identificando Hora por su clave primaria única. 
	*/
	public static function getIdIntervaloById(mysqli $con, int $idHora) : int|HoraError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_intervalo FROM Hora WHERE Id_hora = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idHora);
	
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
	        return HoraError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_intervalo']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdIntervalo
	
	
	#region GET — IdDia
	/**
	* Obtiene el valor Id_dia de Hora de la base de datos identificando Hora por su clave primaria única. 
	*/
	public static function getIdDiaById(mysqli $con, int $idHora) : int|HoraError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_dia FROM Hora WHERE Id_hora = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idHora);
	
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
	        return HoraError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_dia']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdDia
	
	
	
	
	#region SET — IdIntervalo
	/**
	* Establece el valor Id_intervalo de Hora a partir de la base de datos que identifica Hora por su clave principal. 
	*/
	public static function setIdIntervaloById(mysqli $con, int $idHora, int $newIdIntervalo) : true|HoraError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Hora SET Id_intervalo = ? WHERE Id_hora = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdIntervalo, $idHora);
	
	
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
	                return HoraError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdIntervalo
	
	
	#region SET — IdDia
	/**
	* Establece el valor Id_dia de Hora a partir de la base de datos que identifica Hora por su clave principal. 
	*/
	public static function setIdDiaById(mysqli $con, int $idHora, int $newIdDia) : true|HoraError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Hora SET Id_dia = ? WHERE Id_hora = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdDia, $idHora);
	
	
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
	                return HoraError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — IdDia
	
	
	
	
	#region DELETE
	//  [OPTIONAL_BLOB_RETURN_COMMENT]
	
	/**
	* Borra un Hora de la base de datos identificando Hora por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idHora) : true|HoraError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Hora WHERE Id_hora = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idHora);
	
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
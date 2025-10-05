<?php
require_once dirname(__FILE__).'/../error_base.php';
require_once dirname(__FILE__).'/../db/db_errors.php';
require_once dirname(__FILE__).'/base_model.php';

enum AulaErrorType : string
{
    case NOT_FOUND = "AULA_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "AULA_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_AULA = "AULA_DUPLICATE_ID_AULA";
	case DUPLICATE_CODIGO = "AULA_DUPLICATE_CODIGO";
}

class AulaError extends ErrorBase
{
    private function __construct(mixed $type, mixed $data)
    {
        parent::__construct($type, $data);
    }

    public static function notFound() : AulaError
    {
        return new self(AulaErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : AulaError
    {
        return new self(AulaErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdAula(mixed $pair) : AulaError
    {
        return new self(AulaErrorType::DUPLICATE_ID_AULA, $pair);
    }
    

    public static function duplicateCodigo(mixed $pair) : AulaError
    {
        return new self(AulaErrorType::DUPLICATE_CODIGO, $pair);
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
class Aula extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199b599-f665-7391-b591-2e8a2d2961b3";

    protected mysqli $con;
	public int $idAula;
	public string $codigo;
	public string $piso;
	public string $proposito;
	public int $cantidadsillas;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $codigo, string $piso, string $proposito, int $cantidadsillas, int $idAula)
	{
	    $this->con = $con;
		$this->idAula = $idAula;
		$this->codigo = $codigo;
		$this->piso = $piso;
		$this->proposito = $proposito;
		$this->cantidadsillas = $cantidadsillas;
	}

	#region CREATE
	/** 
	* Crea un nuevo Aula en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $codigo, string $piso, string $proposito, int $cantidadsillas, string|int $idAula = self::SQL_DEFAULT) : Aula|AulaError|ErrorDB
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
		
		// Añade 'Piso' para la consulta de inserción SQL.
		$columns[] = "Piso";
		$placeholders[] = "?";
		$values[] = $piso;
		$types .= 's';
		
		// Añade 'Proposito' para la consulta de inserción SQL.
		$columns[] = "Proposito";
		$placeholders[] = "?";
		$values[] = $proposito;
		$types .= 's';
		
		// Añade 'CantidadSillas' para la consulta de inserción SQL.
		$columns[] = "CantidadSillas";
		$placeholders[] = "?";
		$values[] = $cantidadsillas;
		$types .= 'i';
		
		// Añade 'Id_aula' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idAula !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_aula";
		    $placeholders[] = "?";
		    $values[] = $idAula;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Aula');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Aula (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					    return AulaError::duplicateCodigo(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
					if($duplicateKey == 'Id_aula')
					{
					    $stmt->close();
					    return AulaError::duplicateIdAula(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return AulaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Aula se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Aula de la base de datos identificando Aula por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idAula) : Aula|AulaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Aula WHERE Id_aula = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idAula);
	
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
	        return AulaError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Codigo']),
			(string)($row['Piso']),
			(string)($row['Proposito']),
			(int)($row['CantidadSillas']),
			(int)($row['Id_aula'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	
	/**
	* Obtiene un Aula de la base de datos identificando Aula por su Codigo único. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getByCodigo(mysqli $con, string $codigo) : Aula|AulaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT * FROM Aula WHERE Codigo = ?";
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
	        return AulaError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Codigo']),
			(string)($row['Piso']),
			(string)($row['Proposito']),
			(int)($row['CantidadSillas']),
			(int)($row['Id_aula'])
	    );
	
	    $result->close();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — IdAula
	/**
	* Obtiene el valor Id_aula de Aula de la base de datos identificando Aula por su Codigo único. 
	*/
	public static function getIdAulaByCodigo(mysqli $con, string $codigo) : int|AulaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Id_aula FROM Aula WHERE Codigo = ?";
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
	        return AulaError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_aula']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdAula
	
	
	#region GET — Codigo
	/**
	* Obtiene el valor Codigo de Aula de la base de datos identificando Aula por su clave primaria única. 
	*/
	public static function getCodigoById(mysqli $con, int $idAula) : string|AulaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Codigo FROM Aula WHERE Id_aula = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idAula);
	
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
	        return AulaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Codigo']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Codigo de Aula de la base de datos identificando Aula por su Codigo único. 
	*/
	public static function getCodigoByCodigo(mysqli $con, string $codigo) : string|AulaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Codigo FROM Aula WHERE Codigo = ?";
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
	        return AulaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Codigo']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Codigo
	
	
	#region GET — Piso
	/**
	* Obtiene el valor Piso de Aula de la base de datos identificando Aula por su clave primaria única. 
	*/
	public static function getPisoById(mysqli $con, int $idAula) : string|AulaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Piso FROM Aula WHERE Id_aula = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idAula);
	
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
	        return AulaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Piso']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Piso de Aula de la base de datos identificando Aula por su Codigo único. 
	*/
	public static function getPisoByCodigo(mysqli $con, string $codigo) : string|AulaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Piso FROM Aula WHERE Codigo = ?";
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
	        return AulaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Piso']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Piso
	
	
	#region GET — Proposito
	/**
	* Obtiene el valor Proposito de Aula de la base de datos identificando Aula por su clave primaria única. 
	*/
	public static function getPropositoById(mysqli $con, int $idAula) : string|AulaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Proposito FROM Aula WHERE Id_aula = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idAula);
	
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
	        return AulaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Proposito']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Proposito de Aula de la base de datos identificando Aula por su Codigo único. 
	*/
	public static function getPropositoByCodigo(mysqli $con, string $codigo) : string|AulaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Proposito FROM Aula WHERE Codigo = ?";
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
	        return AulaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Proposito']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Proposito
	
	
	#region GET — Cantidadsillas
	/**
	* Obtiene el valor CantidadSillas de Aula de la base de datos identificando Aula por su clave primaria única. 
	*/
	public static function getCantidadsillasById(mysqli $con, int $idAula) : int|AulaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT CantidadSillas FROM Aula WHERE Id_aula = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idAula);
	
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
	        return AulaError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['CantidadSillas']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor CantidadSillas de Aula de la base de datos identificando Aula por su Codigo único. 
	*/
	public static function getCantidadsillasByCodigo(mysqli $con, string $codigo) : int|AulaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT CantidadSillas FROM Aula WHERE Codigo = ?";
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
	        return AulaError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['CantidadSillas']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Cantidadsillas
	
	
	
	
	#region SET — Codigo
	/**
	* Establece el valor Codigo de Aula a partir de la base de datos que identifica Aula por su clave principal. 
	*/
	public static function setCodigoById(mysqli $con, int $idAula, string $newCodigo) : true|AulaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Aula SET Codigo = ? WHERE Id_aula = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newCodigo, $idAula);
	
	
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
					    return AulaError::duplicateCodigo(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return AulaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Codigo de Aula desde la base de datos identificando Aula por su Codigo único. 
	*/
	public static function setCodigoByCodigo(mysqli $con, string $codigo, string $newCodigo) : true|AulaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Aula SET Codigo = ? WHERE Codigo = ?";
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
					    return AulaError::duplicateCodigo(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return AulaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Codigo
	
	
	#region SET — Piso
	/**
	* Establece el valor Piso de Aula a partir de la base de datos que identifica Aula por su clave principal. 
	*/
	public static function setPisoById(mysqli $con, int $idAula, string $newPiso) : true|AulaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Aula SET Piso = ? WHERE Id_aula = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newPiso, $idAula);
	
	
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
	                return AulaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Piso de Aula desde la base de datos identificando Aula por su Codigo único. 
	*/
	public static function setPisoByCodigo(mysqli $con, string $codigo, string $newPiso) : true|AulaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Aula SET Piso = ? WHERE Codigo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newPiso, $codigo);
	
	
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
	                return AulaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Piso
	
	
	#region SET — Proposito
	/**
	* Establece el valor Proposito de Aula a partir de la base de datos que identifica Aula por su clave principal. 
	*/
	public static function setPropositoById(mysqli $con, int $idAula, string $newProposito) : true|AulaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Aula SET Proposito = ? WHERE Id_aula = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newProposito, $idAula);
	
	
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
	                return AulaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Proposito de Aula desde la base de datos identificando Aula por su Codigo único. 
	*/
	public static function setPropositoByCodigo(mysqli $con, string $codigo, string $newProposito) : true|AulaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Aula SET Proposito = ? WHERE Codigo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newProposito, $codigo);
	
	
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
	                return AulaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Proposito
	
	
	#region SET — Cantidadsillas
	/**
	* Establece el valor CantidadSillas de Aula a partir de la base de datos que identifica Aula por su clave principal. 
	*/
	public static function setCantidadsillasById(mysqli $con, int $idAula, int $newCantidadsillas) : true|AulaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Aula SET CantidadSillas = ? WHERE Id_aula = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newCantidadsillas, $idAula);
	
	
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
	                return AulaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor CantidadSillas de Aula desde la base de datos identificando Aula por su Codigo único. 
	*/
	public static function setCantidadsillasByCodigo(mysqli $con, string $codigo, int $newCantidadsillas) : true|AulaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Aula SET CantidadSillas = ? WHERE Codigo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("is", $newCantidadsillas, $codigo);
	
	
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
	                return AulaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Cantidadsillas
	
	
	
	
	#region DELETE
	//  [OPTIONAL_BLOB_RETURN_COMMENT]
	
	/**
	* Borra un Aula de la base de datos identificando Aula por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idAula) : true|AulaError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Aula WHERE Id_aula = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idAula);
	
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
	* Borra un Aula de la base de datos identificando Aula por su Codigo único.
	*/
	public static function deleteByCodigo(mysqli $con, string $codigo) : true|AulaError|ErrorDB
	{
	    // Preparacion
	    $sql = "DELETE FROM Aula WHERE Codigo = ?";
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
<?php
require_once dirname(__FILE__).'/../error_base.php';
require_once dirname(__FILE__).'/../db/db_errors.php';
require_once dirname(__FILE__).'/base_model.php';

enum PersonaErrorType : string
{
    case NOT_FOUND = "PERSONA_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "PERSONA_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_PERSONA = "PERSONA_DUPLICATE_ID_PERSONA";
	case DUPLICATE_DNI = "PERSONA_DUPLICATE_DNI";
	case DUPLICATE_EMAIL = "PERSONA_DUPLICATE_EMAIL";
}

class PersonaError extends ErrorBase
{
    private function __construct(mixed $type, mixed $data)
    {
        parent::__construct($type, $data);
    }

    public static function notFound() : PersonaError
    {
        return new self(PersonaErrorType::NOT_FOUND, null);
    }

    public static function unknownDuplicate(array $pair) : PersonaError
    {
        return new self(PersonaErrorType::UNKNOWN_DUPLICATE, $pair);
    }


    public static function duplicateIdPersona(mixed $pair) : PersonaError
    {
        return new self(PersonaErrorType::DUPLICATE_ID_PERSONA, $pair);
    }
    

    public static function duplicateDni(mixed $pair) : PersonaError
    {
        return new self(PersonaErrorType::DUPLICATE_DNI, $pair);
    }
    

    public static function duplicateEmail(mixed $pair) : PersonaError
    {
        return new self(PersonaErrorType::DUPLICATE_EMAIL, $pair);
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
class Persona extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "0199bae7-2ce9-7bb2-9669-16b93ba3ac0b";

    protected mysqli $con;
	public int $idPersona;
	public string $nombre;
	public string $apellido;
	public string $dni;
	public ?string $email;
	public string $contrasena;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $nombre, string $apellido, string $dni, ?string $email, string $contrasena, int $idPersona)
	{
	    $this->con = $con;
		$this->idPersona = $idPersona;
		$this->nombre = $nombre;
		$this->apellido = $apellido;
		$this->dni = $dni;
		$this->email = $email;
		$this->contrasena = $contrasena;
	}

	#region CREATE
	/** 
	* Crea un nuevo Persona en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $nombre, string $apellido, string $dni, ?string $email, string $contrasena, string|int $idPersona = self::SQL_DEFAULT) : Persona|PersonaError|ErrorDB
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
		
		// Añade 'Apellido' para la consulta de inserción SQL.
		$columns[] = "Apellido";
		$placeholders[] = "?";
		$values[] = $apellido;
		$types .= 's';
		
		// Añade 'DNI' para la consulta de inserción SQL.
		$columns[] = "DNI";
		$placeholders[] = "?";
		$values[] = $dni;
		$types .= 's';
		
		// Añade 'Email' para la consulta de inserción SQL.
		$columns[] = "Email";
		$placeholders[] = "?";
		$values[] = $email;
		$types .= 's';
		
		// Añade 'Contrasena' para la consulta de inserción SQL.
		$columns[] = "Contrasena";
		$placeholders[] = "?";
		$values[] = $contrasena;
		$types .= 's';
		
		// Añade 'Id_persona' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idPersona !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_persona";
		    $placeholders[] = "?";
		    $values[] = $idPersona;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::noValues('Persona');
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Persona (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'DNI')
					{
					    $stmt->close();
					    return PersonaError::duplicateDni(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
					if($duplicateKey == 'Email')
					{
					    $stmt->close();
					    return PersonaError::duplicateEmail(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
					if($duplicateKey == 'Id_persona')
					{
					    $stmt->close();
					    return PersonaError::duplicateIdPersona(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return PersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    // Persona se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Persona de la base de datos identificando Persona por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idPersona) : Persona|PersonaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Persona WHERE Id_persona = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idPersona);
	
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
	        return PersonaError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Nombre']),
			(string)($row['Apellido']),
			(string)($row['DNI']),
			(string)($row['Email']),
			(string)($row['Contrasena']),
			(int)($row['Id_persona'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	
	/**
	* Obtiene un Persona de la base de datos identificando Persona por su DNI único. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getByDni(mysqli $con, string $dni) : Persona|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT * FROM Persona WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $dni);
	
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
	        return PersonaError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Nombre']),
			(string)($row['Apellido']),
			(string)($row['DNI']),
			(string)($row['Email']),
			(string)($row['Contrasena']),
			(int)($row['Id_persona'])
	    );
	
	    $result->close();
	    $stmt->close();
	    return $instance;
	}
	
	/**
	* Obtiene un Persona de la base de datos identificando Persona por su Email único. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getByEmail(mysqli $con, string $email) : Persona|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT * FROM Persona WHERE Email = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $email);
	
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
	        return PersonaError::notFound();
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Nombre']),
			(string)($row['Apellido']),
			(string)($row['DNI']),
			(string)($row['Email']),
			(string)($row['Contrasena']),
			(int)($row['Id_persona'])
	    );
	
	    $result->close();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — IdPersona
	/**
	* Obtiene el valor Id_persona de Persona de la base de datos identificando Persona por su DNI único. 
	*/
	public static function getIdPersonaByDni(mysqli $con, string $dni) : int|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Id_persona FROM Persona WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $dni);
	
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
	        return PersonaError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_persona']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	/**
	* Obtiene el valor Id_persona de Persona de la base de datos identificando Persona por su Email único. 
	*/
	public static function getIdPersonaByEmail(mysqli $con, string $email) : int|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Id_persona FROM Persona WHERE Email = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $email);
	
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
	        return PersonaError::notFound();
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_persona']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdPersona
	
	
	#region GET — Nombre
	/**
	* Obtiene el valor Nombre de Persona de la base de datos identificando Persona por su clave primaria única. 
	*/
	public static function getNombreById(mysqli $con, int $idPersona) : string|PersonaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Nombre FROM Persona WHERE Id_persona = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idPersona);
	
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
	        return PersonaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Nombre']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Nombre de Persona de la base de datos identificando Persona por su DNI único. 
	*/
	public static function getNombreByDni(mysqli $con, string $dni) : string|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Nombre FROM Persona WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $dni);
	
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
	        return PersonaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Nombre']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	/**
	* Obtiene el valor Nombre de Persona de la base de datos identificando Persona por su Email único. 
	*/
	public static function getNombreByEmail(mysqli $con, string $email) : string|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Nombre FROM Persona WHERE Email = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $email);
	
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
	        return PersonaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Nombre']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Nombre
	
	
	#region GET — Apellido
	/**
	* Obtiene el valor Apellido de Persona de la base de datos identificando Persona por su clave primaria única. 
	*/
	public static function getApellidoById(mysqli $con, int $idPersona) : string|PersonaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Apellido FROM Persona WHERE Id_persona = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idPersona);
	
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
	        return PersonaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Apellido']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Apellido de Persona de la base de datos identificando Persona por su DNI único. 
	*/
	public static function getApellidoByDni(mysqli $con, string $dni) : string|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Apellido FROM Persona WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $dni);
	
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
	        return PersonaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Apellido']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	/**
	* Obtiene el valor Apellido de Persona de la base de datos identificando Persona por su Email único. 
	*/
	public static function getApellidoByEmail(mysqli $con, string $email) : string|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Apellido FROM Persona WHERE Email = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $email);
	
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
	        return PersonaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Apellido']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Apellido
	
	
	#region GET — Dni
	/**
	* Obtiene el valor DNI de Persona de la base de datos identificando Persona por su clave primaria única. 
	*/
	public static function getDniById(mysqli $con, int $idPersona) : string|PersonaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT DNI FROM Persona WHERE Id_persona = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idPersona);
	
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
	        return PersonaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['DNI']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor DNI de Persona de la base de datos identificando Persona por su DNI único. 
	*/
	public static function getDniByDni(mysqli $con, string $dni) : string|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT DNI FROM Persona WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $dni);
	
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
	        return PersonaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['DNI']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	/**
	* Obtiene el valor DNI de Persona de la base de datos identificando Persona por su Email único. 
	*/
	public static function getDniByEmail(mysqli $con, string $email) : string|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT DNI FROM Persona WHERE Email = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $email);
	
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
	        return PersonaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['DNI']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Dni
	
	
	#region GET — Email
	/**
	* Obtiene el valor Email de Persona de la base de datos identificando Persona por su clave primaria única. 
	*/
	public static function getEmailById(mysqli $con, int $idPersona) : string|PersonaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Email FROM Persona WHERE Id_persona = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idPersona);
	
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
	        return PersonaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Email']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Email de Persona de la base de datos identificando Persona por su DNI único. 
	*/
	public static function getEmailByDni(mysqli $con, string $dni) : string|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Email FROM Persona WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $dni);
	
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
	        return PersonaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Email']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	/**
	* Obtiene el valor Email de Persona de la base de datos identificando Persona por su Email único. 
	*/
	public static function getEmailByEmail(mysqli $con, string $email) : string|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Email FROM Persona WHERE Email = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $email);
	
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
	        return PersonaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Email']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Email
	
	
	#region GET — Contrasena
	/**
	* Obtiene el valor Contrasena de Persona de la base de datos identificando Persona por su clave primaria única. 
	*/
	public static function getContrasenaById(mysqli $con, int $idPersona) : string|PersonaError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Contrasena FROM Persona WHERE Id_persona = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idPersona);
	
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
	        return PersonaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Contrasena']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Contrasena de Persona de la base de datos identificando Persona por su DNI único. 
	*/
	public static function getContrasenaByDni(mysqli $con, string $dni) : string|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Contrasena FROM Persona WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $dni);
	
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
	        return PersonaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Contrasena']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	/**
	* Obtiene el valor Contrasena de Persona de la base de datos identificando Persona por su Email único. 
	*/
	public static function getContrasenaByEmail(mysqli $con, string $email) : string|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Contrasena FROM Persona WHERE Email = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $email);
	
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
	        return PersonaError::notFound();
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Contrasena']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Contrasena
	
	
	
	
	#region SET — Nombre
	/**
	* Establece el valor Nombre de Persona a partir de la base de datos que identifica Persona por su clave principal. 
	*/
	public static function setNombreById(mysqli $con, int $idPersona, string $newNombre) : true|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Persona SET Nombre = ? WHERE Id_persona = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newNombre, $idPersona);
	
	
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
	                return PersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Nombre de Persona desde la base de datos identificando Persona por su DNI único. 
	*/
	public static function setNombreByDni(mysqli $con, string $dni, string $newNombre) : true|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Persona SET Nombre = ? WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newNombre, $dni);
	
	
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
	                return PersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	/**
	* Establece el valor Nombre de Persona desde la base de datos identificando Persona por su Email único. 
	*/
	public static function setNombreByEmail(mysqli $con, string $email, string $newNombre) : true|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Persona SET Nombre = ? WHERE Email = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newNombre, $email);
	
	
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
	                return PersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Nombre
	
	
	#region SET — Apellido
	/**
	* Establece el valor Apellido de Persona a partir de la base de datos que identifica Persona por su clave principal. 
	*/
	public static function setApellidoById(mysqli $con, int $idPersona, string $newApellido) : true|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Persona SET Apellido = ? WHERE Id_persona = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newApellido, $idPersona);
	
	
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
	                return PersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Apellido de Persona desde la base de datos identificando Persona por su DNI único. 
	*/
	public static function setApellidoByDni(mysqli $con, string $dni, string $newApellido) : true|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Persona SET Apellido = ? WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newApellido, $dni);
	
	
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
	                return PersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	/**
	* Establece el valor Apellido de Persona desde la base de datos identificando Persona por su Email único. 
	*/
	public static function setApellidoByEmail(mysqli $con, string $email, string $newApellido) : true|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Persona SET Apellido = ? WHERE Email = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newApellido, $email);
	
	
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
	                return PersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Apellido
	
	
	#region SET — Dni
	/**
	* Establece el valor DNI de Persona a partir de la base de datos que identifica Persona por su clave principal. 
	*/
	public static function setDniById(mysqli $con, int $idPersona, string $newDni) : true|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Persona SET DNI = ? WHERE Id_persona = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newDni, $idPersona);
	
	
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
					if($duplicateKey == 'DNI')
					{
					    $stmt->close();
					    return PersonaError::duplicateDni(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return PersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor DNI de Persona desde la base de datos identificando Persona por su DNI único. 
	*/
	public static function setDniByDni(mysqli $con, string $dni, string $newDni) : true|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Persona SET DNI = ? WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newDni, $dni);
	
	
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
					if($duplicateKey == 'DNI')
					{
					    $stmt->close();
					    return PersonaError::duplicateDni(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return PersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	/**
	* Establece el valor DNI de Persona desde la base de datos identificando Persona por su Email único. 
	*/
	public static function setDniByEmail(mysqli $con, string $email, string $newDni) : true|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Persona SET DNI = ? WHERE Email = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newDni, $email);
	
	
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
					if($duplicateKey == 'DNI')
					{
					    $stmt->close();
					    return PersonaError::duplicateDni(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return PersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Dni
	
	
	#region SET — Email
	/**
	* Establece el valor Email de Persona a partir de la base de datos que identifica Persona por su clave principal. 
	*/
	public static function setEmailById(mysqli $con, int $idPersona, string $newEmail) : true|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Persona SET Email = ? WHERE Id_persona = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newEmail, $idPersona);
	
	
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
					if($duplicateKey == 'Email')
					{
					    $stmt->close();
					    return PersonaError::duplicateEmail(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return PersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Email de Persona desde la base de datos identificando Persona por su DNI único. 
	*/
	public static function setEmailByDni(mysqli $con, string $dni, string $newEmail) : true|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Persona SET Email = ? WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newEmail, $dni);
	
	
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
					if($duplicateKey == 'Email')
					{
					    $stmt->close();
					    return PersonaError::duplicateEmail(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return PersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	/**
	* Establece el valor Email de Persona desde la base de datos identificando Persona por su Email único. 
	*/
	public static function setEmailByEmail(mysqli $con, string $email, string $newEmail) : true|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Persona SET Email = ? WHERE Email = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newEmail, $email);
	
	
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
					if($duplicateKey == 'Email')
					{
					    $stmt->close();
					    return PersonaError::duplicateEmail(["column" => $duplicateKey, "value" => $duplicateValue]);
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return PersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Email
	
	
	#region SET — Contrasena
	/**
	* Establece el valor Contrasena de Persona a partir de la base de datos que identifica Persona por su clave principal. 
	*/
	public static function setContrasenaById(mysqli $con, int $idPersona, string $newContrasena) : true|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Persona SET Contrasena = ? WHERE Id_persona = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newContrasena, $idPersona);
	
	
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
	                return PersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Contrasena de Persona desde la base de datos identificando Persona por su DNI único. 
	*/
	public static function setContrasenaByDni(mysqli $con, string $dni, string $newContrasena) : true|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Persona SET Contrasena = ? WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newContrasena, $dni);
	
	
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
	                return PersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	/**
	* Establece el valor Contrasena de Persona desde la base de datos identificando Persona por su Email único. 
	*/
	public static function setContrasenaByEmail(mysqli $con, string $email, string $newContrasena) : true|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Persona SET Contrasena = ? WHERE Email = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newContrasena, $email);
	
	
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
	                return PersonaError::unknownDuplicate(["column" => $duplicateKey, "value" => $duplicateValue]);
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	#endregion SET — Contrasena
	
	
	
	
	#region DELETE
	//  [OPTIONAL_BLOB_RETURN_COMMENT]
	
	/**
	* Borra un Persona de la base de datos identificando Persona por su clave primaria única.
	*/
	public static function deleteById(mysqli $con, int $idPersona) : true|PersonaError|ErrorDB
	{
	    // Preparar
	    $sql = "DELETE FROM Persona WHERE Id_persona = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vincular
	    $stmt->bind_param("i", $idPersona);
	
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
	* Borra un Persona de la base de datos identificando Persona por su DNI único.
	*/
	public static function deleteByDni(mysqli $con, string $dni) : true|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "DELETE FROM Persona WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $dni);
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {
	        $stmt->close();
	        return ErrorDB::execute($sql);
	    }
	
	    return true;
	}
	
	/**
	* Borra un Persona de la base de datos identificando Persona por su Email único.
	*/
	public static function deleteByEmail(mysqli $con, string $email) : true|PersonaError|ErrorDB
	{
	    // Preparacion
	    $sql = "DELETE FROM Persona WHERE Email = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::prepare($sql);
	
	    // Vinculacion
	    $stmt->bind_param("s", $email);
	
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
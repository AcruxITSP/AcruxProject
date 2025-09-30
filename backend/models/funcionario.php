<?php
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum FuncionarioError : string
{
    case NOT_FOUND = "FUNCIONARIO_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "FUNCIONARIO_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_FUNCIONARIO = "FUNCIONARIO_DUPLICATE_ID_FUNCIONARIO";
	case DUPLICATE_DNI = "FUNCIONARIO_DUPLICATE_DNI";
}

/**
* Los atributos correspondientes a las columnas binarias de la tabla (como cualquier tipo de BLOB)
* almacenan los datos codificados en base64 en lugar del binario sin procesar.
*
* Cualquier operación que implique devolver un blob devolverá un binario codificado en base64.
*
* Cualquier operación que acepte un blob como entrada esperará un binario sin procesar.
*/
class Funcionario extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "01999b0e-deeb-7746-979d-9445b3f8cae7";

    protected mysqli $con;
	public int $idFuncionario;
	public string $nombre;
	public string $apellido;
	public string $dni;
	public ?string $email;
	public string $contrasena;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $nombre, string $apellido, string $dni, ?string $email, string $contrasena, int $idFuncionario)
	{	
		parent::__construct("contrasena");
	    $this->con = $con;
		$this->idFuncionario = $idFuncionario;
		$this->nombre = $nombre;
		$this->apellido = $apellido;
		$this->dni = $dni;
		$this->email = $email;
		$this->contrasena = $contrasena;
	}

	#region CREATE
	/** 
	* Crea un nuevo Funcionario en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $nombre, string $apellido, string $dni, ?string $email, string $contrasena, string|int $idFuncionario = self::SQL_DEFAULT) : Funcionario|FuncionarioError|ErrorDB
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
		
		// Añade 'Id_funcionario' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idFuncionario !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_funcionario";
		    $placeholders[] = "?";
		    $values[] = $idFuncionario;
		    $types .= 'i';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::NO_VALUES;
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Funcionario (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					if($duplicateKey == 'DNI')
					{
					    $stmt->close();
					    return FuncionarioError::DUPLICATE_DNI;
					}
					if($duplicateKey == 'Id_funcionario')
					{
					    $stmt->close();
					    return FuncionarioError::DUPLICATE_ID_FUNCIONARIO;
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return FuncionarioError::UNKNOWN_DUPLICATE;
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    // Funcionario se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Funcionario de la base de datos identificando Funcionario por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idFuncionario) : Funcionario|FuncionarioError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Funcionario WHERE Id_funcionario = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idFuncionario);
	
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
	        return FuncionarioError::NOT_FOUND;
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Nombre']),
			(string)($row['Apellido']),
			(string)($row['DNI']),
			(string)($row['Email']),
			(string)($row['Contrasena']),
			(int)($row['Id_funcionario'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	
	/**
	* Obtiene un Funcionario de la base de datos identificando Funcionario por su DNI único. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getByDni(mysqli $con, string $dni) : Funcionario|FuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT * FROM Funcionario WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $stmt->bind_param("s", $dni);
	
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
	        $result->close();
	        $stmt->close();
	        return FuncionarioError::NOT_FOUND;
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Nombre']),
			(string)($row['Apellido']),
			(string)($row['DNI']),
			(string)($row['Email']),
			(string)($row['Contrasena']),
			(int)($row['Id_funcionario'])
	    );
	
	    $result->close();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — IdFuncionario
	/**
	* Obtiene el valor Id_funcionario de Funcionario de la base de datos identificando Funcionario por su DNI único. 
	*/
	public static function getIdFuncionarioByDni(mysqli $con, string $dni) : int|FuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Id_funcionario FROM Funcionario WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $stmt->bind_param("s", $dni);
	
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
	        $result->close();
	        $stmt->close();
	        return FuncionarioError::NOT_FOUND;
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_funcionario']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdFuncionario
	
	
	#region GET — Nombre
	/**
	* Obtiene el valor Nombre de Funcionario de la base de datos identificando Funcionario por su clave primaria única. 
	*/
	public static function getNombreById(mysqli $con, int $idFuncionario) : string|FuncionarioError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Nombre FROM Funcionario WHERE Id_funcionario = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idFuncionario);
	
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
	        return FuncionarioError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Nombre']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Nombre de Funcionario de la base de datos identificando Funcionario por su DNI único. 
	*/
	public static function getNombreByDni(mysqli $con, string $dni) : string|FuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Nombre FROM Funcionario WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $stmt->bind_param("s", $dni);
	
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
	        $result->close();
	        $stmt->close();
	        return FuncionarioError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Nombre']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Nombre
	
	
	#region GET — Apellido
	/**
	* Obtiene el valor Apellido de Funcionario de la base de datos identificando Funcionario por su clave primaria única. 
	*/
	public static function getApellidoById(mysqli $con, int $idFuncionario) : string|FuncionarioError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Apellido FROM Funcionario WHERE Id_funcionario = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idFuncionario);
	
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
	        return FuncionarioError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Apellido']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Apellido de Funcionario de la base de datos identificando Funcionario por su DNI único. 
	*/
	public static function getApellidoByDni(mysqli $con, string $dni) : string|FuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Apellido FROM Funcionario WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $stmt->bind_param("s", $dni);
	
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
	        $result->close();
	        $stmt->close();
	        return FuncionarioError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Apellido']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Apellido
	
	
	#region GET — Dni
	/**
	* Obtiene el valor DNI de Funcionario de la base de datos identificando Funcionario por su clave primaria única. 
	*/
	public static function getDniById(mysqli $con, int $idFuncionario) : string|FuncionarioError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT DNI FROM Funcionario WHERE Id_funcionario = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idFuncionario);
	
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
	        return FuncionarioError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['DNI']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor DNI de Funcionario de la base de datos identificando Funcionario por su DNI único. 
	*/
	public static function getDniByDni(mysqli $con, string $dni) : string|FuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT DNI FROM Funcionario WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $stmt->bind_param("s", $dni);
	
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
	        $result->close();
	        $stmt->close();
	        return FuncionarioError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['DNI']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Dni
	
	
	#region GET — Email
	/**
	* Obtiene el valor Email de Funcionario de la base de datos identificando Funcionario por su clave primaria única. 
	*/
	public static function getEmailById(mysqli $con, int $idFuncionario) : string|FuncionarioError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Email FROM Funcionario WHERE Id_funcionario = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idFuncionario);
	
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
	        return FuncionarioError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Email']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Email de Funcionario de la base de datos identificando Funcionario por su DNI único. 
	*/
	public static function getEmailByDni(mysqli $con, string $dni) : string|FuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Email FROM Funcionario WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $stmt->bind_param("s", $dni);
	
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
	        $result->close();
	        $stmt->close();
	        return FuncionarioError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Email']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Email
	
	
	#region GET — Contrasena
	/**
	* Obtiene el valor Contrasena de Funcionario de la base de datos identificando Funcionario por su clave primaria única. 
	*/
	public static function getContrasenaById(mysqli $con, int $idFuncionario) : string|FuncionarioError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Contrasena FROM Funcionario WHERE Id_funcionario = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idFuncionario);
	
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
	        return FuncionarioError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Contrasena']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Contrasena de Funcionario de la base de datos identificando Funcionario por su DNI único. 
	*/
	public static function getContrasenaByDni(mysqli $con, string $dni) : string|FuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Contrasena FROM Funcionario WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $stmt->bind_param("s", $dni);
	
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
	        $result->close();
	        $stmt->close();
	        return FuncionarioError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Contrasena']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Contrasena
	
	
	
	
	#region SET — Nombre
	/**
	* Establece el valor Nombre de Funcionario a partir de la base de datos que identifica Funcionario por su clave principal. 
	*/
	public static function setNombreById(mysqli $con, int $idFuncionario, string $newNombre) : true|FuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Funcionario SET Nombre = ? WHERE Id_funcionario = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newNombre, $idFuncionario);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Nombre de Funcionario desde la base de datos identificando Funcionario por su DNI único. 
	*/
	public static function setNombreByDni(mysqli $con, string $dni, string $newNombre) : true|FuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Funcionario SET Nombre = ? WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newNombre, $dni);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — Nombre
	
	
	#region SET — Apellido
	/**
	* Establece el valor Apellido de Funcionario a partir de la base de datos que identifica Funcionario por su clave principal. 
	*/
	public static function setApellidoById(mysqli $con, int $idFuncionario, string $newApellido) : true|FuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Funcionario SET Apellido = ? WHERE Id_funcionario = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newApellido, $idFuncionario);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Apellido de Funcionario desde la base de datos identificando Funcionario por su DNI único. 
	*/
	public static function setApellidoByDni(mysqli $con, string $dni, string $newApellido) : true|FuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Funcionario SET Apellido = ? WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newApellido, $dni);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — Apellido
	
	
	#region SET — Dni
	/**
	* Establece el valor DNI de Funcionario a partir de la base de datos que identifica Funcionario por su clave principal. 
	*/
	public static function setDniById(mysqli $con, int $idFuncionario, string $newDni) : true|FuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Funcionario SET DNI = ? WHERE Id_funcionario = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newDni, $idFuncionario);
	
	
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
					    return FuncionarioError::DUPLICATE_DNI;
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return FuncionarioError::UNKNOWN_DUPLICATE;
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor DNI de Funcionario desde la base de datos identificando Funcionario por su DNI único. 
	*/
	public static function setDniByDni(mysqli $con, string $dni, string $newDni) : true|FuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Funcionario SET DNI = ? WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
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
					    return FuncionarioError::DUPLICATE_DNI;
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return FuncionarioError::UNKNOWN_DUPLICATE;
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — Dni
	
	
	#region SET — Email
	/**
	* Establece el valor Email de Funcionario a partir de la base de datos que identifica Funcionario por su clave principal. 
	*/
	public static function setEmailById(mysqli $con, int $idFuncionario, string $newEmail) : true|FuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Funcionario SET Email = ? WHERE Id_funcionario = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newEmail, $idFuncionario);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Email de Funcionario desde la base de datos identificando Funcionario por su DNI único. 
	*/
	public static function setEmailByDni(mysqli $con, string $dni, string $newEmail) : true|FuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Funcionario SET Email = ? WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newEmail, $dni);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — Email
	
	
	#region SET — Contrasena
	/**
	* Establece el valor Contrasena de Funcionario a partir de la base de datos que identifica Funcionario por su clave principal. 
	*/
	public static function setContrasenaById(mysqli $con, int $idFuncionario, string $newContrasena) : true|FuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Funcionario SET Contrasena = ? WHERE Id_funcionario = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newContrasena, $idFuncionario);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Contrasena de Funcionario desde la base de datos identificando Funcionario por su DNI único. 
	*/
	public static function setContrasenaByDni(mysqli $con, string $dni, string $newContrasena) : true|FuncionarioError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Funcionario SET Contrasena = ? WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newContrasena, $dni);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — Contrasena
}
?>
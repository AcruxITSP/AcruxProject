<?php
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum EstudianteError : string
{
    case NOT_FOUND = "ESTUDIANTE_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "ESTUDIANTE_UNKNOWN_DUPLICATE";
	case DUPLICATE_ID_ESTUDIANTE = "ESTUDIANTE_DUPLICATE_ID_ESTUDIANTE";
	case DUPLICATE_DNI = "ESTUDIANTE_DUPLICATE_DNI";
}

/**
* Los atributos correspondientes a las columnas binarias de la tabla (como cualquier tipo de BLOB)
* almacenan los datos codificados en base64 en lugar del binario sin procesar.
*
* Cualquier operación que implique devolver un blob devolverá un binario codificado en base64.
*
* Cualquier operación que acepte un blob como entrada esperará un binario sin procesar.
*/
class Estudiante extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "01999b0e-df2f-7430-80bf-54f85cb7b9cc";

    protected mysqli $con;
	public int $idEstudiante;
	public string $nombre;
	public string $apellido;
	public string $dni;
	public ?string $email;
	public string $contrasena;
	public string $reputacion;
	public int $idGrupo;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, string $nombre, string $apellido, string $dni, ?string $email, string $contrasena, int $idGrupo, int $idEstudiante, string $reputacion)
	{	
		parent::__construct("contrasena");
	    $this->con = $con;
		$this->idEstudiante = $idEstudiante;
		$this->nombre = $nombre;
		$this->apellido = $apellido;
		$this->dni = $dni;
		$this->email = $email;
		$this->contrasena = $contrasena;
		$this->reputacion = $reputacion;
		$this->idGrupo = $idGrupo;
	}

	#region CREATE
	/** 
	* Crea un nuevo Estudiante en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, string $nombre, string $apellido, string $dni, ?string $email, string $contrasena, int $idGrupo, string|int $idEstudiante = self::SQL_DEFAULT, string $reputacion = self::SQL_DEFAULT) : Estudiante|EstudianteError|ErrorDB
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
		
		// Añade 'Id_grupo' para la consulta de inserción SQL.
		$columns[] = "Id_grupo";
		$placeholders[] = "?";
		$values[] = $idGrupo;
		$types .= 'i';
		
		// Añade 'Id_estudiante' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($idEstudiante !== self::SQL_DEFAULT)
		{
		    $columns[] = "Id_estudiante";
		    $placeholders[] = "?";
		    $values[] = $idEstudiante;
		    $types .= 'i';
		}
		
		// Añade 'Reputacion' a la consulta de inserción SQL, solo si el parámetro
		// no indica que se debe utilizar el valor predeterminado de la base de datos (self::SQL_DEFAULT).
		if ($reputacion !== self::SQL_DEFAULT)
		{
		    $columns[] = "Reputacion";
		    $placeholders[] = "?";
		    $values[] = $reputacion;
		    $types .= 's';
		}
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::NO_VALUES;
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Estudiante (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
					    return EstudianteError::DUPLICATE_DNI;
					}
					if($duplicateKey == 'Id_estudiante')
					{
					    $stmt->close();
					    return EstudianteError::DUPLICATE_ID_ESTUDIANTE;
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return EstudianteError::UNKNOWN_DUPLICATE;
	            }
	        }
	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    // Estudiante se ha creado exitosamente.
	    $stmt->close();
	    return self::getById($con, $con->insert_id);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un Estudiante de la base de datos identificando Estudiante por su clave primaria única. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getById(mysqli $con, int $idEstudiante) : Estudiante|EstudianteError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT * FROM Estudiante WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idEstudiante);
	
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
	        return EstudianteError::NOT_FOUND;
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Nombre']),
			(string)($row['Apellido']),
			(string)($row['DNI']),
			(string)($row['Email']),
			(string)($row['Contrasena']),
			(int)($row['Id_grupo']),
			(int)($row['Id_estudiante']),
			(string)($row['Reputacion'])
	    );
	
	    $result->free();
	    $stmt->close();
	    return $instance;
	}
	
	/**
	* Obtiene un Estudiante de la base de datos identificando Estudiante por su DNI único. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getByDni(mysqli $con, string $dni) : Estudiante|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT * FROM Estudiante WHERE DNI = ?";
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
	        return EstudianteError::NOT_FOUND;
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self(
			$con,
			(string)($row['Nombre']),
			(string)($row['Apellido']),
			(string)($row['DNI']),
			(string)($row['Email']),
			(string)($row['Contrasena']),
			(int)($row['Id_grupo']),
			(int)($row['Id_estudiante']),
			(string)($row['Reputacion'])
	    );
	
	    $result->close();
	    $stmt->close();
	    return $instance;
	}
	#endregion GET
	
	
	#region GET — IdEstudiante
	/**
	* Obtiene el valor Id_estudiante de Estudiante de la base de datos identificando Estudiante por su DNI único. 
	*/
	public static function getIdEstudianteByDni(mysqli $con, string $dni) : int|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Id_estudiante FROM Estudiante WHERE DNI = ?";
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
	        return EstudianteError::NOT_FOUND;
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_estudiante']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdEstudiante
	
	
	#region GET — Nombre
	/**
	* Obtiene el valor Nombre de Estudiante de la base de datos identificando Estudiante por su clave primaria única. 
	*/
	public static function getNombreById(mysqli $con, int $idEstudiante) : string|EstudianteError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Nombre FROM Estudiante WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idEstudiante);
	
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
	        return EstudianteError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Nombre']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Nombre de Estudiante de la base de datos identificando Estudiante por su DNI único. 
	*/
	public static function getNombreByDni(mysqli $con, string $dni) : string|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Nombre FROM Estudiante WHERE DNI = ?";
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
	        return EstudianteError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Nombre']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Nombre
	
	
	#region GET — Apellido
	/**
	* Obtiene el valor Apellido de Estudiante de la base de datos identificando Estudiante por su clave primaria única. 
	*/
	public static function getApellidoById(mysqli $con, int $idEstudiante) : string|EstudianteError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Apellido FROM Estudiante WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idEstudiante);
	
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
	        return EstudianteError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Apellido']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Apellido de Estudiante de la base de datos identificando Estudiante por su DNI único. 
	*/
	public static function getApellidoByDni(mysqli $con, string $dni) : string|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Apellido FROM Estudiante WHERE DNI = ?";
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
	        return EstudianteError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Apellido']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Apellido
	
	
	#region GET — Dni
	/**
	* Obtiene el valor DNI de Estudiante de la base de datos identificando Estudiante por su clave primaria única. 
	*/
	public static function getDniById(mysqli $con, int $idEstudiante) : string|EstudianteError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT DNI FROM Estudiante WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idEstudiante);
	
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
	        return EstudianteError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['DNI']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor DNI de Estudiante de la base de datos identificando Estudiante por su DNI único. 
	*/
	public static function getDniByDni(mysqli $con, string $dni) : string|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT DNI FROM Estudiante WHERE DNI = ?";
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
	        return EstudianteError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['DNI']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Dni
	
	
	#region GET — Email
	/**
	* Obtiene el valor Email de Estudiante de la base de datos identificando Estudiante por su clave primaria única. 
	*/
	public static function getEmailById(mysqli $con, int $idEstudiante) : string|EstudianteError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Email FROM Estudiante WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idEstudiante);
	
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
	        return EstudianteError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Email']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Email de Estudiante de la base de datos identificando Estudiante por su DNI único. 
	*/
	public static function getEmailByDni(mysqli $con, string $dni) : string|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Email FROM Estudiante WHERE DNI = ?";
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
	        return EstudianteError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Email']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Email
	
	
	#region GET — Contrasena
	/**
	* Obtiene el valor Contrasena de Estudiante de la base de datos identificando Estudiante por su clave primaria única. 
	*/
	public static function getContrasenaById(mysqli $con, int $idEstudiante) : string|EstudianteError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Contrasena FROM Estudiante WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idEstudiante);
	
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
	        return EstudianteError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Contrasena']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Contrasena de Estudiante de la base de datos identificando Estudiante por su DNI único. 
	*/
	public static function getContrasenaByDni(mysqli $con, string $dni) : string|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Contrasena FROM Estudiante WHERE DNI = ?";
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
	        return EstudianteError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Contrasena']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Contrasena
	
	
	#region GET — Reputacion
	/**
	* Obtiene el valor Reputacion de Estudiante de la base de datos identificando Estudiante por su clave primaria única. 
	*/
	public static function getReputacionById(mysqli $con, int $idEstudiante) : string|EstudianteError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Reputacion FROM Estudiante WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idEstudiante);
	
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
	        return EstudianteError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Reputacion']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Reputacion de Estudiante de la base de datos identificando Estudiante por su DNI único. 
	*/
	public static function getReputacionByDni(mysqli $con, string $dni) : string|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Reputacion FROM Estudiante WHERE DNI = ?";
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
	        return EstudianteError::NOT_FOUND;
	    }
	
	    $attributeValue = ((string)($result->fetch_assoc()['Reputacion']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — Reputacion
	
	
	#region GET — IdGrupo
	/**
	* Obtiene el valor Id_grupo de Estudiante de la base de datos identificando Estudiante por su clave primaria única. 
	*/
	public static function getIdGrupoById(mysqli $con, int $idEstudiante) : int|EstudianteError|ErrorDB
	{
	    // Preparar
	    $sql = "SELECT Id_grupo FROM Estudiante WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vincular
	    $stmt->bind_param("i", $idEstudiante);
	
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
	        return EstudianteError::NOT_FOUND;
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_grupo']));
	    $result->free();
	    $stmt->close();
	    return $attributeValue;
	}
	
	/**
	* Obtiene el valor Id_grupo de Estudiante de la base de datos identificando Estudiante por su DNI único. 
	*/
	public static function getIdGrupoByDni(mysqli $con, string $dni) : int|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT Id_grupo FROM Estudiante WHERE DNI = ?";
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
	        return EstudianteError::NOT_FOUND;
	    }
	
	    $attributeValue = ((int)($result->fetch_assoc()['Id_grupo']));
	    $result->close();
	    $stmt->close();
	    return $attributeValue;
	}
	#endregion GET — IdGrupo
	
	
	
	
	#region SET — Nombre
	/**
	* Establece el valor Nombre de Estudiante a partir de la base de datos que identifica Estudiante por su clave principal. 
	*/
	public static function setNombreById(mysqli $con, int $idEstudiante, string $newNombre) : true|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Estudiante SET Nombre = ? WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newNombre, $idEstudiante);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Nombre de Estudiante desde la base de datos identificando Estudiante por su DNI único. 
	*/
	public static function setNombreByDni(mysqli $con, string $dni, string $newNombre) : true|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Estudiante SET Nombre = ? WHERE DNI = ?";
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
	* Establece el valor Apellido de Estudiante a partir de la base de datos que identifica Estudiante por su clave principal. 
	*/
	public static function setApellidoById(mysqli $con, int $idEstudiante, string $newApellido) : true|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Estudiante SET Apellido = ? WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newApellido, $idEstudiante);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Apellido de Estudiante desde la base de datos identificando Estudiante por su DNI único. 
	*/
	public static function setApellidoByDni(mysqli $con, string $dni, string $newApellido) : true|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Estudiante SET Apellido = ? WHERE DNI = ?";
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
	* Establece el valor DNI de Estudiante a partir de la base de datos que identifica Estudiante por su clave principal. 
	*/
	public static function setDniById(mysqli $con, int $idEstudiante, string $newDni) : true|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Estudiante SET DNI = ? WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newDni, $idEstudiante);
	
	
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
					    return EstudianteError::DUPLICATE_DNI;
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return EstudianteError::UNKNOWN_DUPLICATE;
	            }
	        }
	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor DNI de Estudiante desde la base de datos identificando Estudiante por su DNI único. 
	*/
	public static function setDniByDni(mysqli $con, string $dni, string $newDni) : true|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Estudiante SET DNI = ? WHERE DNI = ?";
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
					    return EstudianteError::DUPLICATE_DNI;
					}
	                // Alternativa en caso de que no haya un código de error para la entrada duplicada específica.
	                $stmt->close();
	                return EstudianteError::UNKNOWN_DUPLICATE;
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
	* Establece el valor Email de Estudiante a partir de la base de datos que identifica Estudiante por su clave principal. 
	*/
	public static function setEmailById(mysqli $con, int $idEstudiante, string $newEmail) : true|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Estudiante SET Email = ? WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newEmail, $idEstudiante);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Email de Estudiante desde la base de datos identificando Estudiante por su DNI único. 
	*/
	public static function setEmailByDni(mysqli $con, string $dni, string $newEmail) : true|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Estudiante SET Email = ? WHERE DNI = ?";
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
	* Establece el valor Contrasena de Estudiante a partir de la base de datos que identifica Estudiante por su clave principal. 
	*/
	public static function setContrasenaById(mysqli $con, int $idEstudiante, string $newContrasena) : true|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Estudiante SET Contrasena = ? WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newContrasena, $idEstudiante);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Contrasena de Estudiante desde la base de datos identificando Estudiante por su DNI único. 
	*/
	public static function setContrasenaByDni(mysqli $con, string $dni, string $newContrasena) : true|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Estudiante SET Contrasena = ? WHERE DNI = ?";
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
	
	
	#region SET — Reputacion
	/**
	* Establece el valor Reputacion de Estudiante a partir de la base de datos que identifica Estudiante por su clave principal. 
	*/
	public static function setReputacionById(mysqli $con, int $idEstudiante, string $newReputacion) : true|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Estudiante SET Reputacion = ? WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("si", $newReputacion, $idEstudiante);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Reputacion de Estudiante desde la base de datos identificando Estudiante por su DNI único. 
	*/
	public static function setReputacionByDni(mysqli $con, string $dni, string $newReputacion) : true|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Estudiante SET Reputacion = ? WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ss", $newReputacion, $dni);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — Reputacion
	
	
	#region SET — IdGrupo
	/**
	* Establece el valor Id_grupo de Estudiante a partir de la base de datos que identifica Estudiante por su clave principal. 
	*/
	public static function setIdGrupoById(mysqli $con, int $idEstudiante, int $newIdGrupo) : true|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Estudiante SET Id_grupo = ? WHERE Id_estudiante = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("ii", $newIdGrupo, $idEstudiante);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	
	/**
	* Establece el valor Id_grupo de Estudiante desde la base de datos identificando Estudiante por su DNI único. 
	*/
	public static function setIdGrupoByDni(mysqli $con, string $dni, int $newIdGrupo) : true|EstudianteError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Estudiante SET Id_grupo = ? WHERE DNI = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("is", $newIdGrupo, $dni);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — IdGrupo
}
?>
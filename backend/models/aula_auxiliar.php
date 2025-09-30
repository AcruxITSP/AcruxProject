<?php
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum AulaAuxiliarError : string
{
    case NOT_FOUND = "AULAAUXILIAR_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "AULAAUXILIAR_UNKNOWN_DUPLICATE";
	
}

/**
* Los atributos correspondientes a las columnas binarias de la tabla (como cualquier tipo de BLOB)
* almacenan los datos codificados en base64 en lugar del binario sin procesar.
*
* Cualquier operación que implique devolver un blob devolverá un binario codificado en base64.
*
* Cualquier operación que acepte un blob como entrada esperará un binario sin procesar.
*/
class AulaAuxiliar extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "01999b0e-df74-7d11-bdea-047c6b5741c0";

    protected mysqli $con;
	public int $idAula;
	public int $idAuxiliar;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, int $idAula, int $idAuxiliar)
	{
	    $this->con = $con;
		$this->idAula = $idAula;
		$this->idAuxiliar = $idAuxiliar;
	}

	#region CREATE
	/** 
	* Crea un nuevo AulaAuxiliar en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, int $idAula, int $idAuxiliar) : AulaAuxiliar|AulaAuxiliarError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Id_aula' para la consulta de inserción SQL.
		$columns[] = "Id_aula";
		$placeholders[] = "?";
		$values[] = $idAula;
		$types .= 'i';
		
		// Añade 'Id_auxiliar' para la consulta de inserción SQL.
		$columns[] = "Id_auxiliar";
		$placeholders[] = "?";
		$values[] = $idAuxiliar;
		$types .= 'i';
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::NO_VALUES;
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Aula_Auxiliar (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $stmt->bind_param($types, ...$values);
		
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        // Se ha producido un error durante la ejecución de la consulta.
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    // AulaAuxiliar se ha creado exitosamente.
	    $stmt->close();
	    return AulaAuxiliarError::NOT_FOUND;
	}
	#endregion
	
	
	#region GET
	
	#endregion GET
	
	
	
	
	#region SET — IdAula
	/**
	* Establece el valor Id_aula de AulaAuxiliar a partir de la base de datos que identifica AulaAuxiliar comparando todos sus atributos. 
	*/
	public static function setIdAulaByStrict(mysqli $con, int $idAula, int $idAuxiliar, int $newIdAula) : true|AulaAuxiliarError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Aula_Auxiliar SET Id_aula = ? WHERE Id_aula = ? AND Id_auxiliar = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("iii", $idAula, $idAula, $idAuxiliar);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — IdAula
	
	
	#region SET — IdAuxiliar
	/**
	* Establece el valor Id_auxiliar de AulaAuxiliar a partir de la base de datos que identifica AulaAuxiliar comparando todos sus atributos. 
	*/
	public static function setIdAuxiliarByStrict(mysqli $con, int $idAula, int $idAuxiliar, int $newIdAuxiliar) : true|AulaAuxiliarError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Aula_Auxiliar SET Id_auxiliar = ? WHERE Id_aula = ? AND Id_auxiliar = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("iii", $idAuxiliar, $idAula, $idAuxiliar);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — IdAuxiliar
}
?>
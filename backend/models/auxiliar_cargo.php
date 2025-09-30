<?php
include_once '../db/db_errors.php';
include_once 'base_model.php';

enum AuxiliarCargoError : string
{
    case NOT_FOUND = "AUXILIARCARGO_NOT_FOUND";
    case UNKNOWN_DUPLICATE = "AUXILIARCARGO_UNKNOWN_DUPLICATE";
	
}

/**
* Los atributos correspondientes a las columnas binarias de la tabla (como cualquier tipo de BLOB)
* almacenan los datos codificados en base64 en lugar del binario sin procesar.
*
* Cualquier operación que implique devolver un blob devolverá un binario codificado en base64.
*
* Cualquier operación que acepte un blob como entrada esperará un binario sin procesar.
*/
class AuxiliarCargo extends BaseModel
{
    /**
    * Esta constante se utiliza para indicar que una operación INSERT debe
    * utilizar el valor predeterminado o automático dictado en la base de datos para una columna.
    * Este valor constante no tiene ningún significado, es solo un indicador y dicho
    * valor debería ser imposible de replicar por accidente (se utiliza un GUID por este motivo)
    */
    const SQL_DEFAULT = "01999b0e-df70-7322-973c-d235229c8ae7";

    protected mysqli $con;
	public int $idAuxiliar;
	public int $idCargo;

	/**
	* En caso de que un parámetro represente una columna SQL de cualquier tipo BLOB, se debe introducir el binario sin procesar,
	* dicho binario se codificará en base64 al almacenarlo.
	*/
	protected function __construct(mysqli $con, int $idAuxiliar, int $idCargo)
	{
	    $this->con = $con;
		$this->idAuxiliar = $idAuxiliar;
		$this->idCargo = $idCargo;
	}

	#region CREATE
	/** 
	* Crea un nuevo AuxiliarCargo en la base de datos.
	* Los parámetros cuyos valores predeterminados son `self::SQL_DEFAULT` son opcionales, por lo tanto,
	* la base de datos les asignará un valor predeterminado o automático si no se especifica ningún otro valor.
	*/
	public static function create(mysqli $con, int $idAuxiliar, int $idCargo) : AuxiliarCargo|AuxiliarCargoError|ErrorDB
	{
	    // Preparacion dinamica de datos a insertar
	    $null = null;
	    $columns = [];
	    $placeholders = [];
	    $values = [];
	    $types = "";
	
		// Añade 'Id_auxiliar' para la consulta de inserción SQL.
		$columns[] = "Id_auxiliar";
		$placeholders[] = "?";
		$values[] = $idAuxiliar;
		$types .= 'i';
		
		// Añade 'Id_cargo' para la consulta de inserción SQL.
		$columns[] = "Id_cargo";
		$placeholders[] = "?";
		$values[] = $idCargo;
		$types .= 'i';
	    
	    // Si no hay columnas que insertar, informe este problema.
	    if (empty($columns)) {
	        return ErrorDB::NO_VALUES;
	    }
	
	    // Preparacion
	    $sql = "INSERT INTO Auxiliar_Cargo (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
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
	
	    // AuxiliarCargo se ha creado exitosamente.
	    $stmt->close();
	    return self::getByFKs($con, $idAuxiliar, $idCargo);
	}
	#endregion
	
	
	#region GET
	/**
	* Obtiene un 'AuxiliarCargo' de la base de datos identificando 'AuxiliarCargo' por sus claves externas como una clave compuesta. 
	* Take into account that any attribute of type BLOB will store the base64 encoding of the blob.
	*/
	public static function getByFKs(mysqli $con, int $idAuxiliar, int $idCargo) : AuxiliarCargo|AuxiliarCargoError|ErrorDB
	{
	    // Preparacion
	    $sql = "SELECT * FROM Auxiliar_Cargo WHERE Id_auxiliar = ? AND Id_cargo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $stmt->bind_param("ii", $idAuxiliar, $idCargo);
	
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
	        return AuxiliarCargoError::NOT_FOUND;
	    }
	
	    $row = $result->fetch_assoc();
	    $instance = new self (
			$con,
			(int)($row['Id_auxiliar']),
			(int)($row['Id_cargo'])
	    );
	    
	    $stmt->close();
	    $result->free();
	    return $instance;
	}
	#endregion GET
	
	
	
	
	#region SET — IdAuxiliar
	/**
	* Establece el valor Id_auxiliar de AuxiliarCargo a partir de la base de datos que identifica AuxiliarCargo mediante sus claves externas como clave compuesta. 
	*/
	public static function setIdAuxiliarByFKs(mysqli $con, int $idAuxiliar, int $idCargo, int $newIdAuxiliar) : true|AuxiliarCargoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Auxiliar_Cargo SET Id_auxiliar = ? WHERE Id_auxiliar = ? AND Id_cargo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("iii", $newIdAuxiliar, $idAuxiliar, $idCargo);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — IdAuxiliar
	
	
	#region SET — IdCargo
	/**
	* Establece el valor Id_cargo de AuxiliarCargo a partir de la base de datos que identifica AuxiliarCargo mediante sus claves externas como clave compuesta. 
	*/
	public static function setIdCargoByFKs(mysqli $con, int $idAuxiliar, int $idCargo, int $newIdCargo) : true|AuxiliarCargoError|ErrorDB
	{
	    // Preparacion
	    $sql = "UPDATE Auxiliar_Cargo SET Id_cargo = ? WHERE Id_auxiliar = ? AND Id_cargo = ?";
	    $stmt = $con->prepare($sql);
	    if(!$stmt) return ErrorDB::PREPARE;
	
	    // Vinculacion
	    $null = null;
	    $stmt->bind_param("iii", $newIdCargo, $idAuxiliar, $idCargo);
	
	
	    // Ejecucion
	    if(!$stmt->execute())
	    {	
	        $stmt->close();
	        return ErrorDB::EXECUTE;
	    }
	
	    return true;
	}
	#endregion SET — IdCargo
}
?>
<?php
require_once dirname(__FILE__).'/../db/db_errors.php';

abstract class SQL
{
	private static function replaceBlobsValuesWithNull(string $types, array $values) : array
	{
		$_values = [];
		$_types = str_split($types);
		for($i = 0; $i < count($values); ++$i)
		{
			$type = $_types[$i];
			$value = $values[$i]; 
			$_values[] = $type === 'b' ? null : $value;
		}

		return $_values;
	}

	private static function getBlobEntries(string $types, array $values) : array
	{
		$_values = [];
		$_types = str_split($types);
		for($i = 0; $i < count($values); ++$i)
		{
			$type = $_types[$i];
			if($type !== 'b') continue;

			$value = $values[$i];
			$_values[$i] = $value;
		}

		return $_values;
	}

	public static function query(mysqli $con, string $query, string $types, ...$values) : mysqli_stmt|ErrorDB
	{
		$stmt = $con->prepare($query);
		if(!$stmt) return ErrorDB::prepare($query);

		$unblobedValues = self::replaceBlobsValuesWithNull($types, $values);
		if(!$stmt->bind_param($types, ...$unblobedValues)) return ErrorDB::bindParam($query);

		$blobEntries = self::getBlobEntries($types, $values);
		foreach($blobEntries as $blobIndex => $blob)
		{
			if(!$stmt->send_long_data($blobIndex, $blob)) return ErrorDB::sendLongData($query);
		}

		if(!$stmt->execute()) return ErrorDB::execute($query);
		return $stmt;
	}

	public static function valueQuery(mysqli $con, string $query, string $types, ...$values) : mysqli_result|ErrorDB
	{
		$stmt = self::query($con, $query, $types, ...$values);
		if(!($stmt instanceof mysqli_stmt)) return $stmt;

		$result = $stmt->get_result();
		if(!$result) return ErrorDB::result($query);

		return $result;
	}

	public static function actionQuery(mysqli $con, string $query, string $types, ...$values) : true|ErrorDB
	{
		$stmt = self::query($con, $query, $types, ...$values);
		if(!($stmt instanceof mysqli_stmt)) return $stmt;

		return true;
	}
}
?>
<?php
include_once dirname(__FILE__).'/../error_base.php';

class ErrorDB extends ErrorBase
{
    public static function noValues(string $table) : ErrorDB
    {
        return new ErrorDB(ErrorDBType::NO_VALUES, $table);
    }

    public static function prepare(string $sql) : ErrorDB
    {
        return new ErrorDB(ErrorDBType::PREPARE, $sql);
    }

    public static function bindParam(string $sql) : ErrorDB
    {
        return new ErrorDB(ErrorDBType::BIND_PARAM, $sql);
    }

    public static function sendLongData(string $sql) : ErrorDB
    {
        return new ErrorDB(ErrorDBType::SEND_LONG_DATA, $sql);
    }

    public static function execute(string $sql) : ErrorDB
    {
        return new ErrorDB(ErrorDBType::EXECUTE, $sql);
    }

    public static function result(string $sql) : ErrorDB
    {
        return new ErrorDB(ErrorDBType::RESULT, $sql);
    }
}

enum ErrorDBType : string
{
    case NO_VALUES          = "DB_NO_VALUES";
    case PREPARE            = "DB_PREPARE";
    case BIND_PARAM         = "DB_BIND_PARAM";
    case SEND_LONG_DATA     = "DB_SEND_LONG_DATA";
    case EXECUTE            = "DB_EXECUTE";
    case RESULT             = "DB_RESULT";
}
?>
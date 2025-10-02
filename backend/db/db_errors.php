<?php
enum ErrorDB : string
{
    case NO_VALUES          = "DB_NO_VALUES";
    case PREPARE            = "DB_PREPARE";
    case SEND_LONG_DATA     = "DB_SEND_LONG_DATA";
    case EXECUTE            = "DB_EXECUTE";
    case RESULT             = "DB_RESULT";
}
?>
<?php

abstract class Respuestas
{
    public static function enviarOk($valor) : void
    {
        http_response_code(200);
        echo json_encode(["ok" => true, "value" => $valor]);
        die();
    }

    public static function enviarError($valor) : void
    {
        http_response_code(500);
        echo json_encode(["ok" => false, "value" => $valor]);
        die();
    }
}

?>
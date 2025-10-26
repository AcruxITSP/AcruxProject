<?php
require_once dirname(__FILE__).'/error_base.php';
abstract class Respuestas
{
    public static function enviarOk(mixed $valor = null, ?mysqli $con = null) : void
    {
        if($con)
        {
            $con->commit();
            $con->close();
        }
        http_response_code(200);
        echo json_encode(["ok" => true, "value" => $valor]);
        die();
    }

    public static function enviarError($valor, ?mysqli $con = null) : void
    {
        if($con)
        {
            $con->rollback();
            $con->close();
        }
        http_response_code(500);
        echo json_encode(["ok" => false, "value" => $valor]);
        die();
    }

    public static function enviar($valorOError, ?mysqli $con = null) : void
    {
        if(is_a($valorOError, 'ErrorBase')) self::enviarError($valorOError, $con);
        self::enviarOk($valorOError, $con);
    }
}

?>
<?php
require_once dirname(__FILE__).'/../../utils/respuestas.php';
function sendRedirectResponse(string $location)
{
    Respuestas::enviarOk(["redirect_location" => $location]);
}
?>
<?php
date_default_timezone_set('America/Montevideo');
function connectDb() : ?mysqli
{
    $servername = "p:localhost";
    $username = "root";
    $password = "";
    $dbname = "proyecto_itsp";

    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    mysqli_report(MYSQLI_REPORT_OFF);

    // Check connection
    if (!$conn) return null;
    return $conn;
}
?>
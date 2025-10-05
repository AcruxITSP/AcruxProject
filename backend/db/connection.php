<?php
function connectDb() : ?mysqli
{
    $servername = "p:localhost";
    $username = "root";
    $password = "";
    $dbname = "db_acrux";

    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $dbname, 4000);
    mysqli_report(MYSQLI_REPORT_OFF);

    // Check connection
    if (!$conn) return null;
    return $conn;
}
?>
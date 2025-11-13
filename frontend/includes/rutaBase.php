<?php

$regex = '/xampp/'; 
$ruta = __DIR__;

if (preg_match_all($regex, $ruta, $coincidencias)) {
    define('BASE_PATH', "/frontend");
} else {
    define('BASE_PATH', "/Acrux/AcruxProject3/frontend");
}


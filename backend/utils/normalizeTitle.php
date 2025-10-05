<?php
function normalizeTitle($string) {
    // 1. Convert to lowercase
    $string = mb_strtolower($string, 'UTF-8');
    
    // 2. Remove Spanish accents
    $replacements = [
        'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
        'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U',
        'ñ'=>'n','Ñ'=>'N'
    ];
    $string = strtr($string, $replacements);
    
    // 3. Capitalize first letter of each word
    $string = ucwords($string);
    $string = trim($string);
    
    return $string;
}
?>
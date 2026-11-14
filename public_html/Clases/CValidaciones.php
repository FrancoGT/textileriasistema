<?php
/**
 * Reemplaza todos los acentos por sus equivalentes sin ellos
 *
 * @param $string
 *  string la cadena a sanear
 *
 * @return $string
 *  string saneada
 */

function sanear_string($string) {
    $string = utf8_encode($string);
    $string = str_replace(array('ñ', 'Ñ', 'Ã±','Ã‘'), 'N', $string);
    $string = trim($string);
    $string = str_replace(array('ç', 'Ç', 'Ã§', 'Ã'), 'C', $string);
    $string = str_replace(array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä', 'Ã¡', 'Ã ', 'Ã¤', 'Ã¢', 'Âª', 'Ã', 'Ã', 'Ã', 'Ã'), 'A', $string);
    $string = str_replace(array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë', 'Ã©', 'Ã¨', 'Ã«', 'Ãª', 'Ã', 'Ã', 'Ã', 'Ã'), 'E', $string);
    $string = str_replace(array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î', 'Ã­', 'Ã¬', 'Ã¯', 'Ã®', 'Ã', 'Ã', 'Ã', 'Ã'), 'I', $string);
    $string = str_replace(array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô', 'Ã³', 'Ã²', 'Ã¶', 'Ã´', 'Ã', 'Ã', 'Ã', 'Ã'), 'O', $string);
    $string = str_replace(array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü', 'Ãº', 'Ã¹', 'Ã¼', 'Ã»', 'Ã', 'Ã', 'Ã', 'Ã'), 'U', $string);
    //Esta parte se encarga de eliminar cualquier caracter extraño
    $string = str_replace(array("\\", "¨", "º", "~", "|" , "\"", "·", "$", "%", "'" , "¡" , "&", "¿", "^", "`", "¨", "´"), '', $string);
    $string = strtoupper($string);
    return $string;
}

function VMas($string) {
   $string = str_replace(" ", '+', $string);
   return $string;
}
?>

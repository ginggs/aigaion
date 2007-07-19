<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for filenames. Takes any string, and removes characters that are
|   problematic in filenames
| -------------------------------------------------------------------
|
|       
*/

    function toCleanName($string) {
        $string = strtolower($string);
        $string =  preg_replace('[\W]','',$string);
        return $string;
    }

?>
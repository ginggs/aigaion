<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
|  Helper for searchable cleannames (authors, titles, journals, etc). 
| -------------------------------------------------------------------
|
|       
*/

    function authorCleanName($author) {
        $CI = &get_instance();
        $CI->load->helper('utf8_to_ascii');
        return utf8_to_ascii($author->getName('vlf'));
    }
    function cleanTitle($title) {
        $CI = &get_instance();
        $CI->load->helper('utf8_to_ascii');
        return utf8_to_ascii($title);
    }

?>
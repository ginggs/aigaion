<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
| -------------------------------------------------------------------
|  Language Filter
| -------------------------------------------------------------------
|
|   This filter will autoload the main language files for the appropriate language.
|   Login filter must have been loaded first!
|*/
class Lang_filter extends Filter {
    function before() {
        $CI = &get_instance();
        $userlogin = getUserLogin();
        $CI->lang->load('main',$userlogin->getPreference('language'));
    }
    
}
?>
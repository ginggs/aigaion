<?php
/** Login management from httpauth. All this class needs to do is provide on request a login name or groups
of somebody currently login according to the external login module (httpauth in this case...). */
class Login_httpauth {
    function getLoginName() {
        //return the proper name
        return $_SERVER['PHP_AUTH_USER'];
    }
    
    function getLoginGroups() {
        //return all groups that this user belongs to... (the same names that are stored in aigaion in the abbreviation)
        //not implemented, as I cannot seem to find where the htgroup info is stored in the $_SERVER variable :(
        return array(); 
    }
}
?>
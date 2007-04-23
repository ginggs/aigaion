<?php
/** Login management from httpauth. All this class needs to do is provide on request a login name or groups
of somebody currently login according to the external login module (httpauth in this case...). */
class Login_httpauth {
    /** Returns an associative array containing the login name of the user and all groups that this user 
    belongs to... (the same names that are stored in aigaion in the abbreviation) */
    function getLoginInfo() {
        //return the proper name
        return array('login'=>$_SERVER['PHP_AUTH_USER'],'groups'=>array());
    }
    
}
?>
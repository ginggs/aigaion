<?php

/** Login management from LDAP. */
class Login_ldap {
    /** Returns an associative array containing the login name of the user and all groups that this user 
    belongs to... (the same names that are stored in aigaion in the abbreviation). Expects user and password
    to be stored in the POST */
    function getLoginInfo() {
        //get username and/or pwd from POST
        $loginName = '';
        $loginPwd = '';
        $groups = array();
        if (((isset($_POST["loginName"]))) && (isset($_POST['loginPass'])))    {
            #user logs in via login screen.
            //get username & pwd
            $postloginName = $_POST["loginName"];
            $postloginPwd = $_POST['loginPass'];
            //now try to login from LDAP 
        	$ldap = new Authldap(getConfigurationSetting('LDAP_SERVER'), getConfigurationSetting('LDAP_BASE_DN'), "ActiveDirectory", /* $sDomain = */ "", $postloginName, $postloginPwd);
        
        	$retvalue=NULL;
        	if ( !($ds=$ldap->connect())) {
          		appendErrorMessage("LDAP auth: There was a problem.<br>");
          		appendErrorMessage( "Error code : " . $ldap->ldapErrorCode . "<br>");
          		appendErrorMessage( "Error text : " . $ldap->ldapErrorText . "<br>");
        	} else {//if ($ldap->authBind($bind_param,$loginPwd)) {
        		$loginName = $postloginName;
        		//get groups...
        	}// else {
          		//appendErrorMessage( "LDAP auth: Password check failed.<br>");
          	//	appendErrorMessage( "Error code : " . $ldap->ldapErrorCode . "<br>");
          		//appendErrorMessage( "Error text : " . $ldap->ldapErrorText . "<br>");
        	//}
        }
        
        return array('login'=>$loginName,'groups'=>$groups);
    }

}
?>
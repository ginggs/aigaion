<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php

/** Using LDAP for password checking. */
class Passwordchecker_ldap {
    /** 
    Check password using LDAP; get as much info as possible from the LDAP server.
    NEED TESTING
    */
    function checkPassword($uname, $password,$pwdInMd5) {
        if ($pwdInMd5) {
            return array('uname'=>'','notice'=>'The LDAP password checker cannot handle md5 passwords yet');
        }
        $CI = &get_instance();
        $CI->load->library('authldap');
        //now try to login from LDAP 
        $ldap = new Authldap(getConfigurationSetting('LDAP_SERVER'),
                             getConfigurationSetting('LDAP_BASE_DN'),
                             "ActiveDirectory", 
                             getConfigurationSetting('LDAP_DOMAIN'),
                             "", "");
        //$ldap->dn = getConfigurationSetting('LDAP_BASE_DN');
        //$ldap->server = getConfigurationSetting('LDAP_SERVER');
    	/*
    	$ldap = new Authldap(
    	getConfigurationSetting('LDAP_SERVER'), 
    	getConfigurationSetting('LDAP_BASE_DN'), 
    	"ActiveDirectory",  $sDomain =  "", 
    	$postloginName, $postloginPwd);
        */
    	$ds = $ldap->connect();
    	if (!$ds) {
      		appendErrorMessage("LDAP auth: There was a problem.<br/>");
      		appendErrorMessage( "Error code : " . $ldap->ldapErrorCode . "<br/>");
      		appendErrorMessage( "Error text : " . $ldap->ldapErrorText . "<br/>");
    	} else {
   	    
    	    if ($ldap->checkPass($uname,$password)) {
        		//get groups...
        		//get other personal info...
        		return array('uname'=>$uname);//,'groups'=>$groups);
        	} else {
        	    appendErrorMessage($ldap->ldapErrorText);
        	    return array('uname'=>'');
        	}
    	    
    	}
      		//appendErrorMessage( "LDAP auth: Password check failed.<br/>");
      	//	appendErrorMessage( "Error code : " . $ldap->ldapErrorCode . "<br/>");
      		//appendErrorMessage( "Error text : " . $ldap->ldapErrorText . "<br/>");
        
    }

}
?>
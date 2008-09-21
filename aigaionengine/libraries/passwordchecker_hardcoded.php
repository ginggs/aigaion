<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?><?php

/**  */
class Passwordchecker_hardcoded extends Passwordchecker {
    /** 
    Hard coded password check: only uname/pwd combinations listed below are valid. Uncomment to activate.
    */
    function checkPassword($uname, $password,$pwdInMd5) {
        $accounts = array ('testu'=>'testp');
        foreach ($accounts as $u=>$p) {
            if ($pwdInMd5) {
                $p = md5($p);
            }
            if ($uname==$u && $password==$p) {
                return array('uname'=>$u,'surname'=>'Last Name of user '.$u); 
            }
        } 
        return array('uname'=>'','notice'=>'Not a valid login!'); 
    }

}
?>
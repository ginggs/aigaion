<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
| -------------------------------------------------------------------
|  Login Filter
| -------------------------------------------------------------------
|
|   This filter will check whether a user is logged in.
|   If so, nothing is done. 
|   If not, one of two actions is taken:
|       a) this filter will redirect the system  through the login/dologin controller
|    or b) this filter will return an empty div.
|   The choice between the two actions is determined by the filter config parameter
|   'action', which can have one of two values ('redirect','fail')
|*/
class Login_filter extends Filter {
  function before() {
    $CI = &get_instance();

    //get login object
    $userlogin = getUserLogin();

    //if not logged in: redirect to login/dologin
    if (!$userlogin->isLoggedIn()) {
      $segments = $CI->uri->segment_array();
      if ($this->config['action']=='fail') {
        redirect('/login/fail/');
      }
      redirect('/login/dologin/'.implode('/',$segments));
    }
  }
}
?>
<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Login extends Controller {

	function Login()
	{
		parent::Controller();	
	}
	
	/** The main controller function will of course show the login form.
	    Note that one may pass a specification of a target page where
	    the user should be redirected after a successful login by 
	    appending a path: 'login/index/path/to/redirect/page' */
	function index()
	{
		$segments = $this->uri->segment_array();
		//remove first two elements
		array_shift($segments);
		array_shift($segments);
        //IF ALREADY LOGGED IN: LINK ON.... TO ANOTHER PAGE
	    //get login object
    	$userlogin = getUserLogin();
    	if ($userlogin->isLoggedIn()) {
    	    redirect(implode('/',$segments));
        }
        $data = array('segments' => $segments);
  
    //set header data
    $header ['title']       = 'Aigaion 2.0 - Please login';
    
    //get output
    $output  = $this->load->view('header_clean',        $header,  true);
    $output .= $this->load->view('login/form',          $data,    true);
    $output .= $this->load->view('footer_clean',        '',       true);
    
    //set output
    $this->output->set_output($output);
	}

    /** This controller will perform the login. The login may be submitted 
        from the login form, or the login may be attempted in one of the numerous 
        other ways (public access, external login, etc). This controller is also
        called when a page is requested that is protected by login while the user 
        is not yet logged in.
        When login fails, the user is directed back to the login form 
        (main login controller). When login succeeds, the user is redirected 
        to the front page, or, if specified, the page passed with the original
        request for the login form. */
	function dologin()
	{
    //get login object
  	$userlogin = getUserLogin();

		//try to login
		$userlogin->login();
		if ($userlogin->isLoggedIn()) {
    		//if success: redirect
  	    $this->latesession->set('USERLOGIN', $userlogin);
    		$segments = $this->uri->segment_array();
    		//remove first two elements
    		array_shift($segments);
    		array_shift($segments);
    		redirect(implode('/',$segments));
		} else {
		    //if failure: redirect
    		$segments = $this->uri->segment_array();
    		//remove first two elements
    		array_shift($segments);
    		array_shift($segments);
    		redirect('/login/index/'.implode('/',$segments));
	    }
	}
	
	/** This controller will log the currently logged-in user out, then redirect 
	    to the dologin controller to allow the system to login an anon account
	    (if allowed and posssible). */
	function dologout()
	{
	    //get login object
	  	$userlogin = getUserLogin();
        //logout
		$userlogin->logout();
	    //redirect
   		redirect('');
	}
	
	/** 
	login/anonymous
	
	This controller attempts to login one of the guest accounts. Any other currently
	logged user is logged out.
	
	Fails when one of the following:
	    the given guest account does not exist or is not anonymous
	    no anonymous access is allowed
	    
	Parameters passed by segment:
	    3rd: user_id of the guest account. default taken from config setting 'ANONYMOUS_USER'
	    
	Redirects to the front page
	*/
	function anonymous() {
	    if (getConfigurationSetting('ENABLE_ANON_ACCESS')!='TRUE') {
	        appendErrorMessage('Anonymous accounts are not enabled<br>');
	        redirect('');
	    }
	    
	    $user_id = $this->uri->segment(3,getConfigurationSetting('ANONYMOUS_USER'));
	    $user = $this->user_db->getByID($user_id);
	    if (($user==null)||(!$user->isAnonymous)) {
	        appendErrorMessage('Anonymous login: no existing anonymous user_id provided<br>');
	        redirect('');
	    }
	    
	    //get login object
	  	$userlogin = getUserLogin();
        //logout
		$userlogin->logout();
		//login given anonymous user
	    $result = $userlogin->loginAnonymous($user->user_id);
	    if (($result==1)||($result==2)) {
            appendErrorMessage('Error logging in anonymous account<br>');
	    }
	    redirect('');
	}
	/** This controller function displays a failure message in a div. no surrounding 
	    HTML is included. This can be used for controllers that in themselves do not
	    show a full html page but rather a sub-view, and where failure to login should
	    not redirect the user to a login page. */
	function fail() {
	    $this->load->view('login/fail');
	}
}
?>
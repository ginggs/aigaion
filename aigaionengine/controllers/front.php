<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Front extends Controller {

	function Front()
	{
		parent::Controller();	
	}
	
	/** A simple front page, for testing. */
	function index()
	{
	    //the front controller reloads the config settings and the user preferences;
	    //after that it redirects to the topic tree. Why is this? Because otherwise another user would not pick
	    //up changed config settings (e.g. new file types) without loggin of, closing the browser and cleaning the session.
        $this->latesession->set('SITECONFIG',null);
        $userlogin = &getUserLogin();
        $userlogin->initPreferences();
		redirect('topics');
	}
}
?>
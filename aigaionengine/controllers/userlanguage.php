<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Userlanguage extends Controller {

	function Userlanguage()
	{
		parent::Controller();	
	}
	
	/** There is no default controller . */
	function index()
	{
		redirect('');
	}



    /** 
    setlanguage/set
    
    access point to *temporarily* set the language (for this login session)

    Fails (with error message) when one of: 
        non existing language

    Information passed through segments:
        3rd: language
        
    Returns:
        redirects somewhere
            
    */    
    function set() {
        $language = $this->uri->segment(3); 
        $userlogin = getUserLogin();
        $userlogin->preferences['language'] = $language;
        $this->latesession->set('USERLOGIN',$userlogin);
		$segments = $this->uri->segment_array();
		//remove first three elements
		array_shift($segments);
		array_shift($segments);
		array_shift($segments);
		redirect(implode('/',$segments));
        redirect('');
    }  

}


?>
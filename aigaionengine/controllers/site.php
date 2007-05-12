<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Site extends Controller {

	function Site()
	{
		parent::Controller();	
	}
	
	/** Pass control to the site/configure/ controller */
	function index()
	{
		redirect('site/configure');
	}

    /** 
    site/configure
        
    Fails when unsufficient user rights
    
    Paramaters:
        3rd segment: if 3rd segment is 'commit', site config data is expected in the POST
    
    Returns a full html page with a site configuration form. */
	function configure()
	{
	    //check rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('database_manage'))
            ) 
        {
	        appendErrorMessage('Configure database: insufficient rights.<br>');
	        redirect('');
        }
        
	    $commit = $this->uri->segment(3,'');
	    
	    $this->load->library('validation');
        $this->validation->set_error_delimiters('<div class="errormessage">Changes not committed: ', '</div>');
	    if ($commit=='commit') {
	        $siteconfig = $this->siteconfig_db->getFromPost();
	        if ($siteconfig!= null) {
    	        //do validation
                //----no validation rules are implemented yet. When validation rules are defined, see e.g. users/commit for
                //examples of validation code
            	//if ($this->validation->run() == TRUE) {
    	            //if validation successfull, set settings
    	            $siteconfig->update();
    	            $siteconfig = $this->siteconfig_db->getSiteConfig();
    	        //}
    	    }
	    } else {
	        $siteconfig = $this->siteconfig_db->getSiteConfig();
	    }
	    
        //get output: always return to configuration page
        $headerdata = array();
        $headerdata['title'] = 'Aigaion 2.0: Site configuration';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        
        $output .= $this->load->view('site/edit',
                                      array('siteconfig'=>$siteconfig),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}

	/** 
	site/maintenance
	
	Entry point for maintenance functions.
	
	Fails with error message when one of:
	    insufficient user rights
	    non-existing maintenance function given

	Paramaters:
	    3rd segment: name of the maintenance function to be executed (can be 'all')
	    
	Returns:
	    A full HTML page presenting 
	        the maintenance options
	        plus, if a maintenance function is given, the result of the chosen maintenance option 
	*/
	function maintenance()
	{
	    //check rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('database_manage'))
            ) 
        {
	        appendErrorMessage('Configure database: insufficient rights.<br>');
	        redirect('');
        }

	    $maintenance = $this->uri->segment(3,'');

	    if ($maintenance != '') {
	        appendMessage('Maintenance function '.$maintenance.' not implemented yet.<br>');
	    }

        //get output
        $headerdata = array();
        $headerdata['title'] = 'Aigaion 2.0: Site maintenance';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('site/maintenance',
                                      array(),
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
    }
    

}
?>
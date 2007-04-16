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
	    $commit = $this->uri->segment(3,'');
	    
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = 'Aigaion 2.0: Site configuration';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        
        
        $output .= $this->load->view('site/edit',
                                      array(),  
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
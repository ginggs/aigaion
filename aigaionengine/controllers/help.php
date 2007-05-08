<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Help extends Controller {

	function Help()
	{
		parent::Controller();	
	}
	
	/**  */
	function index()
	{
	    redirect ('help/view');
	}
	

	function view() {
        //get output
        $headerdata = array();
        $headerdata['title'] = 'Aigaion 2.0: Site configuration';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        
        $output .= $this->load->view('help/header',
                                      array(),  
                                      true);
        $output .= $this->load->view('help/'.$this->uri->segment(3,'front'),
                                      array(),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);

	}

	
}
?>
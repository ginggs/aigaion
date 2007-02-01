<?php

class Configuration extends Controller {

	function Configuration()
	{
		parent::Controller();
	}
	
	function index()
	{
	  $this->edit();
	}

  //edit() - Call configuration edit form.
  function edit()
  {
    $headerdata = array();
    $headerdata['title'] = 'Site Configuration';
    
    $output = $this->load->view('header', $headerdata, true);
    $output .= "configuration edit form";
    $output .= $this->load->view('footer','', true);
    
    $this->output->set_output($output);
    
    //call configuration form
  }
  
  //commit() - Commit the posted configuration to the database
  function commit()
  {
    echo "Configuration commit";
    
    //retrieve configuration from POST
    //commit configuration
  }
}
?>
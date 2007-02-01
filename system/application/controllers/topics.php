<?php

class Topics extends Controller {

	function Topics()
	{
		parent::Controller();
	}
	
	function index()
	{
	  $this->show();
	}

  //show() - Call single topic overview
  function show()
  {
    $headerdata = array();
    $headerdata['title'] = 'Topic';
    
    $output = $this->load->view('header', $headerdata, true);
    $output .= "Single topic view";
    $output .= $this->load->view('footer','', true);
    
    $this->output->set_output($output);
    
    //get ID from segment
    
    //load view
  }
  
  //edit() - Call topic edit form. When no ID is given: new topicform
  function edit()
  {
    echo "Single topic form";
    
    //get ID from segment
    
    //if no ID: empty form
    
    //if ID: get Topic and present in form
  }
  
  //delete() - Remove one topic from the database
  function delete()
  {
    echo "Single topic delete";
    
    //get ID from segment
    //delete topic
  }
  
  //commit() - Commit the posted topic to the database
  function commit()
  {
    echo "Single topic commit";
    
    //retrieve topic from POST
    //commit topic
  }
}
?>
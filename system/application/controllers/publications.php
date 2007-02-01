<?php

class Publications extends Controller {

	function Publications()
	{
		parent::Controller();
		$header   = array();
		$content  = array();
		$footer   = array();
	}
	
	function index()
	{
	  $type = $this->uri->segment(3);
	  $id   = $this->uri->segment(4);
	  
	  switch ($type) { 
	      
	    default:
	      $this->_publicationlist();
	  }
	}

  //show() - Call single publication overview
  function show()
  {
    //retrieve publication ID
    $pub_id   = $this->uri->segment(3);
    
    if (!$pub_id)
      $pub_id = 1;
      
    //load publication
    $this->load->model('publication_model');
    
    $publication = new Publication_model;
    $publication->getByID($pub_id);
    
    //set header data
    $header ['title']       = 'Aigaion 2.0 - '.$publication->getTitle();
    $content['publication'] = $publication;
    
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('publications/single', $content, true);
    $output .= $this->load->view('footer',              '',       true);
    
    //set output
    $this->output->set_output($output);
  }
  
  //edit() - Call publication edit form. When no ID is given: new publicationform
  function edit()
  {
    echo "Single publication form";
    
    //get ID from segment
    
    //if no ID: empty form
    
    //if ID: get Publication and present in form
  }
  
  //delete() - Remove one publication from the database
  function delete()
  {
    echo "Single publication delete";
    
    //get ID from segment
    //delete publication
  }
  
  //commit() - Commit the posted publication to the database
  function commit()
  {
    echo "Single publication commit";
    
    //retrieve publication from POST
    //commit publication
  }
  
  function _publicationlist()
  {
    $this->load->model('publication_list_model');
    $publicationList = new Publication_list_model;
    $publicationList->getAll();
    $publicationList->header = "All Publications in the Database";
    
    //set header data
    $header ['title']           = 'Aigaion 2.0 - '.$publicationList->header;
    
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('publications/list',   $publicationList, true);
    $output .= $this->load->view('footer',              '',       true);
    
    //set output
    $this->output->set_output($output);  
  }  
}
?>
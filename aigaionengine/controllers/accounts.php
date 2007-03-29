<?php

class Accounts extends Controller {

	function Accounts()
	{
		parent::Controller();
	}
	
	function index()
	{
	  $this->_accountlist();
	}

  //list() - Call accountlist overview
  function _accountlist()
  {
    $headerdata = array();
    $headerdata['title'] = 'Manage Accounts';
    
    $output = $this->load->view('header', $headerdata, true);
    $output .= "Account list overview";  
    $output .= $this->load->view('footer','', true);
    
    $this->output->set_output($output);
  }
  
  //view() - Call single account overview
  function single()
  {
    echo "Single account view";
    
    //get ID from segment
    
    //load view
  }
  
  //edit() - Call account edit form. When no ID is given: new accountform
  function edit()
  {
    echo "Single account form";
    
    //get ID from segment
    
    //if no ID: empty form
    
    //if ID: get account and present in form
  }
  
  //delete() - Remove one account from the database
  function delete()
  {
    echo "Single account delete";
    
    //get ID from segment
    //delete account
  }
  
  //commit() - Commit the posted account to the database
  function commit()
  {
    echo "Single account commit";
    
    //retrieve account from POST
    //commit account
  }
}
?>
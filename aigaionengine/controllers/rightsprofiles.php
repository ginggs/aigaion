<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Rightsprofiles extends Controller {

	function Rightsprofiles()
	{
		parent::Controller();	
	}
	
	/** No default controller */
	function index()
	{
		redirect('');
	}

    /** 
    rightsprofiles/view
    
    Entry point for viewing one rightsprofile.
    
	Fails with error message when one of:
	    a non-existing rightsprofile rightsprofile_id requested
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: rightsprofile_id, the id of the rightsprofile to be viewed
	         
    Returns:
        A full HTML page with all information about the rightsprofile
    */
    function view()	{
	    $rightsprofile_id = $this->uri->segment(3,-1);
	    $rightsprofile = $this->rightsprofile_db->getByID($rightsprofile_id);
	    if ($rightsprofile==null) {
	        appendErrorMessage("View rightsprofile: non-existing id passed");
	        redirect('');
	    }
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = 'Rightsprofile';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('rightsprofiles/full',
                                      array('rightsprofile'   => $rightsprofile),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}
	
	
    /** 
    rightsprofiles/add
    
    Entry point for adding a rightsprofile.
    
	Fails with error message when one of:
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    none
	         
    Returns:
        A full HTML page with an 'add rightsprofile' form
    */
    function add()
	{
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = 'Rightsprofile';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('rightsprofiles/edit',
                                      array(),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}
	
    /** 
    rightsprofiles/edit
    
    Entry point for editing a rightsprofile.
    
	Fails with error message when one of:
	    non-existing rightsprofile id requested
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: rightsprofile_id, the id of the rightsprofile to be edited
	         
    Returns:
        A full HTML page with an 'edit rightsprofile' form
    */
    function edit()
	{
	    $rightsprofile_id = $this->uri->segment(3,-1);
	    $rightsprofile = $this->rightsprofile_db->getByID($rightsprofile_id);
	    if ($rightsprofile==null) {
	        appendErrorMessage("Edit rightsprofile: non-existing id passed");
	        redirect('');
	    }
	    
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = 'Rightsprofile';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('rightsprofiles/edit',
                                      array('rightsprofile'=>$rightsprofile),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}

	/** 
	rightsprofiles/delete
	
	Entry point for deleting a rightsprofile.
	Depending on whether 'commit' is specified in the url, confirmation may be requested before actually
	deleting. 
	
	Fails with error message when one of:
	    delete requested for non-existing rightsprofile
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: rightsprofile_id, the id of the to-be-deleted-rightsprofile
	    4th: if the 4th segment is the string 'commit', no confirmation is requested.
	         if not, a confirmation form is shown; upon choosing 'confirm' this same controller will be 
	         called with 'commit' specified
	         
    Returns:
        A full HTML page showing a 'request confirmation' form for the delete action, if no 'commit' was specified
        Redirects somewhere (?) after deleting, if 'commit' was specified
	*/
	function delete()
	{
	    $rightsprofile_id = $this->uri->segment(3,-1);
	    $rightsprofile = $this->rightsprofile_db->getByID($rightsprofile_id);
	    $commit = $this->uri->segment(4,'');

	    if ($rightsprofile==null) {
	        appendErrorMessage('Delete rightsprofile: non existing id specified.<br>\n');
	        redirect('');
	    }

        if ($commit=='commit') {
            //do delete, redirect somewhere
            appendErrorMessage('Delete rightsprofile: not implemented yet');
            redirect('');
        } else {
            //get output
            $headerdata = array();
            $headerdata['title'] = 'Rightsprofile';
            $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('rightsprofiles/delete',
                                          array('rightsprofile'=>$rightsprofile),  
                                          true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);
        }
    }
    
    /**
    rightsprofiles/commit
    
	Fails with error message when one of:
	    edit-commit requested for non-existing rightsprofile
	    insufficient user rights
	    
	Parameters passed via POST:
	    action = (add|edit)
	    rightsprofile_id
	    name
	    a list of set right names for all checked rights
	         
    Redirects to somewhere (?) if the commit was successfull
    Redirects to the edit or add form if the validation of the form values failed
    */
    function commit() {
        //get data from POST
        
        //check if fail needed
        
        //validate form values; 
        
        //return to add/edit form if validation failed
        
        //redirect somewhere if commit was successfull
        
        appendMessage("COMMIT RIGHTSPROFILE FORM: not implemented yet");
        redirect('');
    }
}
?>
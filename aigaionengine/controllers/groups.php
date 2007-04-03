<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Groups extends Controller {

	function Groups()
	{
		parent::Controller();	
	}
	
	/** no default controller */
	function index()
	{
		redirect('');
	}


    
    /** 
    groups/view
    
    Entry point for viewing one group.
    
	Fails with error message when one of:
	    a non-existing group_id requested
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: group_id, the id of the group to be viewed
	         
    Returns:
        A full HTML page with all information about the group
    */
    function view()	{
	    $group_id = $this->uri->segment(3,-1);
	    $group = $this->group_db->getByID($group_id);
	    if ($group==null) {
	        appendErrorMessage("View group: non-existing group_id passed");
	        redirect('');
	    }
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = 'Group';
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('groups/full',
                                      array('group'   => $group),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}
	
	
    /** 
    groups/add
    
    Entry point for adding a group.
    
	Fails with error message when one of:
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    none
	         
    Returns:
        A full HTML page with an 'add group' form
    */
    function add()
	{
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = 'Group';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('groups/edit',
                                      array(),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}
	
    /** 
    groups/edit
    
    Entry point for editing a group.
    
	Fails with error message when one of:
	    non-existing group_id requested
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: group_id, the id of the group to be edited
	         
    Returns:
        A full HTML page with an 'edit group' form
    */
    function edit()
	{
	    $group_id = $this->uri->segment(3,-1);
	    $group = $this->group_db->getByID($group_id);
	    if ($group==null) {
	        appendErrorMessage("Edit group: non-existing group_id passed");
	        redirect('');
	    }
	    
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = 'Group';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('groups/edit',
                                      array('group'=>$group),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}

	/** 
	groups/delete
	
	Entry point for deleting a group.
	Depending on whether 'commit' is specified in the url, confirmation may be requested before actually
	deleting. 
	
	Fails with error message when one of:
	    delete requested for non-existing group
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: group_id, the id of the to-be-deleted-group
	    4th: if the 4th segment is the string 'commit', no confirmation is requested.
	         if not, a confirmation form is shown; upon choosing 'confirm' this same controller will be 
	         called with 'commit' specified
	         
    Returns:
        A full HTML page showing a 'request confirmation' form for the delete action, if no 'commit' was specified
        Redirects somewhere (?) after deleting, if 'commit' was specified
	*/
	function delete()
	{
	    $group_id = $this->uri->segment(3);
	    $group = $this->group_db->getByID($group_id);
	    $commit = $this->uri->segment(4,'');

	    if ($group==null) {
	        appendErrorMessage('Delete group: non existing group specified.<br>\n');
	        redirect('');
	    }

        if ($commit=='commit') {
            //do delete, redirect somewhere
            appendErrorMessage('Delete group: not implemented yet');
            redirect('');
        } else {
            //get output
            $headerdata = array();
            $headerdata['title'] = 'User';
            $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('groups/delete',
                                          array('group'=>$group),  
                                          true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);
        }
    }
    function commit() {
        appendMessage("COMMIT GROUP FORM: not implemented yet");
        redirect('');
    }
}
?>
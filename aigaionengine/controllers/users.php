<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Users extends Controller {

	function Users()
	{
		parent::Controller();	
	}
	
	/** Pass control to the users/change/(logged user) controller */
	function index()
	{
		redirect('users/change/'.getUserLogin()->userId());
	}

    /** 
    users/manage
    
    Entry point for managing user accounts.
    
	Fails with error message when one of:
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    none
	         
    Returns:
        A full HTML page with all a list of all users and groups
    */
    function manage() {
        //get output
        $headerdata = array();
        $headerdata['title'] = 'User';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= "
            <p class='header1'>Users</p>
            <ul>
            ";
        $users = $this->user_db->getAllUsers();
        
        foreach ($users as $user) {
            $output .= "<li>".$this->load->view('users/summary',
                                          array('user'   => $user),  
                                          true)."</li>";
        }
        $output .= "</ul>\n".anchor('users/add','[add a new user]')."\n";

        $output .= "
            <p class='header1'>Groups</p>
            <ul>
            ";
        $groups = $this->group_db->getAllGroups();
        
        foreach ($groups as $group) {
            $output .= "<li>".$this->load->view('groups/summary',
                                          array('group'   => $group),  
                                          true)."</li>";
        }
        $output .= "</ul>\n".anchor('groups/add','[add a new group]')."\n";


        $output .= "
            <p class='header1'>Rights profiles</p>
            <ul>
            ";
//        $rightsprofiles = $this->rightsprofile_db->getAllRightsprofiles();
//        
//        foreach ($rightsprofiles as $rightsprofile) {
//            $output .= "<li>".$this->load->view('rightsprofiles/summary',
//                                          array('rightsprofile'   => $rightsprofile),  
//                                          true)."</li>";
//        }
        $output .= "</ul>\n".anchor('rightsprofiles/add','[add a new rightsprofile]')."\n";

        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);        
    }
    
    /** 
    users/view
    
    Entry point for viewing one user account.
    
	Fails with error message when one of:
	    a non-existing user_id requested
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: user_id, the id of the user to be viewed
	         
    Returns:
        A full HTML page with all information about the user
    */
    function view()	{
	    $user_id = $this->uri->segment(3,-1);
	    $user = $this->user_db->getByID($user_id);
	    if ($user==null) {
	        appendErrorMessage("View user: non-existing user_id passed");
	        redirect('');
	    }
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = 'User';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('users/full',
                                      array('user'   => $user),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}
	
	
    /** 
    users/add
    
    Entry point for adding a user account.
    
	Fails with error message when one of:
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    none
	         
    Returns:
        A full HTML page with an 'add user' form
    */
    function add()
	{
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = 'User';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('users/edit',
                                      array(),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}
	
    /** 
    users/edit
    
    Entry point for editing a user account.
    
	Fails with error message when one of:
	    non-existing user_id requested
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: user_id, the id of the user to be edited
	         
    Returns:
        A full HTML page with an 'edit user' form
    */
    function edit()
	{
	    $user_id = $this->uri->segment(3,-1);
	    $user = $this->user_db->getByID($user_id);
	    if ($user==null) {
	        appendErrorMessage("Edit user: non-existing user_id passed");
	        redirect('');
	    }
	    
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = 'User';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('users/edit',
                                      array('user'=>$user),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}

	/** 
	users/delete
	
	Entry point for deleting a user.
	Depending on whether 'commit' is specified in the url, confirmation may be requested before actually
	deleting. 
	
	Fails with error message when one of:
	    delete requested for non-existing user
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: user_id, the id of the to-be-deleted-user
	    4th: if the 4th segment is the string 'commit', no confirmation is requested.
	         if not, a confirmation form is shown; upon choosing 'confirm' this same controller will be 
	         called with 'commit' specified
	         
    Returns:
        A full HTML page showing a 'request confirmation' form for the delete action, if no 'commit' was specified
        Redirects somewhere (?) after deleting, if 'commit' was specified
	*/
	function delete()
	{
	    $user_id = $this->uri->segment(3);
	    $user = $this->user_db->getByID($user_id);
	    $commit = $this->uri->segment(4,'');

	    if ($user==null) {
	        appendErrorMessage('Delete user: non existing user specified.<br>\n');
	        redirect('');
	    }

        if ($commit=='commit') {
            //do delete, redirect somewhere
            appendErrorMessage('Delete user: not implemented yet');
            redirect('');
        } else {
            //get output
            $headerdata = array();
            $headerdata['title'] = 'User';
            $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('users/delete',
                                          array('user'=>$user),  
                                          true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);
        }
    }
    function commit() {
        appendMessage("COMMIT USER FORM: not implemented yet");
        redirect('');
    }
}
?>
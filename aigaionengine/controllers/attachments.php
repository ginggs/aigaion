<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Attachments extends Controller {

	function Attachments()
	{
		parent::Controller();	
	}
	
	/** There is no default controller for attachments. */
	function index()
	{
		redirect('');
	}

    /** 
    attachments/view
    
    Entry point for viewing (i.e. downloading) one attachment.
    
	Fails with error message when one of:
	    a non-existing attachment requested
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: att_id, the id of the attachment to be downloaded
	         
    Returns:
        The attachment file in proper format
    */
	function view() {
	    $att_id = $this->uri->segment(3);
	    $attachment = $this->attachment_db->getByID($att_id);
	    
	    if ($attachment==null) {
	        appendErrorMessage('Download attachment: Attachment does not exist.<br>');
	        redirect('');
	    }
	    
        $output = $this->load->view('attachments/download',
                              array('attachment'   => $attachment
                                    ),  
                              true);
                              
        //set output
        $this->output->set_output($output);
	}

	/** 
	attachments/delete
	
	Entry point for deleting an attachment.
	Depending on whether 'commit' is specified in the url, confirmation may be requested before actually
	deleting. 
	
	Fails with error message when one of:
	    delete requested for non-existing attachment
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: att_id, the id of the to-be-deleted-attachment
	    4th: if the 4th segment is the string 'commit', no confirmation is requested.
	         if not, a confirmation form is shown; upon choosing 'confirm' this same controller will be 
	         called with 'commit' specified
	         
    Returns:
        A full HTML page showing a 'request confirmation' form for the delete action, if no 'commit' was specified
        Redirects somewhere (?) after deleting, if 'commit' was specified
	*/
	function delete()
	{
	    $att_id = $this->uri->segment(3);
	    $attachment = $this->attachment_db->getByID($att_id);
	    $commit = $this->uri->segment(4,'');

	    if ($attachment==null) {
	        appendErrorMessage('Delete attachment: attachment does not exist.<br>');
	        redirect('');
	    }

	    //besides the rights needed to READ this attachment, checked by attachment_db->getByID, we need to check:
	    //edit_access_level and the user edit rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('attachment_edit'))
             || 
                ($userlogin->isAnonymous() && ($attachment->edit_access_level!='public'))
             ||
                (    ($attachment->edit_access_level == 'private') 
                  && ($userlogin->userId() != $attachment->user_id) 
                  && (!$userlogin->hasRights('attachment_edit_all'))
                 )                
            ) 
        {
	        appendErrorMessage('Delete attachment: insufficient rights.<br>');
	        redirect('');
        }

        if ($commit=='commit') {
            //do delete, redirect somewhere
            appendErrorMessage('Delete attachment: not implemented yet');
            redirect('');
        } else {
            //get output: a full web page with a 'confirm delete' form
            $headerdata = array();
            $headerdata['title'] = 'Attachment: delete';
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('attachments/delete',
                                         array('attachment'=>$attachment),  
                                         true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);	
        }
    }


	/** 
	attachments/add
	
	Entry point for adding an attachment.
	Shows the form needed for uploading.
	The actual upload is processed in the 'attachments/commit' controller.
	
	Fails with error message when one of:
	    add attachment requested for non-existing publication
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: pub_id, the id of the publication to which the attachment will be added
	         
    Returns:
        A full HTML page showing an 'upload attachment' form for the given publication
	*/
	function add() {
	    $pub_id = $this->uri->segment(3);
        
        $this->load->model('publication_model');
        $publication = new Publication_model;
        $publication->loadByID($pub_id);
        
        if ($publication == null) {
            echo "<div class='errormessage'>Add atachment: no valid publication ID provided</div>";
        }


	    //besides the rights needed to READ this attachment, checked by attachment_db->getByID, we need to check:
	    //edit_access_level and the user edit rights
	    //in this case it's mostly the rights on the publication that determine access
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('attachment_edit'))
             || 
                ($userlogin->isAnonymous() && ($publication->edit_access_level!='public'))
             ||
                (    ($publication->edit_access_level == 'private') 
                  && ($userlogin->userId() != $publication->user_id) 
                  && (!$userlogin->hasRights('publication_edit_all'))
                 )                
            ) 
        {
	        appendErrorMessage('Add attachment: insufficient rights.<br>');
	        redirect('');
        }

        //get output: a full web page with a 'confirm delete' form
        $headerdata = array();
        $headerdata['title'] = 'Attachment: delete';
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('attachments/add',
                                     array('publication'=>$publication),  
                                     true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);	        
    }
    
    /** 
    attachments/edit
    
    Entry point for editing an attachment.
    
	Fails with error message when one of:
	    non-existing att_id requested
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: att_id, the id of the attachment to be edited
	         
    Returns:
        A full HTML page with an 'edit attachment' form
    */
    function edit()
	{
	    $att_id = $this->uri->segment(3,-1);
	    $attachment = $this->attachment_db->getByID($att_id);
	    if ($attachment==null) {
	        appendErrorMessage("Edit attachment: non-existing att_id passed");
	        redirect('');
	    }
	    
	    //besides the rights needed to READ this attachment, checked by attachment_db->getByID, we need to check:
	    //edit_access_level and the user edit rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('attachment_edit'))
             || 
                ($userlogin->isAnonymous() && ($attachment->edit_access_level!='public'))
             ||
                (    ($attachment->edit_access_level == 'private') 
                  && ($userlogin->userId() != $attachment->user_id) 
                  && (!$userlogin->hasRights('attachment_edit_all'))
                 )                
            ) 
        {
	        appendErrorMessage('Edit attachment: insufficient rights.<br>');
	        redirect('');
        }
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = 'Attachment';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output .= $this->load->view('attachments/edit',
                                      array('attachment'=>$attachment),  
                                      true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}
    
    /** 
    attachments/commit
    
    Entry point for committing an attachment (add or edit).
    
	Fails with error message when one of:
	    non-existing att_id requested
	    insufficient user rights
	    problem uploading file
	    
	Parameters passed via POST:
	    all info from the add or edit form
	    $action = (add|edit)
	         
    Returns:
        Somewhere
    */
    function commit()
	{
        //get data from POST
        $attachment = $this->attachment_db->getFromPost();

	    if ($attachment==null) {
	        appendErrorMessage("Commit attachment: no data to commit<br/>");
	        redirect('');
	    }

//             the checks on the attachment itself are of course not tested here,
//             but in the commit action, as the client can have sent 'wrong' form data        

    
        //if validation was successfull: add or change.
        $success = False;
        if ($this->input->post('action') == 'edit') {
            //do edit
            $success = $attachment->commit();
        } else {
            //do add
            $success = $attachment->add();
        }
        if (!$success) {
            //might happen, e.g. if upload fails due to post size limits, upload size limits, etc.
            //or illegal attachment extensions etc.
            appendErrorMessage("Commit attachment: an error occurred. Please contact your Aigaion administrator.<br>"); 
            redirect ('');
        }
        //redirect somewhere if commit was successfull
        redirect('');

	}
    
    /** 
    attachments/setmain
    
    Entry point for setting attachment as main.
    
	Fails with error message when one of:
	    non-existing att_id requested
	    
	Fails silently when insufficient user rights
	    
	Parameters passed via url segments:
	    3rd: $att_id 
	         
    Returns:
        redirects to publications/show
    */
    function setmain() {
	    $att_id = $this->uri->segment(3,-1);
	    $attachment = $this->attachment_db->getByID($att_id);
	    if ($attachment==null) {
	        appendErrorMessage("Edit attachment: non-existing att_id passed");
	        redirect('');
	    }
	    $attachment->ismain=true;
	    $attachment->commit();
	    redirect('publications/show/'.$attachment->pub_id);
    }
    
    /** 
    attachments/unsetmain
    
    Entry point for unsetting attachment as main.
    
	Fails with error message when one of:
	    non-existing att_id requested

	Fails silently when insufficient user rights
	    
	Parameters passed via url segments:
	    3rd: $att_id 
	         
    Returns:
        redirects to publications/show
    */
    function unsetmain() {
	    $att_id = $this->uri->segment(3,-1);
	    $attachment = $this->attachment_db->getByID($att_id);
	    if ($attachment==null) {
	        appendErrorMessage("Edit attachment: non-existing att_id passed");
	        redirect('');
	    }
	    $attachment->ismain=false;
	    $attachment->commit();
	    redirect('publications/show/'.$attachment->pub_id);
    }
}


?>
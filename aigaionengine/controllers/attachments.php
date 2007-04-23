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
	        appendErrorMessage('Delete attachment: attachment does not exist.<br>\n');
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
    
}


?>
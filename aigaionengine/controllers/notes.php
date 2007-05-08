<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Notes extends Controller {

	function Notes()
	{
		parent::Controller();	
	}
	
	/** no default */
	function index()
	{
		redirect('');
	}


	/** 
	notes/delete
	
	Entry point for deleting a note.
	Depending on whether 'commit' is specified in the url, confirmation may be requested before actually
	deleting. 
	
	Fails with error message when one of:
	    delete requested for non-existing note
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: topic_id, the id of the to-be-deleted-note
	    4th: if the 4th segment is the string 'commit', no confirmation is requested.
	         if not, a confirmation form is shown; upon choosing 'confirm' this same controller will be 
	         called with 'commit' specified
	         
    Returns:
        A full HTML page showing a 'request confirmation' form for the delete action, if no 'commit' was specified
        Redirects somewhere (?) after deleting, if 'commit' was specified
	*/
	function delete()
	{
	    $note_id = $this->uri->segment(3);
	    $note = $this->note_db->getByID($note_id);
	    $commit = $this->uri->segment(4,'');

	    if ($note==null) {
	        appendErrorMessage('Delete note: non existing note specified.<br>');
	        redirect('');
	    }

        if ($commit=='commit') {
            //do delete, redirect somewhere
            appendErrorMessage('Delete note: not implemented yet');
            redirect('');
        } else {
            //get output
            $headerdata = array();
            $headerdata['title'] = 'Delete note';
            $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('notes/delete',
                                          array('note'=>$note),  
                                          true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);
        }
    }
    
	/** Entrypoint for adding a note. Shows the necessary form. 3rd segment is pub_id */
	function add()
	{
	    $pub_id = $this->uri->segment(3);
        
        $publication = $this->publication_db->getByID($pub_id);
        
        if ($publication == null) {
            echo "<div class='errormessage'>Add note: no valid publication ID provided</div>";
        }

        $this->load->library('validation');
        $this->validation->set_error_delimiters('<div class="errormessage">Changes not committed: ', '</div>');

        //get output
        $headerdata = array();
        $headerdata['title'] = 'Add note';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output  .= $this->load->view('notes/edit' , array('pub_id' => $pub_id),  true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
    }
    
	/** Entrypoint for editing a note. Shows the necessary form. */
	function edit()
	{
        $this->load->library('validation');
        $this->validation->set_error_delimiters('<div class="errormessage">Changes not committed: ', '</div>');

	    $note_id = $this->uri->segment(3,1);
        $note = $this->note_db->getByID($note_id);

	    if ($note==null) {
	        appendErrorMessage('Note does not exist.<br>');
	        redirect('');
	    }
        	    
        //get output
        $headerdata = array();
        $headerdata['title'] = 'Edit note';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output  .= $this->load->view('notes/edit' , array('note'=>$note),  true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);

    }
    
    /**
    notes/commit
    
	Fails with error message when one of:
	    edit-commit requested for non-existing note
	    insufficient user rights
	    
	Parameters passed via POST:
	    action = (add|edit)
	         
    Redirects to somewhere (?) if the commit was successfull
    Redirects to the edit or add form if the validation of the form values failed
    */
    function commit() {
        $this->load->library('validation');
        $this->validation->set_error_delimiters('<div class="errormessage">Changes not committed: ', '</div>');

        //get data from POST
        $note = $this->note_db->getFromPost();
        
        //check if fail needed: was all data present in POST?
        if ($note == null) {
            appendErrorMEssage("Commit note: no data to commit<br/>");
            redirect ('');
        }
        
        //validate form values; 
        //validation rules: 
    	$this->validation->set_rules(array( 'pub_id' => 'required'
                                           )
                                     );
    	$this->validation->set_fields(array( 'pub_id' => 'Publication id'
                                           )
                                     );
    		
    	if ($this->validation->run() == FALSE) {
            //return to add/edit form if validation failed
            //get output
            $headerdata = array();
            $headerdata['title'] = 'Note';
            $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('notes/edit',
                                          array('note'         => $note,
                                                'action'        => $this->input->post('action')),
                                          true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);
            
        } else {    
            //if validation was successfull: add or change.
            $success = False;
            if ($this->input->post('action') == 'edit') {
                //do edit
                $success = $note->commit();
            } else {
                //do add
                $success = $note->add();
            }
            if (!$success) {
                //this is quite unexpected, I think this should not happen if we have no bugs.
                appendErrorMessage("Commit note: an error occurred. Please contact your Aigaion administrator.<br>");
                redirect ('');
            }
            //redirect somewhere if commit was successfull
            redirect('');

        }
        
    }
    
}
?>
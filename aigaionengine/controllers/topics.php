<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Topics extends Controller {

	function Topics()
	{
		parent::Controller();	
	}
	
	/** Pass control to the topics/browse/ controller */
	function index()
	{
		redirect('topics/browse');
	}

    /** Simple browse page for Topics. 
        This controller returns a full web page.
        Third parameter selects root topic_id for tree (default:1) */
	function browse()
	{
	    $root_id = $this->uri->segment(3,1);
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = 'Topic';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        
        
        $root = $this->topic_db->getByID($root_id);
        $this->load->vars(array('subviews'  => array('topics//maintreerow'=>array())));
        $output .= "<div id='topictree-holder'>\n<ul class='topictree-list'>\n"
                    .$this->load->view('topics/tree',
                                      array('topics'   => $root->getChildren(),
                                            'showroot'  => True,
                                            'depth'     => -1
                                            ),  
                                      true)."</ul>\n</div>\n";
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}


	/** Entrypoint for adding a topic. Shows the necessary form. */
	function add()
	{
        $this->load->library('validation');
        $this->validation->set_error_delimiters('<div class="errormessage">', '</div>');

        //get output
        $headerdata = array();
        $headerdata['title'] = 'Topic';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output  .= $this->load->view('topics/edit' , array(),  true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
    }
    
	/** Entrypoint for editing a category. Shows the necessary form. */
	function edit()
	{
        $this->load->library('validation');
        $this->validation->set_error_delimiters('<div class="errormessage">', '</div>');

	    $topic_id = $this->uri->segment(3,1);
	    if ($topic_id==1) {
	        redirect('topics/browse');
	    }
        $topic = $this->topic_db->getByID($topic_id);

	    if ($topic==null) {
	        appendErrorMessage('Topic does not exist.<br>');
	        redirect('');
	    }
        	    
        //get output
        $headerdata = array();
        $headerdata['title'] = 'Topic';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output  .= $this->load->view('topics/edit' , array('topic'=>$topic),  true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);

    }
    
    /** Simple view page for single topic. 
        This controller returns a full web page.
        Third parameter selects topic_id (default:1)
        If topic 1 is chosen, user is redirected to browse/ controller */
	function view()
	{
	    $topic_id = $this->uri->segment(3,1);
	    if ($topic_id==1) {
	        redirect('topics/browse');
	    }
        $topic = $this->topic_db->getByID($topic_id);

	    if ($topic==null) {
	        appendErrorMessage('Topic does not exist.<br>');
	        redirect('topics/browse');
	    }
	    
        //get output
        $headerdata = array();
        $headerdata['title'] = 'Topic';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $output  .= $this->load->view('topics/full', array('topic' => $topic),  true);
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
	}    
    
    
    /**
    topics/commit
    
	Fails with error message when one of:
	    edit-commit requested for non-existing topic
	    insufficient user rights
	    
	Parameters passed via POST:
	    action = (add|edit)
	    topic_id
	    name
	    description
	    url
	    parent_id
	         
    Redirects to somewhere (?) if the commit was successfull
    Redirects to the edit or add form if the validation of the form values failed
    */
    function commit() {
        $this->load->library('validation');
        $this->validation->set_error_delimiters('<div class="errormessage">', '</div>');

        //get data from POST
        $topic = $this->topic_db->getFromPost();
        
        //check if fail needed: was all data present in POST?
        if ($topic == null) {
            appendErrorMEssage("Commit topic: no data to commit<br/>");
            redirect ('');
        }
        
        //validate form values; 
        //validation rules: 
        //  -no topic with the same name and a different ID can exist
        //  -name is required (non-empty)
    	$this->validation->set_rules(array( 'name' => 'required'
                                           )
                                     );
    	$this->validation->set_fields(array( 'name' => 'Topic Name'
                                           )
                                     );
    		
    	if ($this->validation->run() == FALSE) {
            //return to add/edit form if validation failed
            //get output
            $headerdata = array();
            $headerdata['title'] = 'Topic';
            $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('topics/edit',
                                          array('topic'         => $topic,
                                                'action'        => $this->input->post('action')),
                                          true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);
            
        } else {    
            //if validation was successfull: add or change.
            
            //redirect somewhere if commit was successfull
            appendMessage("
                COMMIT TOPIC FORM: not implemented yet.<br>
                The to-be-committed topic contains the following values: 
                <ul>
                <li>Action: ".$this->input->post('action')."
                <li>Topic id: ".$topic->topic_id."
                <li>Topic name: ".$topic->name."
                <li>Topic description: ".$topic->description."
                <li>Topic url: ".$topic->url."
                <li>Topic parent_id: ".$topic->parent_id."
                </ul>
                ");
            
            redirect('');
        }
        
    }
}
?>
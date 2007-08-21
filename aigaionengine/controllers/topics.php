<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Topics extends Controller {

	function Topics()
	{
		parent::Controller();	
	}
	
	/** Pass control to the topics/browse/ controller */
	function index()
	{
	  $this->browse();
	}

    /** Simple browse page for Topics. 
        This controller returns a full web page.
        Third parameter selects root topic_id for tree (default:1) */
	function browse()
	{
	    $root_id = $this->uri->segment(3,1);
	    
	    //no rights check here: anyone can (try) to browse topics (though not all topics may be visible)
        //get output
        $headerdata = array();
        $headerdata['title'] = 'Browse topic tree';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);
        
        $userlogin = getUserLogin();
        $user = $this->user_db->getByID($userlogin->userId());
        $config = array('onlyIfUserSubscribed'=>True,
                         'flagCollapsed'=>True,
                         'user'=>$user,
                         'includeGroupSubscriptions'=>True
                        );
        $root = $this->topic_db->getByID($root_id, $config);
        if ($root == null) {
            appendErrorMessage( "Browse topics: no valid topic ID provided<br/>");
            redirect('');
        }
        $output .= "<div style='border:1px solid black;padding:0.2em;float:right;'>"
                    .$this->load->view('site/stats',
                                      array(),  
                                      true)."</div>\n";
        $this->load->vars(array('subviews'  => array('topics/maintreerow'=>array('useCollapseCallback'=>True))));
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

	/** 
	topics/delete
	
	Entry point for deleting a topic.
	Depending on whether 'commit' is specified in the url, confirmation may be requested before actually
	deleting. 
	
	Fails with error message when one of:
	    delete requested for non-existing topic
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: topic_id, the id of the to-be-deleted-topic
	    4th: if the 4th segment is the string 'commit', no confirmation is requested.
	         if not, a confirmation form is shown; upon choosing 'confirm' this same controller will be 
	         called with 'commit' specified
	         
    Returns:
        A full HTML page showing a 'request confirmation' form for the delete action, if no 'commit' was specified
        Redirects somewhere (?) after deleting, if 'commit' was specified
	*/
	function delete()
	{
	    $topic_id = $this->uri->segment(3);
        $config=array();
	    $topic = $this->topic_db->getByID($topic_id,$config);
	    $commit = $this->uri->segment(4,'');

	    if ($topic==null) {
	        appendErrorMessage('Delete topic: non existing topic specified.<br/>');
	        redirect('');
	    }

	    //besides the rights needed to READ this topic, checked by topic_db->getByID, we need to check:
	    //edit_access_level and the user edit rights
            $userlogin  = getUserLogin();
            $user       = $this->user_db->getByID($userlogin->userID());
            if (    (!$userlogin->hasRights('topic_edit'))
             || 
                !$this->accesslevels_lib->canEditObject($topic)
            ) 
        {
	        appendErrorMessage('Delete topic: insufficient rights.<br/>');
	        redirect('');
        }

        if ($commit=='commit') {
            //do delete, redirect somewhere
            $topic->delete();
            redirect('');
        } else {
            //get output
            $headerdata = array();
            $headerdata['title'] = 'Delete topic';
            $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('topics/delete',
                                          array('topic'=>$topic),  
                                          true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);
        }
    }
    
	/** Entrypoint for adding a topic. Shows the necessary form. */
	function add()
	{
        $this->load->library('validation');
        $this->validation->set_error_delimiters('<div class="errormessage">Changes not committed: ', '</div>');
        $parent_id = $this->uri->segment(3,-1);
        $config = array();
        $parent = $this->topic_db->getByID($parent_id,$config);
	    //edit_access_level and the user edit rights
        $userlogin = getUserLogin();
        if (    (!$userlogin->hasRights('topic_edit'))
            ) 
        {
	        appendErrorMessage('Add topic: insufficient rights.<br/>');
	        redirect('');
        }
        
        //get output
        $headerdata = array();
        $headerdata['title'] = 'Add topic';
        $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output  = $this->load->view('header', $headerdata, true);
        $output .= $this->load->view('topics/edit' , array('parent'=>$parent),  true);
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);
    }
    
	/** Entrypoint for editing a category. Shows the necessary form. */
	function edit()
	{
        $this->load->library('validation');
        $this->validation->set_error_delimiters('<div class="errormessage">Changes not committed: ', '</div>');

	    $topic_id = $this->uri->segment(3,1);
	    if ($topic_id==1) {
	        redirect('topics/browse');
	    }
        $config=array();
      $topic = $this->topic_db->getByID($topic_id,$config);

	    if ($topic==null) {
	        appendErrorMessage('Topic does not exist.<br/>');
	        redirect('');
	    }
        	    

	    //besides the rights needed to READ this topic, checked by topic_db->getByID, we need to check:
	    //edit_access_level and the user edit rights
        $userlogin  = getUserLogin();
        $user       = $this->user_db->getByID($userlogin->userID());
    
        if (    (!$userlogin->hasRights('topic_edit'))
             || 
                !$this->accesslevels_lib->canEditObject($topic)
            ) 
        {
	        appendErrorMessage('Edit topic: insufficient rights.<br/>');
	        redirect('');
        }

        //get output
        $headerdata = array();
        $headerdata['title'] = 'Edit topic';
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
	function single()
	{
	    $topic_id = $this->uri->segment(3,1);
        $page   = $this->uri->segment(4,0);
	    if ($topic_id==1) {
	        redirect('topics/browse');
	    }
        $config=array();
        $topic = $this->topic_db->getByID($topic_id,$config);
        $userlogin=getUserLogin();
	    if ($topic==null) {
	        appendErrorMessage('Topic does not exist.<br/>');
	        redirect('topics/browse');
	    }
	    
	    //no additional rights check beyond those in the topic_db->getbyID, as anyone can view topics as long
	    // as he has the right access levels
	    
	      $this->load->helper('publication');
    
        //get output
        $headerdata                 = array();
        $headerdata['title']        = 'View topic';
        $headerdata['javascripts']  = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $content['topic']           = $topic;
        $content['header']          = "Publications for topic: ".$topic->name;
        if ($userlogin->getPreference('liststyle')>0) {
            //set these parameters when you want to get a good multipublication list display
            $content['multipage']       = True;
            $content['resultcount']     = $this->publication_db->getCountForTopic($topic_id);
            $content['currentpage']     = $page;
            $content['multipageprefix'] = 'topics/single/'.$topic_id.'/';
        }
        $content['publications']    = $this->publication_db->getForTopic($topic_id,$page);
        
        $output = $this->load->view('header', $headerdata, true);

        $output  .= $this->load->view('topics/full', $content,  true);
        
        
        
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
        $this->validation->set_error_delimiters('<div class="errormessage">Changes not committed: ', '</div>');

        //get data from POST
        $topic = $this->topic_db->getFromPost();
        
        //check if fail needed: was all data present in POST?
        if ($topic == null) {
            appendErrorMessage("Commit topic: no data to commit<br/>");
            redirect ('');
        }

//             the access level checks are of course not tested here,
//             but in the commit action, as the client can have sent 'wrong' form data        
        
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
            $success = False;
            if ($this->input->post('action') == 'edit') {
                //do edit
                $success = $topic->update();
            } else {
                //do add
                $success = $topic->add();
            }
            if (!$success) {
                //this is quite unexpected, I think this should not happen if we have no bugs.
                appendErrorMessage("Commit topic: an error occurred. Please contact your Aigaion administrator.<br/>");
                redirect('topics/single/'.$topic->topic_id);
            }
            //redirect somewhere if commit was successfull
            redirect('topics/single/'.$topic->topic_id);

        }
        
    }
    
    /**
    topics/collapse
    
    Collapses or expands a topic for the logged user. Is normally called async, without processing the
    returned partial, by clicking one of the collapse or expand buttons in a topic tree rendered by 
    subview 'maintreerow' with argument 'useCollapseCallback'=>True
    
	Fails with error message when one of:
	    collapse requested for non-existing topic
	    insufficient user rights
	    
	Parameters passed via URL:
	    3rd segment: topic_id
	    4rd segment: collapse status (0|1), default 1 (collapsed)
	         
    Returns a partial html fragment:
        an empty div if successful
        an div containing an error message, otherwise
    
    */
    function collapse() {    
        $topic_id = $this->uri->segment(3,-1);
        $collapse = $this->uri->segment(4,1);
        
        $config=array();
        $topic = $this->topic_db->getByID($topic_id,$config);
        
        if ($topic == null) {
            echo "<div class='errormessage'>Collapse topic: no valid topic ID provided</div>";
        } else {
            //do collapse
            if ($collapse==1) {
                $topic->collapse();
            } else {
                $topic->expand();
            }
    
            echo "<div/>";
        }
    }
}
?>
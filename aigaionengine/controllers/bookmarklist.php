<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Bookmarklist extends Controller {

	function Bookmarklist()
	{
		parent::Controller();	
	}
	
	/** Pass control to the bookmarklist/view/ */
	function index()
	{
		redirect('bookmarklist/view');
	}

    /** 
    bookmarklist/view
    
    Entry point for viewing the bookmark list of the logged user.
    
	Fails with error message when one of:
	    insufficient rights
	    
	Parameters passed via URL segments:
	    none
	         
    Returns:
        A full HTML page with the list of bookmarked publications
    */
    function view() {
	    //get URL segments: none
	    
	    //check rights
      $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist')) {
            appendErrorMessage("View bookmarklist: insufficient rights<br>");
            redirect('');
        }
	            	    
	    //get output
        $this->load->helper('publication');

        $headerdata = array();
        $headerdata['title'] = 'Bookmark list';
        $headerdata ['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $output = $this->load->view('header', $headerdata, true);

        $content['header']          = 'Bookmarklist of '.$userlogin->loginName();
        $content['publications']    = $this->publication_db->getForBookmarkList();
        
        
        $output .= $this->load->view('bookmarklist/controls', array(), True);
        $output .= $this->load->view('publications/list', $content, true);

        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);        

    }    

    /** 
    bookmarklist/addpublication
    
    Entry point for adding a publication to the bookmark list of the logged user.
    
	Fails with error message when one of:
	    adding nonexisting pub_id 
	    insufficient rights
	    
	Parameters passed via URL segments:
	    3rd: pub_id
	         
    Returns:
        A partial DIV containing the 'remove' link for that publication
    */
    function addpublication() {
        $pub_id   = $this->uri->segment(3,-1);

	    //check rights is done in the $this->bookmarklist_db->addPublication function, no need to do it twice

        //load publication
        $publication = $this->publication_db->getByID($pub_id);
        if ($publication == null)
        {
            appendErrorMessage("Add publication to bookmarklist: non-existing publication id passed");
            redirect('');
        }
        
        $this->bookmarklist_db->addPublication($publication->pub_id);
        $output = $this->ajax->link_to_remote("[UnBookmark]", 
                                     array('url' => site_url('/bookmarklist/removepublication/'.$publication->pub_id), 
                                           'update' => 'bookmark_pub_'.$publication->pub_id
                                           )
                                     );

        //set output
        $this->output->set_output($output);        
      
    }


    /** 
    bookmarklist/removepublication
    
    Entry point for removing a publication from the bookmark list of the logged user.
    
	Fails with error message when one of:
	    removing nonexisting pub_id 
	    insufficient rights
	    
	Parameters passed via URL segments:
	    3rd: pub_id
	         
    Returns:
        A partial DIV containing the 'add' link for that publication
    */
    function removepublication() {
        $pub_id   = $this->uri->segment(3,-1);

	    //check rights is done in the $this->bookmarklist_db->removePublication function, no need to do it twice

        //load publication
        $publication = $this->publication_db->getByID($pub_id);
        if ($publication == null)
        {
            appendErrorMessage("Removing publication from bookmarklist: non-existing publication id passed");
            redirect('');
        }
        
        $this->bookmarklist_db->removePublication($publication->pub_id);
        $output = $this->ajax->link_to_remote("[Bookmark]", 
                                     array('url' => site_url('/bookmarklist/addpublication/'.$publication->pub_id), 
                                           'update' => 'bookmark_pub_'.$publication->pub_id
                                           )
                                     );

        //set output
        $this->output->set_output($output);        
    }


    /** 
    bookmarklist/addtotopic
    
    Entry point for adding all publications in the bookmark list to a certain topic.
    
	Fails with error message when one of:
	    insufficient rights
	    nonexisting topic
	    
	Parameters passed via POST:
	    topic_id
	         
    Redirects to the bookmarklist/view controller
        
    */
    function addtotopic() {
	    //check rights is done in the $this->bookmarklist_db->removePublication function, no need to do it twice

	    $topic_id = $this->input->post('topic_id');
        $userlogin  = getUserLogin();
        $user       = $this->user_db->getByID($userlogin->userID());
        $config = array('onlyIfUserSubscribed'=>True,
                         'user'=>$user,
                         'includeGroupSubscriptions'=>True
                        );
        $topic = $this->topic_db->getByID($topic_id, $config);
        if ($topic == null) {
            appendErrorMessage( "Add bookmarked publications to topic: no valid topic ID provided.<br>");
            redirect('bookmarklist/view');
        } 
        $this->bookmarklist_db->addToTopic($topic);
        redirect('bookmarklist/view');
    }

    /** 
    bookmarklist/maketopic
    
    Entry point for turning all publications in the bookmark list into a new topic.
    
	Fails with error message when one of:
	    insufficient rights
	    
	Parameters passed via POST:
	    none
	         
    Redirects to the bookmarklist/edit controller for the new topic
        
    */
    function maketopic() {
      $userlogin = getUserLogin();
	    if (!$userlogin->hasRights('topic_edit')) {
	        appendErrorMessage('Insufficient rights to create topic<br>');
	        redirect('');
	    }
	    
	    $topic = new Topic;
	    $topic->name = '-new from bookmarklist-';
        if (!$topic->add()) {
	        appendErrorMessage('Error creating topic<br>');
	        redirect('');
        }
        $this->bookmarklist_db->addToTopic($topic);
        redirect('topics/edit/'.$topic->topic_id);
    }


    /** 
    bookmarklist/clear
    
    Clear bookmarklist
    
	Fails with error message when one of:
	    insufficient rights
	    
	Parameters passed via POST:
	    none
	         
    Redirects to the bookmarklist/view controller
        
    */
    function clear() {
      $userlogin = getUserLogin();
	    if (!$userlogin->hasRights('bookmarklist')) {
	        appendErrorMessage('Insufficient rights to clear bookmarklist<br>');
	        redirect('');
	    }
        $this->bookmarklist_db->clear();
        redirect('bookmarklist');
    }
}
?>
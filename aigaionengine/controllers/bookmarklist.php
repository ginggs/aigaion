<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Bookmarklist extends Controller {

	function Bookmarklist()
	{
		parent::Controller();	
	}
	
	/** Pass control to the bookmarklist/viewlist/ */
	function index()
	{
		$this->viewlist();
	}

    /** 
    bookmarklist/viewlist
    
    Entry point for viewing the bookmark list of the logged user.
    
	Fails with error message when one of:
	    insufficient rights
	    
	Parameters passed via URL segments:
	    none
	         
    Returns:
        A full HTML page with the list of bookmarked publications
    */
    function viewlist() {
	    //get URL segments: none
	    
	    //check rights
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('bookmarklist')) {
            appendErrorMessage("View bookmarklist: insufficient rights<br/>");
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
        $output = '<span title="Click to UnBookmark publication">'
                 .$this->ajax->link_to_remote("<img border=0 src='".getIconUrl('bookmarked.gif')."'>",
                  array('url'     => site_url('/bookmarklist/removepublication/'.$publication->pub_id),
                        'update'  => 'bookmark_pub_'.$publication->pub_id
                        )
                  ).'</span>';

        //set output
        $this->output->set_output($output);        
      
    }

    /** 
    bookmarklist/addtopic
    
    Entry point for adding all accessible publications from a give topic to the bookmark list of the logged user.
    
	Fails with error message when one of:
	    adding nonexisting topic_id 
	    insufficient rights
	    
	Parameters passed via URL segments:
	    3rd: topic_id
	         
    Returns:
        to the vieww page of that topic
    */
    function addtopic() {
        $topic_id   = $this->uri->segment(3,-1);

	    //check rights is done in the $this->bookmarklist_db->addTopic function, no need to do it twice

        //load topic
        $config=array();
        $topic = $this->topic_db->getByID($topic_id,$config);
        if ($topic == null)
        {
            appendErrorMessage("Add topic to bookmarklist: non-existing topic id passed");
            redirect('');
        }
        
        $this->bookmarklist_db->addTopic($topic->topic_id);
        redirect('topics/single/'.$topic->topic_id);
      
    }
    
    /** 
    bookmarklist/addauthor
    
    Entry point for adding all accessible publications from a given author to the bookmark list of the logged user.
    
	Fails with error message when one of:
	    adding nonexisting author_id 
	    insufficient rights
	    
	Parameters passed via URL segments:
	    3rd: author_id
	         
    Returns:
        to the view page of that author
    */
    function addauthor() {
        $author_id   = $this->uri->segment(3,-1);

	    //check rights is done in the $this->bookmarklist_db->addAuthor function, no need to do it twice

        //load author
        $author = $this->author_db->getByID($author_id);
        if ($author == null)
        {
            appendErrorMessage("Add author to bookmarklist: non-existing author id passed");
            redirect('');
        }
        
        $this->bookmarklist_db->addAuthor($author->author_id);
        redirect('authors/show/'.$author->author_id);
      
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
        $output = '<span title="Click to Bookmark publication">'
                 .$this->ajax->link_to_remote("<img border=0 src='".getIconUrl('nonbookmarked.gif')."'>",
                  array('url'     => site_url('/bookmarklist/addpublication/'.$publication->pub_id),
                        'update'  => 'bookmark_pub_'.$publication->pub_id
                        )
                  ).'</span>';

        //set output
        $this->output->set_output($output);        
    }
    

    /** 
    bookmarklist/removetopic
    
    Entry point for removing all accessible publications of a topic from the bookmark list of the logged user.
    
	Fails with error message when one of:
	    removing nonexisting topic_id 
	    insufficient rights
	    
	Parameters passed via URL segments:
	    3rd: topic_id
	         
    Returns:
        to the single view page of that topic
    */
    function removetopic() {
        $topic_id   = $this->uri->segment(3,-1);

	    //check rights is done in the $this->bookmarklist_db->removeTopic function, no need to do it twice

        //load topic
        $config=array();
        $topic = $this->topic_db->getByID($topic_id,$config);
        if ($topic == null)
        {
            appendErrorMessage("Removing topic from bookmarklist: non-existing topic id passed");
            redirect('');
        }
        
        $this->bookmarklist_db->removeTopic($topic->topic_id);
        redirect('topics/single/'.$topic->topic_id);      
    }

    /** 
    bookmarklist/removeauthor
    
    Entry point for removing all accessible publications of an author from the bookmark list of the logged user.
    
	Fails with error message when one of:
	    removing nonexisting author_id 
	    insufficient rights
	    
	Parameters passed via URL segments:
	    3rd: author_id
	         
    Returns:
        to the single view page of that author
    */
    function removeauthor() {
        $author_id   = $this->uri->segment(3,-1);

	    //check rights is done in the $this->bookmarklist_db->removeAuthor function, no need to do it twice

        //load author
        $author = $this->author_db->getByID($author_id);
        if ($author == null)
        {
            appendErrorMessage("Removing author from bookmarklist: non-existing author id passed");
            redirect('');
        }
        
        $this->bookmarklist_db->removeAuthor($author->author_id);
        redirect('authors/show/'.$author->author_id);      
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
            appendErrorMessage( "Add bookmarked publications to topic: no valid topic ID provided.<br/>");
            redirect('bookmarklist/viewlist');
        } 
        $this->bookmarklist_db->addToTopic($topic);
        $this->viewlist();
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
	    if (!$userlogin->hasRights('bookmarklist')) {
	        appendErrorMessage('Making topic from bookmarklist: insufficient rights<br/>');
	        redirect('');
	    }
	    if (!$userlogin->hasRights('topic_edit')) {
	        appendErrorMessage('Insufficient rights to create topic<br/>');
	        redirect('');
	    }
	    
	    $topic = new Topic;
	    $topic->name = '-new from bookmarklist-';
        if (!$topic->add()) {
	        appendErrorMessage('Error creating topic<br/>');
	        redirect('');
        }
        $this->bookmarklist_db->addToTopic($topic);
        redirect('topics/edit/'.$topic->topic_id);
    }

	/** 
	bookmarklist/deleteall
	
	Entry point for deleting all from the bookmarklist.
	Depending on whether 'commit' is specified in the url, confirmation may be requested before actually
	deleting. 
	
	Fails with error message when one of:
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    4rd: if the 3rd segment is the string 'commit', no confirmation is requested.
	         if not, a confirmation form is shown; upon choosing 'confirm' this same controller will be 
	         called with 'commit' specified
	         
    Returns:
        A full HTML page showing a 'request confirmation' form for the delete action, if no 'commit' was specified
        Redirects somewhere (bookmarklist page) after deleting, if 'commit' was specified
	*/
	function deleteall()
	{
	    $commit = $this->uri->segment(3,'');

	    //besides the rights needed to READ this publication, checked by publication_db->getByID, we need to check:
	    //edit_access_level and the user edit rights
        $userlogin  = getUserLogin();

	    if (!$userlogin->hasRights('bookmarklist') || !$userlogin->hasRights('publication_edit')) {
	        appendErrorMessage('Deleting publications from bookmarklist: insufficient rights<br/>');
	        redirect('');
	    }

        if ($commit=='commit') {
            //do delete, redirect somewhere
            $publications = $this->publication_db->getForBookmarkList(-1);
            $nrdeleted = 0;
            $nrskipped = 0;
            foreach ($publications as $publication) {
                if ($this->accesslevels_lib->canEditObject($publication)) {
                    if ($publication->delete()) {
                        $nrdeleted++;
                    } else {
                        $nrskipped++;
                    }
                } else {
                    $nrskipped++;
                }
            }
            appendMessage('Deleted '.$nrdeleted.' publications<br>');
            appendMessage('Skipped '.$nrskipped.' publications due to insufficient rights<br>');
            redirect('bookmarklist');
        } else {
            //get output
            $headerdata = array();
            $headerdata['title'] = 'Delete all from bookmarklist';
            $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('bookmarklist/delete',
                                          array(),  
                                          true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);
        }
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
	        appendErrorMessage('Insufficient rights to clear bookmarklist<br/>');
	        redirect('');
	    }
        $this->bookmarklist_db->clear();
        $this->viewlist();
    }
}
?>
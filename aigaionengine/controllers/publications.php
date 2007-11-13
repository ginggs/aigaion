<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Publications extends Controller {

	function Publications()
	{
		parent::Controller();
		
		$this->load->helper('publication');
	}
	
  /** Default function: list publications */
  function index()
	{
    $this->showlist();
	}

  /** 
    publications/show
    
    Calls single publication view
    
	  Fails with error message when one of:
	    insufficient user rights
	    publication does not exist
	    
	  Parameters passed via URL segments:
	    pub_id
	         
    Returns:
        A single publication overview
  */
  function show($pub_id)
  {
    if (!is_numeric($pub_id))
    {
      //retrieve publication ID
      $pub_id   = $this->uri->segment(3);
    }
    $categorize = $this->uri->segment(4,'');
    if (!$pub_id)
      redirect('');

    //load publication
    $publication = $this->publication_db->getByID($pub_id);
    if ($publication == null)
    {
      appendErrorMessage("View publication: non-existing publication id passed");
      redirect('');
    }
    
    //set header data
    $header ['title']       = 'Aigaion 2.0 - '.$publication->title;
    $header ['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
    $content['publication'] = $publication;
    $content['categorize']  = $categorize=='categorize';
    
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('publications/single', $content, true);
    $output .= $this->load->view('footer',              '',       true);
    
    //set output
    $this->output->set_output($output);
  }
  
  /** 
  publications/showlist
  
  Entry point for showing a list of publications.
  
  fails with error message when one of:
    insufficient user rights
	    
  Parameters passed via URL segments:
      3rd: order by info
	  4rth: page number
	         
  Returns:
      A full HTML page with all a list of all publications
    */
  function showlist()
  {
 	    $this->load->helper('publication');
        $order   = $this->uri->segment(3,'year');
        if (!in_array($order,array('year','type','recent','title','author'))) {
          $order='';
        }
        $page   = $this->uri->segment(4,0);
        //get output
        $headerdata                 = array();
        $headerdata['title']        = 'Publication list';
        $headerdata ['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        $headerdata['sortPrefix']        = 'publications/showlist/';
        
        $userlogin = getUserLogin();
        $content['header']          = 'All publications';
        switch ($order) {
            case 'type':
                $content['header']          = 'All publications ordered on journal and type';
                break;
            case 'recent':
                $content['header']          = 'All recent publications';
                break;
            case 'title':
                $content['header']          = 'All publications ordered on title';
                break;
            case 'author':
                $content['header']          = 'All publications ordered on author';
                break;
        }
        
        
        if ($userlogin->getPreference('liststyle')>0) {
            //set these parameters when you want to get a good multipublication list display
            $content['multipage']       = True;
            $content['currentpage']     = $page;
            $content['multipageprefix'] = 'publications/showlist/'.$order.'/';
        }
        $content['publications']    = $this->publication_db->getForTopic('1',$order);
        $content['order'] = $order;
        
        $output = $this->load->view('header', $headerdata, true);
        $output .= $this->load->view('publications/list', $content, true);
        
        
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);

  }
  /** 
  publications/unassigned
  
  Entry point for showing a list of publications that are not assigned to a topic.
  
  fails with error message when one of:
    insufficient user rights
	    
  Parameters passed via URL segments:
      3rd: order by info
	  4rth: page number
	         
  Returns:
      A full HTML page with all a list of all publications that are not assigned to a topic.
    */
  function unassigned()
  {
 	    $this->load->helper('publication');
        $order   = $this->uri->segment(3,'year');
        if (!in_array($order,array('year','type','recent','title','author'))) {
          $order='';
        }
        $page   = $this->uri->segment(4,0);
        //get output
        $headerdata                 = array();
        $headerdata['title']        = 'Publication list';
        $headerdata ['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        $headerdata['sortPrefix']        = 'publications/unassigned/';
        
        $userlogin = getUserLogin();
        $content['header']          = 'All publications not assigned to a topic';
        switch ($order) {
            case 'type':
                $content['header']          = 'All publications not assigned to a topic ordered on journal and type';
                break;
            case 'recent':
                $content['header']          = 'All recent publications not assigned to a topic';
                break;
            case 'title':
                $content['header']          = 'All publications not assigned to a topic ordered on title';
                break;
            case 'author':
                $content['header']          = 'All publications not assigned to a topic ordered on author';
                break;
        }
        
        
        if ($userlogin->getPreference('liststyle')>0) {
            //set these parameters when you want to get a good multipublication list display
            $content['multipage']       = True;
            $content['currentpage']     = $page;
            $content['multipageprefix'] = 'publications/unassigned/'.$order.'/';
        }
        $content['publications']    = $this->publication_db->getUnassigned('1',$order);
        $content['order'] = $order;
        
        $output = $this->load->view('header', $headerdata, true);
        $output .= $this->load->view('publications/list', $content, true);
        
        
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);

  }  
  //Calls an empty publication edit form
  function add()
  {
    $this->edit();
  }
  
  //edit() - Call publication edit form. When no ID is given: new publicationform
  function edit($publication = "")
  {
    if (is_numeric($publication))
    {
      $pub_id = $publication;
      $publication = $this->publication_db->getByID($pub_id);
      $publication->getKeywords();
      
      //set header data
      $edit_type = "edit";
    }
    else if (empty($publication))
    {
      //php4 compatiblity: new $this->publication won't work
      $publication = $this->publication;
      $edit_type = "new";
    }
    else
    {
      //there was a publication post, retrieve the edit type from the post.
      $edit_type = $this->input->post('edit_type');
    }

    $userlogin  = getUserLogin();
    $user       = $this->user_db->getByID($userlogin->userID());
    if (    (!$userlogin->hasRights('publication_edit'))
         || !$this->accesslevels_lib->canEditObject($publication)           
        ) 
    {
      appendErrorMessage('Edit publication : insufficient rights.<br/>');
      redirect('');
    }
    
    $header ['title']       = "Aigaion 2.0 - ".$edit_type." publication";
    $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
    $content['edit_type']   = $edit_type;
    $content['publication'] = $publication;
    
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('publications/edit',   $content, true);
    $output .= $this->load->view('footer',              '',       true);
    
    //set output
    $this->output->set_output($output);
  }
  
  //import() - Call publication import page
  function import()
  {
    $userlogin  = getUserLogin();
    $user       = $this->user_db->getByID($userlogin->userID());
    if (!$userlogin->hasRights('publication_edit'))
    {
      appendErrorMessage('Import : insufficient rights.<br/>');
      redirect('');
    }
    
    $header ['title']       = "Aigaion 2.0 - import publications";
    $header ['javascripts'] = array();
    
    $content = "";
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('publications/import', $content, true);
    $output .= $this->load->view('footer',              '',       true);
    
    //set output
    $this->output->set_output($output);
  }
  
	/** 
	publications/delete
	
	Entry point for deleting a publication.
	Depending on whether 'commit' is specified in the url, confirmation may be requested before actually
	deleting. 
	
	Fails with error message when one of:
	    delete requested for non-existing publication
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: pub_id, the id of the to-be-deleted-publication
	    4th: if the 4th segment is the string 'commit', no confirmation is requested.
	         if not, a confirmation form is shown; upon choosing 'confirm' this same controller will be 
	         called with 'commit' specified
	         
    Returns:
        A full HTML page showing a 'request confirmation' form for the delete action, if no 'commit' was specified
        Redirects somewhere (?) after deleting, if 'commit' was specified
	*/
	function delete()
	{
	    $pub_id = $this->uri->segment(3);
	    $publication = $this->publication_db->getByID($pub_id);
	    $commit = $this->uri->segment(4,'');

	    if ($publication==null) {
	        appendErrorMessage('Delete publication: non existing publication specified.<br/>');
	        redirect('');
	    }

	    //besides the rights needed to READ this publication, checked by publication_db->getByID, we need to check:
	    //edit_access_level and the user edit rights
        $userlogin  = getUserLogin();

        if (    (!$userlogin->hasRights('publication_edit'))
             || 
                !$this->accesslevels_lib->canEditObject($publication)        
            ) 
        {
	        appendErrorMessage('Delete publication: insufficient rights.<br/>');
	        redirect('');
        }
        
        if ($commit=='commit') {
            //do delete, redirect somewhere
            if ($publication->delete()) {
                redirect('');
            } else {
                redirect('publications/show/'.$publication->pub_id);
            }
        } else {
            //get output
            $headerdata = array();
            $headerdata['title'] = 'Delete publication';
            $headerdata['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('publications/delete',
                                          array('publication'=>$publication),  
                                          true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);
        }
    }  
  
  //commit() - Commit the posted publication to the database
  function commit()
  {
    
    $publication = $this->publication_db->getFromPost();
    $oldpublication = $this->publication_db->getByID($publication->pub_id); //needed to check access levels, as post data may be rigged
    //check the submit type, if 'type_change', we redirect to the edit form
    $submit_type = $this->input->post('submit_type');
    
    if ($submit_type == 'type_change')
    {
      $this->edit($publication);
    }
    else if ($this->publication_db->validate($publication))
    {
      $edit_type = $this->input->post('edit_type');
      $bReview = false;
      if ($submit_type != 'review')
      {
        //review cite id
        $review['bibtex_id']   = $this->publication_db->reviewBibtexID($publication);
        
        //review keywords
        $review['keywords']  = $this->keyword_db->review($publication->keywords);

        //review authors and editors
        $review['authors']   = $this->author_db->review($publication->authors);
        $review['editors']   = $this->author_db->review($publication->editors);
        
        if (($review['bibtex_id']   != null) ||
            ($review['keywords']  != null) || 
            ($review['authors']   != null) || 
            ($review['editors']   != null))
        {
          $bReview = true;
          $review['edit_type'] = $edit_type;
          $this->review($publication, $review);
        }
      }
      if (!$bReview)
      {
        //do actual commit, depending on the edit_type, choose add or update
        $userlogin  = getUserLogin();
        $user       = $this->user_db->getByID($userlogin->userID());
        if ( (!$userlogin->hasRights('publication_edit'))
          || (($oldpublication == null) && ($edit_type != 'new'))
          || (!$this->accesslevels_lib->canEditObject($oldpublication) && ($oldpublication != null))
          ) 
        {
          appendErrorMessage('Commit publication: insufficient rights.<br/>');
          redirect('');
        }
        
        if ($edit_type == 'new')
          $publication = $this->publication_db->add($publication);
        else
          $publication = $this->publication_db->update($publication);
              
        //show publication
        redirect('publications/show/'.$publication->pub_id);
      }
    }
    else //there were validation errors
    {
      //edit the publication once again
      $this->edit($publication);
    }
  }
  
  function review($publication, $review_data)
  {
    $oldpublication = $this->publication_db->getByID($publication->pub_id); //needed to check access levels, as post data may be rigged
    $userlogin      = getUserLogin();
    $user           = $this->user_db->getByID($userlogin->userID());
    if ((!$userlogin->hasRights('publication_edit'))
         || (($oldpublication == null) && ($review_data['edit_type']!='new'))
         || (!$this->accesslevels_lib->canEditObject($oldpublication) && ($oldpublication != null))
        ) 
    {
      appendErrorMessage('Review publication: insufficient rights.<br/>');
      redirect('');
    }

    $header ['title']       = "Aigaion 2.0 - review publication";
    $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
    $content['publication'] = $publication;
    $content['review']      = $review_data;
    
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('publications/review', $content, true);
    $output .= $this->load->view('footer',              '',       true);
    
    //set output
    $this->output->set_output($output);
  }

/**
    publications/subscribe
    
    Susbcribes a publication to a topic. Is normally called async, without processing the
    returned partial, by clicking a subscribe link in a topic tree rendered by 
    subview 'publicationsubscriptiontreerow' 
    
	Fails with error message when one of:
	    susbcribe requested for non-existing topic or publication
	    insufficient user rights
	    
	Parameters passed via URL:
	    3rd segment: topic_id
	    4rd segment: publication_id 
	         
    Returns a partial html fragment:
        an empty div if successful
        an div containing an error message, otherwise
    
    */
    function subscribe() {    
        $topic_id = $this->uri->segment(3,-1);
        $pub_id = $this->uri->segment(4,-1);
        
        $publication = $this->publication_db->getByID($pub_id);
        if ($publication == null) {
            echo "<div class='errormessage'>Subscribe topic: no valid publication ID provided</div>";
        }

        $config = array('publicationId'=>$pub_id);
        $topic = $this->topic_db->getByID($topic_id,$config);
        
        if ($topic == null) {
            echo "<div class='errormessage'>Subscribe topic: no valid topic ID provided</div>";
        }
        //do subscribe
        $topic->subscribePublication();

        echo "<div/>";
    }    
    
        
    /**
    publications/unsubscribe
    
    Unsusbcribes a publication from a topic. Is normally called async, without processing the
    returned partial, by clicking a subscribe link in a topic tree rendered by 
    subview 'publicationsubscriptiontreerow' 
    
	Fails with error message when one of:
	    unsusbcribe requested for non-existing topic or publication
	    insufficient user rights
	    
	Parameters passed via URL:
	    3rd segment: topic_id
	    4rd segment: publication_id 
	         
    Returns a partial html fragment:
        an empty div if successful
        an div containing an error message, otherwise
    
    */
    function unsubscribe() {    
        $topic_id = $this->uri->segment(3,-1);
        $pub_id = $this->uri->segment(4,-1);
        
        $publication = $this->publication_db->getByID($pub_id);
        if ($publication == null) {
            echo "<div class='errormessage'>Unsubscribe topic: no valid publication ID provided</div>";
        }
        $config = array('publicationId'=>$pub_id);
        $topic = $this->topic_db->getByID($topic_id,$config);
        
        if ($topic == null) {
            echo "<div class='errormessage'>Unsubscribe topic: no valid topic ID provided</div>";
        }
        //do subscribe
        $topic->unsubscribePublication();

        echo "<div/>";
    }    
    
    /**
    publications/read
    
    marks a publication as read
    
	Fails with error message when one of:
	    read requested for non-existing publication
	    insufficient user rights
	    
	Parameters passed via URL:
	    3rd segment: publication_id 
	    possibly through post: mark
	         
    Redirects to publication view
    */
    function read() {
        $pub_id = $this->uri->segment(3,-1);
        
        $publication = $this->publication_db->getByID($pub_id);
        if ($publication == null) {
            appendErrorMessage('Mark publication: unknown publication');
            redirect ('');
        }
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('note_edit')) {
            appendErrorMessage('Mark publication: insufficient rights');
            redirect ('publications/show/'.$publication->pub_id);
        }
        $mark = $this->input->post('mark','');
        if ($mark==0)$mark='';
        $publication->read($mark);
        redirect ('publications/show/'.$publication->pub_id);
    }
    /**
    publications/unread
    
    marks a publication as not-read
    
	Fails with error message when one of:
	    unread requested for non-existing publication
	    insufficient user rights
	    
	Parameters passed via URL:
	    3rd segment: publication_id 
	         
    Redirects to publication view
    */
    function unread() {
        $pub_id = $this->uri->segment(3,-1);
        
        $publication = $this->publication_db->getByID($pub_id);
        if ($publication == null) {
            appendErrorMessage('Mark publication: unknown publication');
            redirect ('');
        }
        $userlogin = getUserLogin();
        if (!$userlogin->hasRights('note_edit')) {
            appendErrorMessage('Mark publication: insufficient rights');
            redirect ('publications/show/'.$publication->pub_id);
        }
        $publication->unread();
        redirect ('publications/show/'.$publication->pub_id);
    }
}
?>
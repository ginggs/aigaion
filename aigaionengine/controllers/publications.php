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
  
  function showlist()
  {
 	      $this->load->helper('publication');
        $page   = $this->uri->segment(3,0);
        //get output
        $headerdata                 = array();
        $headerdata['title']        = 'Publication list';
        $headerdata ['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
        
        $content['header']          = 'All publications';
        $content['publications']    = $this->publication_db->getForTopic('1',$page);
        
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
  
  
  //delete() - Remove one publication from the database
  function delete()
  {
    $userlogin  = getUserLogin();
    $user       = $this->user_db->getByID($userlogin->userID());
    if ((!$userlogin->hasRights('publication_edit'))
         || !$this->accesslevels_lib->canEditObject($publication)         
        ) 
    {
      appendErrorMessage('Delete publication: insufficient rights.<br/>');
      redirect('');
    }
    echo "Single publication delete";
  }
  
  
  //commit() - Commit the posted publication to the database
  function commit()
  {
    $this->load->helper('specialchar');
    
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
        //review keywords
        $review['keywords']  = $this->keyword_db->review($publication->keywords);

        //review authors and editors
        $review['authors']   = $this->author_db->review($publication->authors);
        $review['editors']   = $this->author_db->review($publication->editors);
        
        if (($review['keywords']  != null) || 
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
          || !$this->accesslevels_lib->canEditObject($oldpublication)
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
         || !$this->accesslevels_lib->canEditObject($oldpublication) 
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
    
  function jaro()
  {
    $header ['title']       = "Aigaion 2.0 - ";
    $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
    
    
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $this->load->helper('specialchar');
    
    $str_a = $this->uri->segment(3);
    $str_b = $this->uri->segment(4);
    
    for ($i = 0; $i < 1000; $i++)
    {
      levenshtein($str_a, $str_b);
      //jaroSimilarity($str_a, $str_b);
    }
    
    $output .= $this->load->view('footer',              '',       true);
    
    $this->output->set_output($output);
    
  }
}
?>
<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Import extends Controller {

	function Import()
	{
		parent::Controller();
		
		$this->load->helper('publication');
	}
	
  /** Default function: list publications */
  function index()
	{
    $this->viewform();
	}

  function viewform()
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
    $output .= $this->load->view('import/importform', $content, true);
    $output .= $this->load->view('footer',              '',       true);
    
    //set output
    $this->output->set_output($output);
  }
  
	
  //commit() - Commit the posted publication to the database
  function commit()
  {
    $this->load->helper('specialchar');
    $this->load->library('parser_bibtex');

    $import_data = $this->input->post('import_data');    
    
    //TODO: DETECT WHETER BIBTEX OR RIS. FOR NOW: ASSUME BIBTEX
    $this->parser_bibtex->loadData($import_data);
    $this->parser_bibtex->parse();
    $publications = $this->parser_bibtex->getPublications();
    
    $reviewed_publications  = array();
    $review_messages        = array();
    $count                  = 0;
    foreach ($publications as $publication) {
        //get review messages
        //review bibtex_id
        $review['bibtex_id']   = $this->publication_db->reviewBibtexID($publication);
        
        //review keywords
        $review['keywords']  = $this->keyword_db->review($publication->keywords);

        //review authors and editors
        $review['authors']   = $this->author_db->review($publication->authors);
        $review['editors']   = $this->author_db->review($publication->editors);
        
        $reviewed_publications[$count] = $publication;
        $review_messages[$count]       = $review;
        $count++;
        unset($review);
      }
      $this->review($reviewed_publications[0], $review_messages[0]);

/*    if (!$bReview)
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
    
    else //there were validation errors
    {
      //edit the publication once again
      $this->edit($publication);
    }
  */  
  }
  
  function review($publication, $review_data)
  {
    $oldpublication = $this->publication_db->getByID($publication->pub_id); //needed to check access levels, as post data may be rigged
    $userlogin      = getUserLogin();
    $user           = $this->user_db->getByID($userlogin->userID());
    $review_data['edit_type'] = 'new';
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

 
}
?>
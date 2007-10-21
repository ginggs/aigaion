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
    $this->load->library('parser_import');

    $import_data  = $this->input->post('import_data');    
    $type = '';
    //determine type of input
    if (preg_match("/(@[A-Za-z]{4,}\s*[\r\n\t]*{)/", $import_data) == 1)
    {
      $type = "BibTeX";
    } 
    else if (preg_match("/(TY\s{1,2}-\s)/", $import_data) == 1)
    {
      $type = "ris";
    }
    else if (preg_match("/\%0/", $import_data) == 1)
    {
      $type = "refer";
    }
    
    $import_count = $this->input->post('import_count');
    $markasread   = $this->input->post('markasread')=='markasread'; // true iff all imported entries should be marked as 'read' for the user
    if ($import_data != null)
    {
      switch ($type) {
        case 'BibTeX':
          $this->load->library('parseentries');
          $this->parser_import->loadData($import_data);
          $this->parser_import->parse($this->parseentries);
          $publications = $this->parser_import->getPublications();
          break;
        case 'ris':
          $this->load->library('parseentries_ris');
          $this->parser_import->loadData($import_data);
          $this->parser_import->parse($this->parseentries_ris);
          $publications = $this->parser_import->getPublications();
          break;
        case 'refer':
          $this->load->library('parseentries_refer');
          $this->parser_import->loadData($import_data);
          $this->parser_import->parse($this->parseentries_refer);
          $publications = $this->parser_import->getPublications();
          break;
        default:
          appendErrorMessage("Import: can't figure out import data format; no parsing possible");
          redirect('import/viewform');
          break;
      }
      
      $reviewed_publications  = array();
      $review_messages        = array();
      $count                  = 0;
      foreach ($publications as $publication) {
          //get review messages
          
          //review title
          $review['title']     = $this->publication_db->reviewTitle($publication);
          
          //review bibtex_id
          $review['bibtex_id'] = $this->publication_db->reviewBibtexID($publication);
          
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
        $this->review($reviewed_publications, $review_messages);
    }
    if ($import_count != null)
    {
      $count = 0;
      for ($i = 0; $i < $import_count; $i++)
      {
        if ($this->input->post('do_import_'.$i) == 'CHECKED')
        {
          $count++;
          $publication = $this->publication_db->getFromPost("_".$i);
          $publication = $this->publication_db->add($publication);
        }
      }
      appendMessage('Succesfully imported '.$count.' publications.');
      redirect('publications/showlist/recent');
    }
  }
  
  function review($publications, $review_data)
  {
    $userlogin      = getUserLogin();
    if (!$userlogin->hasRights('publication_edit'))
    {
      appendErrorMessage('Review publication: insufficient rights.<br/>');
      redirect('');
    }

    $header ['title']       = "Aigaion 2.0 - review publication";
    $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
    $content['publications'] = $publications;
    $content['reviews']      = $review_data;
    
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('import/review',       $content, true);
    $output .= $this->load->view('footer',              '',       true);
    
    //set output
    $this->output->set_output($output);
  }

 
}
?>
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
    
    $header ['title']       = "import publications";
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
          $review['authors']   = $this->author_db->review($publication->authors); //each item consists of an array A with A[0] a review message, and A[1] an array of arrays of the similar author IDs
          $review['editors']   = $this->author_db->review($publication->editors); //each item consists of an array A with A[0] a review message, and A[1] an array of arrays of the similar author IDs
          
          $reviewed_publications[$count] = $publication;
          $review_messages[$count]       = $review;
          $count++;
          unset($review);
        }
        $this->review($reviewed_publications, $review_messages,$markasread);
    }
    if ($import_count != null)
    {
      $to_import = array();
      $old_bibtex_ids = array();
      $count = 0;
      for ($i = 0; $i < $import_count; $i++)
      {
        if ($this->input->post('do_import_'.$i) == 'CHECKED')
        {
          $count++;
          $publication = $this->publication_db->getFromPost("_".$i,True);
          $publication->actualyear = $this->input->post('actualyear_'.$i); //note that the actualyear is a field that normally is derived on update or add, but in the case of import, it has been set through the review form!
          $to_import[] = $publication;
          $old_bibtex_ids[$this->input->post('old_bibtex_id_'.$i)] = $count-1;
        }
      }
      foreach ($to_import as $pub_to_import) {
        //if necessary, change crossref (if reffed pub has changed bibtex_id)
        if (trim($pub_to_import->crossref)!= '') {
  	        if (array_key_exists($pub_to_import->crossref,$old_bibtex_ids)) {
  	            $pub_to_import->crossref = $to_import[$old_bibtex_ids[$pub_to_import->crossref]]->bibtex_id;
  	            //appendMessage('changed crossref entry:'.$publication->bibtex_id.' crossref:'.$publication->crossref);
  	        }
        }            
        $pub_to_import = $this->publication_db->add($pub_to_import);
        if ($markasread)$pub_to_import->read('');
      }
      appendMessage('Succesfully imported '.$count.' publications.');
      redirect('publications/showlist/recent');
    }
  }
  
  function review($publications, $review_data,$markasread)
  {
    $userlogin      = getUserLogin();
    if (!$userlogin->hasRights('publication_edit'))
    {
      appendErrorMessage('Review publication: insufficient rights.<br/>');
      redirect('');
    }

    $header ['title']       = "review publication";
    $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
    $content['publications'] = $publications;
    $content['reviews']      = $review_data;
    $content['markasread']   = $markasread;
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('import/review',       $content, true);
    $output .= $this->load->view('footer',              '',       true);
    
    //set output
    $this->output->set_output($output);
  }

 
}
?>
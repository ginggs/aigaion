<?php

class Authors extends Controller {

	function Authors()
	{
		parent::Controller();
	}
	
	function index()
	{
	  $this->_authorlist();
	}

  //show() - Call single author overview
  function show($author_id)
  {
    if (!is_numeric($author_id))
    {
      //retrieve author ID
      $author_id   = $this->uri->segment(3);
    }
    
    //load author
    $author = $this->author_db->getByID($author_id);
    if ($author == null)
    {
      appendErrorMessage("View Author: non-existing author id passed");
      redirect('');
    }
    
    $this->load->helper('publication');
    
    
    //set header data
    $header ['title']       = 'Aigaion 2.0 - '.$author->getName();
    $header ['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
    
    //set data
    $authorContent['author']            = $author;
    $publicationContent['header']       = 'Publications of '.$author->getName();
    $publicationContent['publications'] = $this->publication_db->getForAuthor($author_id);

    
    //get output
    $output  = $this->load->view('header',              $header,        true);
    $output .= $this->load->view('authors/single',      $authorContent, true);
    
    if ($publicationContent['publications'] != null) {
      $output .= $this->load->view('publications/list', $publicationContent, true);
    }
    
    $output .= $this->load->view('footer',              '',             true);
    
    //set output
    $this->output->set_output($output);  
  }
  
  //edit() - Call author edit form. When no ID is given: new authorform
  function edit()
  {
    echo "Single author form";
    
    //get ID from segment
    
    //if no ID: empty form
    
    //if ID: get Author and present in form
  }
  
  //delete() - Remove one author from the database
  function delete()
  {
    echo "Single author delete";
    
    //get ID from segment
    //delete author
  }
  
  //commit() - Commit the posted author to the database
  function commit()
  {
    echo "Single author commit";
    
    //retrieve author from POST
    //commit author
  }
  
  function _authorlist()
  {
    $this->load->helper('form');
    
    $authorList = $this->author_db->getAllAuthors();
    
    
    
    //set header data
    $header ['title']         = 'Aigaion 2.0 - Authors';
    $header ['javascripts']   = array('prototype.js');
    $content['header']        = "All authors in the database";
    $content['authorlist']    = $authorList;
    
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('authors/list',        $content, true);
    $output .= $this->load->view('footer',              '',       true);
    
    //set output
    $this->output->set_output($output);

  }
  
  function searchlist()
  {
    $author_search = $this->input->post('author_search');
    if ($author_search) // user pressed show, so redirect to single author page
    {
      echo "to be done: user pressed 'show' -> find corresponding author and redirect to single author page";
    }
    else
    {
      $author_search  = $this->uri->segment(3);
      $authorList     = $this->author_db->getAuthorsLike($author_search);
      echo $this->load->view('authors/list_items', array('authorlist' => $authorList), true);
    }
  }
}
?>
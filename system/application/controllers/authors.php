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
  function show()
  {
    //retrieve author ID
    $author_id   = $this->uri->segment(3);
    
    if (!$author_id)
      $author_id = 1;
      
    //load author
    $this->load->model('author_model');
    $author = new Author_model;
    $author->getByID($author_id);
    
    //get author's publications
    $this->load->model('publication_list_model');
    $publicationList = new Publication_list_model;
    $publicationList->getForAuthor($author_id);
    $publicationList->header = "Publications of ".$author->getName();
    
    //set header data
    $header ['title']         = 'Aigaion 2.0 - '.$author->getName();
    $content['author']        = $author;
    $content['publications']  = $publicationList;
    
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('authors/single',      $content, true);
    $output .= $this->load->view('footer',              '',       true);
    
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
    $this->load->model('author_list_model');
    $authorList = new Author_list_model;
    $authorList->getAll();
    $authorList->header = "All authors in the database";
    
    //set header data
    $header ['title']         = 'Aigaion 2.0 - '.$authorList->header;
    
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('authors/list',        $authorList, true);
    $output .= $this->load->view('footer',              '',       true);
    
    //set output
    $this->output->set_output($output);

  }
}
?>
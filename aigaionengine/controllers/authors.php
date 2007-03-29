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
    $this->load->helper('publication');
    $this->load->model('author_model');
    $this->load->model('publication_list_model');
    
    $author           = new Author_model;
    $publicationlist  = new Publication_list_model;
    
    //retrieve author ID
    $author_id        = $this->uri->segment(3);
    
    if (!$author_id)
      $author_id      = 1;
      
    //load author
    $author->loadByID($author_id);
    
    //get author's publications
    $publicationlist->loadForAuthor($author_id);
    $publicationlist->header = "Publications of ".$author->data->getName();
    
    //set data
    $header ['title']           = 'Aigaion 2.0 - '.$author->data->getName();
    $content['author']          = $author;
    $content['publicationlist'] = $publicationlist;
    
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
    $this->load->helper('form');
    $this->load->model('author_list_model');
    $authorList = new Author_list_model;
    $authorList->loadAll();
    $authorList->header = "All authors in the database";
    
    //set header data
    $header ['title']         = 'Aigaion 2.0 - '.$authorList->header;
    $header ['javascripts']   = array('prototype.js');
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
      $author_search = $this->uri->segment(3);
      $this->load->model('author_list_model');
      $authorList = new Author_list_model;
      $authorList->loadWhere($author_search);
      echo $this->load->view('authors/list_items', array('authorlist' => $authorList), true);
    }
  }
}
?>
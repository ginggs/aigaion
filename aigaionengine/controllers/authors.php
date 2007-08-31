<?php if (!defined('BASEPATH')) exit('No direct script access allowed'); ?>
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
  
  //Calls an empty author edit form
  function add()
  {
    $this->edit();
  }
  
  //edit() - Call author edit form. When no ID is given: new authorform
  function edit($author = "")
  {
    if (is_numeric($author))
    {
      $author_id  = $author;
      $author     = $this->author_db->getByID($author_id);
      
      //set header data
      $edit_type = "edit";
    }
    else if (empty($author))
    {
      //php4 compatiblity: new $this->author won't work
      $author     = $this->author;
      $edit_type  = "new";
    }
    else
    {
      //there was a author post, retrieve the edit type from the post.
      $edit_type = $this->input->post('edit_type');
    }
    
    $userlogin = getUserLogin();
    if (!$userlogin->hasRights('publication_edit'))
    {
      appendErrorMessage('Edit author: insufficient rights.<br/>');
      redirect('');
    }

    $header ['title']       = "Aigaion 2.0 - ".$edit_type." author";
    $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
    $content['edit_type']   = $edit_type;
    $content['author']      = $author;
    
    //get output
    $output  = $this->load->view('header',        $header,  true);
    $output .= $this->load->view('authors/edit',  $content, true);
    $output .= $this->load->view('footer',        '',       true);
    
    //set output
    $this->output->set_output($output);
  }
  
  
	/** 
	authors/delete
	
	Entry point for deleting an author.
	Depending on whether 'commit' is specified in the url, confirmation may be requested before actually
	deleting. 
	
	Fails with error message when one of:
	    delete requested for non-existing author
	    insufficient user rights
	    
	Parameters passed via URL segments:
	    3rd: author_id, the id of the to-be-deleted-author
	    4th: if the 4th segment is the string 'commit', no confirmation is requested.
	         if not, a confirmation form is shown; upon choosing 'confirm' this same controller will be 
	         called with 'commit' specified
	         
    Returns:
        A full HTML page showing a 'request confirmation' form for the delete action, if no 'commit' was specified
        Redirects somewhere (?) after deleting, if 'commit' was specified
	*/
	function delete()
	{
	    $author_id = $this->uri->segment(3);
	    $author = $this->author_db->getByID($author_id);
	    $commit = $this->uri->segment(4,'');

	    if ($author==null) {
	        appendErrorMessage('Delete author: author does not exist.<br/>');
	        redirect('');
	    }

        $userlogin  = getUserLogin();
        if (    (!$userlogin->hasRights('publication_edit'))
            ) 
        {
	        appendErrorMessage('Delete author: insufficient rights.<br/>');
	        redirect('');
        }

        if ($commit=='commit') {
            //do delete, redirect somewhere
            $author->delete();
            redirect('authors');
        } else {
            //get output: a full web page with a 'confirm delete' form
            $headerdata = array();
            $headerdata['title'] = 'Author: delete';
            
            $output = $this->load->view('header', $headerdata, true);
    
            $output .= $this->load->view('authors/delete',
                                         array('author'=>$author),  
                                         true);
            
            $output .= $this->load->view('footer','', true);
    
            //set output
            $this->output->set_output($output);	
        }
    }
  
  //commit() - Commit the posted author to the database
  function commit()
  {
    $userlogin = getUserLogin();
    if (!$userlogin->hasRights('publication_edit'))
    {
      appendErrorMessage('Edit author: insufficient rights.<br/>');
      redirect('');
    }

    
    $author = $this->author_db->getFromPost();
    //check the submit type, if 'type_change', we redirect to the edit form
    $submit_type = $this->input->post('submit_type');
    
    if ($this->author_db->validate($author))
    {
      $bReview = false;
      if ($submit_type != 'review')
      {

        //review authors and editors
        $review['author']   = $this->author_db->review(array($author));
        
        if ($review['author'] != null)
        {
          $bReview = true;
          $this->review($author, $review);
        }
      }
      if (!$bReview)
      {
        //do actual commit, depending on the edit_type, choose add or update
        //
        $edit_type = $this->input->post('edit_type');
        if ($edit_type == 'new') {
          //note: the author_db review method will not give an error if ONE EXACT MATCH EXISTS
          //so we should still check that here
          if ($this->author_db->getByExactName($author->firstname,$author->von,$author->surname) != null) {
            appendMessage('Author "'.$author->getName('lvf').'" already exists in the database.<br/>');
            redirect('authors/add');
          } else {
            $author = $this->author_db->add($author);
          }
        } else
          $author = $this->author_db->update($author);
              
        //show publication
        redirect('authors/show/'.$author->author_id);
      }
    }
    else //there were validation errors
    {
      //edit the publication once again
      $this->edit($author);
    }
  }

  function review($author, $review_data)
  {
    $userlogin = getUserLogin();
    if (!$userlogin->hasRights('publication_edit'))
    {
      appendErrorMessage('Edit author: insufficient rights.<br/>');
      redirect('');
    }

    $header ['title']       = "Aigaion 2.0 - review publication";
    $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
    $content['edit_type']   = $this->input->post('edit_type');
    $content['author']      = $author;
    $content['review']      = $review_data;
    
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('authors/edit',        $content, true);
    $output .= $this->load->view('footer',              '',       true);
    
    //set output
    $this->output->set_output($output);
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
  
  function li_authors($fieldname = "")
  {
    if ($fieldname == "")
      $fieldname = 'authors';
      
    $author = $this->input->post($fieldname);
    
    if ($author != "")
    {
      $authors = $this->author_db->getAuthorsLike($author);
      echo $this->load->view('authors/li_authors', array('authors' => $authors), true);
    }
  }
}
?>
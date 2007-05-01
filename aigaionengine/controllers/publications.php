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
  
  //edit() - Call publication edit form. When no ID is given: new publicationform
  function edit($publication = "")
  {
    $this->load->library('validation');
    $this->validation->set_error_delimiters('<div class="errormessage">Changes not committed: ', '</div>');

    if (is_numeric($publication))
    {
      $pub_id = $publication;
      $publication = $this->publication_db->getByID($pub_id);
      
      //set header data
      $edit_type = "edit";

      if ($pub_id)
        $edit_type = "new";
    }
    else
    {
      $edit_type = "change";
    } 

    $header ['title']       = "Aigaion 2.0 - ".$edit_type." publication";
    $header ['javascripts'] = array('prototype.js', 'effects.js', 'dragdrop.js', 'controls.js');
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
    echo "Single publication delete";
  }
  
  //commit() - Commit the posted publication to the database
  function commit()
  {
    $this->load->helper('specialchar');
    $this->load->library('validation');
    $this->validation->set_error_delimiters('<div class="errormessage">Changes not committed: ', '</div>');

    $publication = $this->publication_db->getFromPost();
    
    //validate form values; 
    //validation rules: get required fields from publication field array
    $validate_required  = array();
    $fields             = getPublicationFieldArray($publication->pub_type);
    foreach ($fields as $field => $value)
    {
      if ($value == 'required')
      {
        $validate_required[$field] = 'required';
      }
    }
    $this->validation->set_rules($validate_required);
    //$this->validation->set_fields(array( 'name' => 'Topic Name'));
    
    //check the submit type, if 'type_change', we redirect to the edit form
    $submit_type = $this->input->post('submit_type');
    
    if ($submit_type == 'type_change')
    {
      $this->edit($publication);
    }
    else if ($this->validation->run())
    {
      //do actual commit
      
      //show publication
      redirect('publications/show/'.$publication->pub_id);
    }
    else //there were validation errors
    {
      //edit the publication once again
      $this->edit($publication);
    }
  }

  function showlist()
  {
 	      $this->load->helper('publication');
    
        //get output
        $headerdata                 = array();
        $headerdata['title']        = 'Publication list';
        
        $content['header']          = 'testheader';
        $content['publications']    = $this->publication_db->getForTopic('1');
        
        $output = $this->load->view('header', $headerdata, true);
        $output .= $this->load->view('publications/list', $content, true);
        
        
        
        $output .= $this->load->view('footer','', true);

        //set output
        $this->output->set_output($output);

  }
}
?>
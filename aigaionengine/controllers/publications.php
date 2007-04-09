<?php

class Publications extends Controller {

	function Publications()
	{
		parent::Controller();
		$header   = array();
		$content  = array();
		$footer   = array();
		
		$this->load->helper('publication');
	}
	
	function index()
	{
	  $type = $this->uri->segment(3);
	  $id   = $this->uri->segment(4);
	  
	  switch ($type) { 
	      
	    default:
	      $this->showlist();
	  }
	}

  //show() - Call single publication overview
  function show($pub_id)
  {
    if (!is_numeric($pub_id))
    {
      //retrieve publication ID
      $pub_id   = $this->uri->segment(3);
    }
    
    if (!$pub_id)
      redirect('');
      
    //load publication
    $this->load->model('publication_model');
    
    $publication = new Publication_model;
    $publication->loadByID($pub_id);
    
    //set header data
    $header ['title']       = 'Aigaion 2.0 - '.$publication->data->title;
    $header ['javascripts'] = array('tree.js','scriptaculous.js','builder.js','prototype.js');
    $content['publication'] = $publication;
    
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
    $this->load->helper('form');
    $this->load->model('publication_model');

    if (is_numeric($publication))
    {
      $publication = new Publication_model;
      
      //retrieve publication ID
      $pub_id   = $this->uri->segment(3);
      
      //load publication
      
      if ($pub_id)
        $publication->loadByID($pub_id);
      
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
    $this->load->model('publication_model');
    $this->load->helper('specialchar');
    $publication = new Publication_model;
    $publication->loadFromPost();

    if ($publication->validate())
    {
      //do actual commit
      
      //show publication
      redirect('publications/show/'.$publication->data->pub_id);
    }
    else
    {
      //show error and redirect to editform
      echo "<div class='header'>".$publication->validationMessage."</div>\n";
      $this->edit($publication);
    }
  }
  
  function showlist()
  {
    $this->load->model('publication_list_model');
    
    $publicationlist = new Publication_list_model;
    $publicationlist->loadAll();
    $publicationlist->header = "All Publications in the Database";
    
    //set header data
    $header ['title']           = 'Aigaion 2.0 - '.$publicationlist->header;
    $data   ['publicationlist'] = $publicationlist;
    
    //get output
    $output  = $this->load->view('header',              $header,  true);
    $output .= $this->load->view('publications/list',   $data,    true);
    $output .= $this->load->view('footer',              '',       true);
    
    //set output
    $this->output->set_output($output);  
  }
  
  function li_keywords()
  {
   
   
    $keywords = $this->input->post('authors');
    if (!$keywords)
      $keywords = $this->input->post('editors');
    
    if (!$keywords)
      $keywords = $this->input->post('keywords');
    
    if ($keywords != "")
    {
      $this->db->select('cleanname');
      $this->db->from('author');
      $this->db->like('cleanname', $keywords);
      $this->db->orderby('cleanname');
      $this->db->limit('50');
      $Q = $this->db->get();
      
      if ($Q->num_rows() > 0)
      {
        echo $this->load->view('li_keywords', array('keywords' => $Q->result()), true);
      }
    }
   
  }
}
?>
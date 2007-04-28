<?php
class Publication_model extends Model {
  
  //declare variables: Publication vars
  var $userVars   = array();
  var $systemVars = array('pub_id'        => '', 
                          'specialchars'  => '', 
                          'cleantitle'    => '', 
                          'cleanjournal'  => '', 
                          //'actualyear'    => '',
                          'user_id'    => '');
  
  
  //authors and editors
  var $authors = array();
  var $editors = array();
  

	function Publication_model()
	{
		parent::Model();
		$this->prettyPrint = true;
	}
	
	function loadByID($pub_id)
	{
	  $Q = $this->db->getwhere('publication', array('pub_id' => $pub_id));
	  if ($Q->num_rows() > 0)
	  {
	    $this->loadFromRow($Q->row());
	  }  
	}
	
	function loadFromRow($R)
	{
	  $this->authors = array();
	  $this->editors = array();
	  $this->userVars = array();
	  //get all fields from the database and store in userVars array
	  foreach ($R as $key => $value)
    {
      $this->userVars[$key] = $value;
    }
    //separate systemvars from uservars
    foreach ($this->systemVars as $key => $value)
    {
      $this->systemVars[$key] = $this->userVars[$key];
      unset($this->userVars[$key]);
    }
    //retrieve authors and editors
    $this->db->select('*');
    $this->db->from('author, publicationauthor');
    $this->db->where('author.ID = publicationauthor.author');//', 'publicationauthor.author');
    $this->db->where('publicationauthor.pub_id', $this->systemVars['pub_id']);
    $this->db->orderby('publicationauthor.rank');
    
    $Q = $this->db->get();
    if ($Q->num_rows() > 0)
    {
      $this->load->model('author_model');
      
      foreach ($Q->result() as $R)
      {
        $author = new Author_model;
        $author->loadFromRow($R);
        if ($R->is_editor == 'N')
        {
          $this->authors[] = $author;
        }
        else
        {
          $this->editors[] = $author;
        }
        unset($author);
      }
    }

    //format output
    $this->_formatUserVars();
  }
  
  function loadFromPost()
  {
  }
  
  function loadFromArray($publication)
  {
    $this->loadFromRow($publication);
  }
  
  function addObject()
  {
  }
  
  function updateObject()
  {
  }
  
  function deleteObject()
  {
  }
  
  function getUserVars()
  {
    return $this->userVars;
  }
  
  function getAsArray()
  {
    $authors = array();
    foreach ($this->authors as $author)
    {
      $authors[] = $author->getAsArray();
    }
    $editors = array();
    foreach ($this->editors as $editor)
    {
      $editors[] = $editor->getAsArray();
    }
    $returnArray =  array_merge($this->systemVars, $this->userVars);
    $returnArray['authors'] = $authors;
    $returnArray['editors'] = $editors;
    
    return $returnArray;
  }
  
  function _formatUserVars()
  {
    if ($this->prettyPrint)
    {
      if ($this->systemVars['specialchars'] == "TRUE")
      {
        $CI = &get_instance();
        $CI->load->helper('specialchar');
        //format uservars
        $this->userVars = prettyPrintBibCharsFromArray($this->userVars);
      }
    }

		if (($this->userVars['firstpage'] != "0") || ($this->userVars['lastpage'] != "0")) {
		  $this->userVars['pages'] = "";
			if ($this->userVars['firstpage'] != "0") {
				$this->userVars['pages'] = $this->userVars['firstpage'];
			}

			if (($this->userVars['firstpage'] != $this->userVars['lastpage']) && ($this->userVars['lastpage'] != "0")) {
				$this->userVars['pages'] .= "-".$this->userVars['lastpage'];
			}
		}

  }
  
  function _getPrettyTitle()
  {
      if ($this->systemVars['specialchars'] == "TRUE")
      {
        $CI = &get_instance();
        $CI->load->helper('specialchar');
        //format uservars
        return prettyPrintBibCharsFromString($this->userVars['title']);
      }
      else
      {
        return $this->userVars['title'];
      }
  }
	
	/*
	  Publication_model->GetByID($pub_id)
	  - Retrieves a publication row from the publication table
	  - Calls $this->GetFromRow($R) for further processing
	*/
	function getByID($pub_id)
	{
	}
	
	/*
	  Publication_model->GetFromRow($R)
	  - Stores all row elements in $this
	  - Retrieves authors and editors
	*/
	function getFromRow($R)
	{
	  //store row in $this
	  foreach ($R as $key => $value)
    {
      $this->$key = $value;
    }
    
	}
}
?>
<?php
class Publication_model extends Model {
  
  //declare variables: Publication vars
  var $pub_id       = '';
  var $entered_by   = '';
  var $year   	    = '';
  var $actualyear   = '';
  var $title   	    = '';
  var $bibtex_id   	= '';
  var $pub_type   	= '';
  var $type   	    = '';
  var $survey   	  = '';
  var $mark   	    = '';
  var $series   	  = '';
  var $volume   	  = '';
  var $publisher   	= '';
  var $location   	= '';
  var $issn         = '';  	 
  var $isbn   	    = '';
  var $firstpage   	= '';
  var $lastpage   	= '';
  var $journal   	  = '';
  var $booktitle   	= '';
  var $number   	  = '';
  var $institution  = '';
  var $address   	  = '';
  var $chapter   	  = '';
  var $edition   	  = '';
  var $howpublished = '';
  var $month   	    = '';
  var $organization = '';
  var $school   	  = '';
  var $note   	    = '';
  var $keywords   	= '';
  var $abstract   	= '';
  var $url   	      = '';
  var $doi   	      = '';
  var $crossref   	= '';
  var $namekey   	  = '';
  var $userfields   = '';
  var $specialchars = '';
  var $cleanjournal = '';
  var $cleantitle   = '';
  
  //authors and editors
  var $authors = array();
  var $editors = array();
  

	function Publication_model()
	{
		parent::Model();
	}
	
	/*
	  Publication_model->GetByID($pub_id)
	  - Retrieves a publication row from the publication table
	  - Calls $this->GetFromRow($R) for further processing
	*/
	function getByID($pub_id)
	{
	  $Q = $this->db->getwhere('publication', array('pub_id' => $pub_id));
	  if ($Q->num_rows() > 0)
	  {
	    $this->getFromRow($Q->row());
	  }  
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
    
    //retrieve authors and editors
    $this->db->select('*');
    $this->db->from('author, publicationauthor');
    $this->db->where('author.ID = publicationauthor.author');//', 'publicationauthor.author');
    $this->db->where('publicationauthor.pub_id', $this->pub_id);
    $this->db->orderby('publicationauthor.rank');
    
    $Q = $this->db->get();
    if ($Q->num_rows() > 0)
    {
      $this->load->model('author_model');
      
      foreach ($Q->result() as $R)
      {
        $author = new Author_model;
        $author->getFromRow($R);
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
	}
	function getTitle()
	{
	  return $this->title;
	}
	
	function getAuthors()
	{
	  $result = "";
	  $authorCount = count($this->authors);
	  if ($authorCount > 0)
	  {
  	  $result .= "<div id='commalist'>\n<ul>";
  	  foreach ($this->authors as $author)
  	  {
  	    $result .= "<li>".$author->getName()."</li>\n";
  	  }
  	  $result .= "</ul>\n</div>\n";
  	  return $result;
  	}
    else return "";
  }
}
?>
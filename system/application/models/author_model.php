<?php
class Author_model extends Model {
  
  //declare variables: Author vars
  var $ID           = '';
  var $surname   	  = '';
  var $von   	    	= '';
  var $firstname   	= '';
  var $email   	    = '';
  var $url   	    	= '';
  var $institute   	= '';
  var $specialchars = '';  	 
  var $cleanname   	= '';
  

	function Author_model()
	{
		parent::Model();
	}
	
	function getByID($ID)
	{
	  $Q = $this->db->getwhere('author', array('ID' => $ID));
	  if ($Q->num_rows() > 0)
	  {
	    $this->getFromRow($Q->row());
	  }  
	}
	
	function getFromRow($R)
	{
	  foreach ($R as $key => $value)
    {
      $this->$key = $value;
    }
  }
  
  function getName()
  {
    return trim($this->firstname." ".trim($this->von." ".trim($this->surname)));
  }
  
  function getDetailArray()
  {
    $details = array();
    $details['firstname'] = $this->firstname;
    $details['von']       = $this->von;
    $details['surname']   = $this->surname;
    $details['institute'] = $this->institute;
    $details['email']     = $this->email;
    $details['url']       = $this->url;
    
    return $details;
  }
}
?>
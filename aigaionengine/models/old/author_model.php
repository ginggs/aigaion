<?php
class Author_model extends Model {
  
  //declare variables: Author vars
  
  //The systemVars array contains those variables that
  //cannot be set by users:
  //author_id, specialchars and cleanname
  var $systemVars   = array('ID' => '', 'specialchars' => '', 'cleanname' => '');
  
  //the userVars array contains all variables that may
  //be set by users.
  var $userVars     = array();
  
  //flags
  var $prettyPrint;
  
	function Author_model()
	{
		parent::Model();
		$this->prettyPrint = true;
	}
	
	function loadByID($ID)
	{
	  $Q = $this->db->getwhere('author', array('ID' => $ID));
	  if ($Q->num_rows() > 0)
	  {
	    $this->loadFromRow($Q->row());
	  }  
	}
	
	function loadFromRow($R)
	{
	  //get all fields from the database and store in userVars array
	  unset($this->userVars);
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
    //format output
    $this->_formatUserVars();
  }
  
  function loadFromPost()
  {
  }
  
  function loadFromArray($author)
  {
    $this->loadFromRow($author);
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
    return array_merge($this->systemVars, $this->userVars);
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
    $pref = "VLF";
    switch($pref) {
      case "FVL":
        $this->userVars['name'] = ltrim($this->userVars['firstname']." ".ltrim($this->userVars['von']." ".$this->userVars['surname']));
        break;
      case "VLF":
        {
          $this->userVars['name'] = ltrim($this->userVars['von']." ".$this->userVars['surname']);
          if ($this->userVars['firstname'])
            $this->userVars['name'] .= ", ".$this->userVars['firstname'];
        }
        break;
      default:
        break;
    }
  }
  
  function _getName()
  {
      return $this->userVars['name'];
  }
}
?>
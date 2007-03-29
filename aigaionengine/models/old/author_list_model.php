<?php

$this->CI = &get_instance();
$this->CI->load->model('author_model');
class Author_list_model extends Author_model {
  
  var $header       = '';
  var $authors      = array();

	function Author_list_model()
	{
		parent::Author_model();
	}
  
  function loadAll()
  {
	  $this->db->orderby('cleanname');
	  $Q = $this->db->get('author');
	  if ($Q->num_rows() > 0)
	  {
	    $this->_loadFromResult($Q);
	  }
  }
  
  function loadWhere($cleanname)
  {
    $this->db->orderby('cleanname');
    $this->db->like('cleanname', $cleanname);
    $Q = $this->db->get('author');
	  if ($Q->num_rows() > 0)
	  {
	    $this->_loadFromResult($Q);
	  }
  }
  
  function _loadFromResult($Q)
  {
    foreach ($Q->result() as $R)
    {
      //use Author_model function
      $this->loadFromRow($R);
      $author = array();
      $author = $this->getAsArray();
      $this->authors[$author['ID']] = $author;
    }
  }
}
?>
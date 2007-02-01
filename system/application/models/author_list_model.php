<?php
class Author_list_model extends Model {
  
  var $header       = '';
  var $authors      = array();

	function Author_list_model()
	{
		parent::Model();
	}
	
	function getAll()
	{
	  $this->db->orderby('cleanname');
	  $Q = $this->db->get('author');
	  if ($Q->num_rows() > 0)
	  {
	    $this->load->model('author_model');
	    foreach ($Q->result() as $R)
	    {
	      $author = new Author_model;
	      $author->getFromRow($R);
	      $this->authors[] = $author;
	      unset($author);
	    }
	  }
	}
}
?>
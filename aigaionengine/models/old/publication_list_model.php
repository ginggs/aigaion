<?php
$this->CI = &get_instance();
$this->CI->load->model('publication_model');
class Publication_list_model extends Publication_model {
  
  var $header       = '';
  var $publications = array();

	function Publication_list_model()
	{
		parent::Publication_model();
	}
	
	/*
	  Publication_model->GetRange($start_pub_id, $count)
	  - Retrieves $count publications starting with pub_id $start_pub_id
	*/
	function loadRange($start_pub_id, $count)
	{
	  $this->db->where(array('pub_id >=' => $pub_id));
	  $this->db->orderby('pub_id');
	  $this->db->limit($count);
	  $Q = $this->db->get('publication');
	  
	  if ($Q->num_rows() > 0)
	  {
      $this->_loadFromResult($Q);	  
    }
	  return $this;
	}
	
	/*
	  Publication_model->GetAll()
	  - Retrieves all publications in the database
	*/
	function loadAll()
	{
	  $this->db->orderby('actualyear, cleantitle');
	  $Q = $this->db->get('publication');
	  if ($Q->num_rows() > 0)
	  {
	    $this->_loadFromResult($Q);
	  }
	}
	
	function loadForAuthor($author_id)
	{
	  $this->db->select('*');
    $this->db->from('publication');
    $this->db->join('publicationauthor', 'publication.pub_id = publicationauthor.pub_id', 'left');
    $this->db->where('publicationauthor.author', $author_id);
    $this->db->orderby('actualyear, cleantitle');
    
    $Q = $this->db->get();
    if ($Q->num_rows() > 0)
    {
      $this->_loadFromResult($Q);
    }
    return count($this->publications);
	}
	
	function _loadFromResult($Q)
  {
    foreach ($Q->result() as $R)
    {
      //use Publication_model function
      $this->loadFromRow($R);
      $publication = array();
      $publication = $this->getAsArray();
      $this->publications[$publication['pub_id']] = $publication;
    }
  }
}
?>
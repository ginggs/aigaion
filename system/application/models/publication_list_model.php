<?php
class Publication_list_model extends Model {
  
  var $header       = '';
  var $publications = array();

	function Publication_list_model()
	{
		parent::Model();
	}
	
	/*
	  Publication_model->GetRange($start_pub_id, $count)
	  - Retrieves $count publications starting with pub_id $start_pub_id
	*/
	function getRange($start_pub_id, $count)
	{
	  $this->db->where(array('pub_id >=' => $pub_id));
	  $this->db->orderby('pub_id');
	  $this->db->limit($count);
	  $Q = $this->db->get('publication');
	  
	  if ($Q->num_rows() > 0)
	  {
	    $this->load->model('publication_model');
	    foreach ($Q->result() as $R)
	    {
	      $publication = new Publication_model;
	      $publication->GetFromRow($R);
	      $this->publications[] = $publication;
	      unset($publication);
	    }
	  }
	  return $this;
	}
	
	/*
	  Publication_model->GetAll()
	  - Retrieves all publications in the database
	*/
	function getAll()
	{
	  $this->db->orderby('actualyear, cleantitle');
	  $Q = $this->db->get('publication');
	  if ($Q->num_rows() > 0)
	  {
	    $this->load->model('publication_model');
	    foreach ($Q->result() as $R)
	    {
	      $publication = new Publication_model;
	      $publication->getFromRow($R);
	      $this->publications[] = $publication;
	      unset($publication);
	    }
	  }
	}
	
	function getForAuthor($author_id)
	{
	  $this->db->select('*');
    $this->db->from('publication');
    $this->db->join('publicationauthor', 'publication.pub_id = publicationauthor.pub_id', 'left');
    $this->db->where('publicationauthor.author', $author_id);
    $this->db->orderby('actualyear, cleantitle');
    
    $Q = $this->db->get();
    if ($Q->num_rows() > 0)
    {
      $this->load->model('publication_model');
      
      foreach ($Q->result() as $R)
      {
        $publication = new Publication_model;
        $publication->getFromRow($R);
        $this->publications[] = $publication;
        unset($publication);
      }
    }
    return count($this->publications);
	}
}
?>
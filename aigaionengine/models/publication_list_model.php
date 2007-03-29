<?php
/*
Web based document management system
Copyright (C) 2007  (in alphabetical order):
Wietse Balkema, Arthur van Bunningen, Dennis Reidsma, Sebastan Schleussner

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

/*
  Publication_list_model class
  Class for storing and retrieving multiple publications.
  The actual publication data are stored in an array with Publication objects:
  
  -- Functions --
  
  bool loadAll()
    Retrieves a list with all publications in the database, ordered by year.
    Returns true on success, false on fail.

  bool loadRange($start_pub_id, $count)
    Retrieve $count publications starting with pub_id $start_pub_id
    return true on success, false on fail.
    
  bool loadForAuthor($author_id)
    Retrieve all publications for the author with $author_id

  bool _loadFromResult($Q)
    Loads the publications from a query result from the publication table.
    Returns true on success, false on fail.
  
  bool _clearList()
    Cleans up old authorlist data and creates a new authorlist array.
    Returns true on success, false on fail.
  

  bool format($formatStyle, $list = '')
    Formats the list according to the specified format style.
    When no $list is passed, $this->list is used.
    $list should be an array of the Author class.
    The formatted list is stored in $this->list.
    Returns true on success, false on fail.
*/
$this->CI = &get_instance();
$this->CI->load->model('publication_model');
class Publication_list_model extends Publication_model {
  
  var $header       = '';
  var $list         = array();

  //constructor
  function Publication_list_model()
  {
    parent::Publication_model();
  }
  
  function loadAll()
  {
    //retrieve all publications, order by actualyear and cleantitle
    $Q = $this->db->query("SELECT * FROM publication ORDER BY actualyear, cleantitle");
    if ($Q->num_rows() > 0)
    {
      //load the list
      $this->_loadFromResult($Q);
    }
  }
  
  function loadRange($start_pub_id, $count)
  {
    //retrieve list
    $Q = $this->db->query("SELECT * FROM publication 
                           WHERE pub_id >= ".$this->db->escape($start_pub_id)." 
                           ORDER BY pub_id
                           LIMIT ".$this->db->escape($count));
    //retrieve results or fail    
    if ($Q->num_rows() > 0)
    {
      return $this->_loadFromResult($Q);	  
    }
    return false;
  }
  
  function loadForAuthor($author_id)
  {
		$Q = $this->db->query("SELECT DISTINCT publication.* FROM publication, publicationauthor
                           WHERE publicationauthor.author = ".$this->db->escape($author_id)."
                           AND publication.pub_id = publicationauthor.pub_id
                           ORDER BY actualyear, cleantitle");
                           
/*
    $this->db->select('*');
    $this->db->from('publication');
    $this->db->join('publicationauthor', 'publication.pub_id = publicationauthor.pub_id', 'left');
    $this->db->where('publicationauthor.author', $author_id);
    $this->db->orderby('actualyear, cleantitle');
    
    $Q = $this->db->get();
*/
    if ($Q->num_rows() > 0)
    {
      $this->_loadFromResult($Q);
    }
    return count($this->list);    
  }
  
  function _loadFromResult($Q)
  {
    $this->crossref_cache = array();
    foreach ($Q->result() as $R)
    {
      //use Publication_model functions
      //clean up any publication data
      $this->_loadFromRow($R);
      $this->list[] = $this->data;
    }
    //cleanup
    $this->_clearData();
    unset($this->crossref_cache);
  }
  
  function _clearList()
  {
    $this->_clearData();
    unset($this->list);
    $this->list = array();
    
    return (isset($this->list));
  }
  
  function _format($formatStyle, $list = '')
  {
    if ($list == '')
    {
      $list = $this->list;
    }
    
    $this->_clearList();
    
    foreach ($list as $data)
    {
      parent::_format($formatStyle, $data);
      $this->list[$this->data->pub_id] = $this->data;
    }
    
    //cleanup
    $this->_clearData();
  }
}
?>
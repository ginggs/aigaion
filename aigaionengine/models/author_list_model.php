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
  Author_list_model class
  Class for storing and retrieving multiple authors.
  The actual author data are stored in an array with Author objects:
  
  -- Functions --
  
  bool loadAll()
    Retrieves a list with all authors in the database, ordered by cleanname.
    Returns true on success, false on fail.

  bool loadWhere($cleanname)
    Retrieves a list with all authors in the database where the cleanname begins
    with the character sequence as presented in $cleanname.
    Returns true on success, false on fail.
    
  bool loadForPublication($pub_id, $is_editor = 'N')
    Retrieves all authors/editors for the publication with pub_id = $pub_id.
    Returns true on success, false on fail.
    
  bool _loadFromResult($Q)
    Loads the authors from a query result from the author table.
    Returns true on success, false on fail.
    
  bool _addFromRow($R)
    Adds an instance of an author object to the list.
    Returns true on success, false on fail.
    
  bool _addFromExactName($firstname = '', $von = '', $surname = '')
    Adds an author to the list only when an exact match of the name is found.
    Returns true on success, false on fail.
    
  bool _addToList($firstname = '', $von = '', $surname = '')
    Adds an author to the list. When the author is found in the database,
    the entire object is added, else only the name passed is set in the author object.
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
$this->CI->load->model('author_model');
class Author_list_model extends Author_model {

  var $header       = '';       //header contains the title presented in the list header
  var $list         = array();  //array, to contain author data

  //constructor
  function Author_list_model()
  {
    parent::Author_model();
  }
  
  function loadAll()
  {
    //get all authors from the database, order by cleanname
    $Q = $this->db->query('SELECT * FROM author ORDER BY cleanname');
    
    //retrieve results or fail
    if ($Q->num_rows() > 0)
    {
      return $this->_loadFromResult($Q);
    }
    else
      return false;
  }
  
  function loadWhere($cleanname)
  {
    //select all authors from the database where the cleanname begins with the characters
    //as given in $cleanname
    $Q = $this->db->query('SELECT * FROM author 
                           WHERE cleanname LIKE "'.addslashes($cleanname).'%" 
                           ORDER BY cleanname');
    
    //retrieve results or fail
    if ($Q->num_rows() > 0)
    {
      return $this->_loadFromResult($Q);
    }
    else
      return false;
  }
  
  function loadForPublication($pub_id, $is_editor = 'N')
  {
    //retrieve authors and editors
    $Q = $this->db->query("SELECT * FROM author, publicationauthor 
                           WHERE author.ID = publicationauthor.author
                           AND publicationauthor.pub_id = ".$this->db->escape($pub_id)."
                           AND is_editor = ".$this->db->escape($is_editor)."
                           ORDER BY publicationauthor.rank");
    
    //retrieve results or fail                       
    if ($Q->num_rows() > 0)
    {
      return $this->_loadFromResult($Q);
    }
    else
      return false;
    
  }
  
  function _loadFromResult($Q)
  {
    //cleanup old data
    $this->_clearList();
    
    //add each row to the list
    foreach ($Q->result() as $R)
    {
      //if an error occurs, return false
      if (!$this->_addFromRow($R))
        return false;
    }
    //cleanup single authordata and return
    return $this->_clearData();
  }

  function _addFromRow($R)
  {
    //use Author_model functions
    //clean up any author data, on fail return false
    if (!$this->_clearData())
      return false;
    
    //add the author to the list, return true on success
    if ($this->_loadFromRow($R))
    {
      $this->list[] = $this->data;
      return true;
    }
    else
      return false;
  }
  
  function _addFromExactName($firstname = '', $von = '', $surname = '')
  {
    //clean up any author data, on fail return false
    if (!$this->_clearData())
      return false;

    //try to load the author from the database, return true on success
    if ($this->loadByExactName($firstname, $von, $surname))
    {
      $this->list[] = $this->data;
      return true;
    }
    else
      return false;
  }
  
  function _addToList($firstname = '', $von = '', $surname = '')
  {
    //clean up any author data, on fail return false
    if (!$this->_clearData())
      return false;

    //try to load the author from the database, return true on success
    if ($this->loadByExactName($firstname, $von, $surname))
    {
      $this->list[] = $this->data;
      return true;
    }
    else if ($this->setByName($firstname, $von, $surname))
    {
      $this->list[] = $this->data;
      return true;
    }
    else
      return false;
  }
  
  function _clearList()
  {
    //cleanup parent data and create new list.
    $this->_clearData();
    unset($this->list);
    $this->list = array();
    
    return isset($this->list);
  }
  
  function format($formatStyle, $list = '')
  {
    //if no list is passed, use $this->list
    if ($list == '')
      $list = $this->list;
    else
    {
      //cleanup and assign new list. $list should be an array of the Author class type.
      $this->_clearList();
      $this->list = $list;
    }

    //perform the formatting.
    foreach ($list as $data)
    {
      //the parent formatting function stores the formatted data in $this->data
      parent::format($formatStyle, $data);
      
      //retrieve the data element from the parent 
      $this->list[] = $this->data;
    }
    
    //cleanup the parent data.
    $this->_clearData();
  }
}
?>
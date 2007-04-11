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
  Author class
  Class for storing data of one single author. The class is used
  in the Author_model and Author_list_model classes.
  
  Besides data storage, some author handling functions are available.
  
  -- Functions --
  
  string getName($style = '')
    Returns the author name, formatted according to the user's preference.
*/
class Author {
  //Author variables are named after their corresponding database
  //field names.
  
  //system vars
  var $author_id    = 0;
  var $specialchars = '';
  var $cleanname    = '';
  
  //user vars
  var $firstname    = '';
  var $von          = '';
  var $surname      = '';
  var $email        = '';
  var $url          = '';
  var $institute    = '';
  
  //class constructor
  function Author()
  {
  }
  
  //getName returns the author name, formatted according to the user's preference
  function getName($style = '')
  {
    //if no style is given, get style from user preference
    if ($style == '')
      $style = 'vlf';
//TODO: GET STYLE FROM USER PREF
      
    switch($style) {
      case 'fvl':   //first von last
        $name = $this->firstname;
        if ($this->von != '')
          ($name != '') ? $name .= " ".$this->von : $name = $this->von;
        
        if ($this->surname != '')
          ($name != '') ? $name .= " ".$this->surname : $name = $this->surname;
          
        return $name;
        break;
      
      case 'vlf':   //von last, first
        $name = $this->von;
        if ($this->surname != '')
          ($name != '') ? $name .= " ".$this->surname : $name = $this->surname;
        
        if ($this->firstname != '')
          ($name != '') ? $name .= ", ".$this->firstname : $name = $this->firstname;
          
        return $name;
        break;
      
      case 'vl':    //von last
        $name = $this->von;
        if ($this->surname != '')
          ($name != '') ? $name .= " ".$this->surname : $name = $this->surname;
          
        return $name;
        break;
      
      default:      //last, von, first
        $name = $this->surname;
        if ($this->von != '')
          ($name != '') ? $name .= ", ".$this->von : $name = $this->von;
        
        if ($this->firstname != '')
          ($name != '') ? $name .= ", ".$this->firstname : $name = $this->firstname;
          
        return $name;
        break;
    }
  }
}

/*
  Author_model class
  Class for storing and retrieving Author data.
  The actual author data are stored in an Author object:
  $this->$data;
  
  -- Functions --
  
  bool loadByID($author_id)
    Retrieves an author from the database and stores it in a new Author object.
    returns true on success, false on fail.
    
  bool loadByExactName($firstname = "", $von = "", $surname = "")
    Retrieves an author from the database with the name as specified by the function parameters.
    returns true on success, false on fail.
  
  bool setByName($firstname = "", $von = "", $surname = "")
    Creates a new authormodel with the passed data. Does not commit to database.
    Returns true on success, false on fail.
    
  bool loadFromArray($author_array)
  bool _loadFromRow($author_row)
    Retrieves an author from an author result row.
    returns true on success, false on fail.
    
  bool loadFromPost()
    Retrieves an author from an input post.
    returns true on success, false on fail.
    
  int addObject()
    Adds an author to the database.
    returns the author_id or false on fail.
  
  bool updateObject()
    Updates an author in the database.
    returns true on success, false on fail.
  
  bool deleteObject()
    Removes an author from the database.
    returns true on success, false on fail.
  
  bool _clearData()
    Cleans up old author data and creates a new Author object.
    Returns true on success, false on fail.
    
  bool format($formatStyle, $data = '')
    Formats the data according to the specified format style.
    When no $data is passed, $this->data is used.
    $data should be of the Author class.
    The formatted data are stored in $this->data.
    Returns true on success, false on fail.
    
*/
class Author_model extends Model {
  
  //$data can hold one single author of the Author class
  var $data         = '';
  
  //class constructor
  function Author_model()
  {
    //call parent constructor to initialize the CI basic class functions
    parent::Model();
    
    //init $data with new author object
    $this->data = new Author;
  }
  
  function loadByID($author_id)
  {
    //retrieve one author row	  
    $Q = $this->db->query("SELECT * FROM author WHERE author_id = ".$this->db->escape($author_id));
    if ($Q->num_rows() == 1) 
    {
      //load the author
      return $this->_loadFromRow($Q->row());
    }
    else 
      return false;
  }
  
  function loadByExactName($firstname = "", $von = "", $surname = "")
  {
    //check if there is input, if not fail
    if (!($firstname || $von || $surname))
      return false;
      
    //prepare the name
    $name = $surname;
    if ($von != '')
      ($name != '') ? $name .= ", ".$von : $name = $von;
    
    if ($firstname != '')
      ($name != '') ? $name .= ", ".$firstname : $name = $firstname;
    
    //strip out any special characters
    $name = stripBibCharsFromString($name);
    $name = stripQuotesFromString($name);
    
    //do the query
    $Q = $this->db->query("SELECT * FROM author WHERE cleanname = ".$this->db->escape($name));
  
    //only when a single result is found, load the result. Else fail
    if ($Q->num_rows() == 1)
      return $this->_loadFromRow($Q->row());
    else
      return false;
  }
  
  function setByName($firstname = "", $von = "", $surname = "")
  {
    //check if there is input, if not fail
    if (!($firstname || $von || $surname))
      return false;
    
    //pack into array
    $authorArray = array("firstname" => $firstname, "von" => $von, "surname" => $surname);
    
    //load from array
    return $this->loadFromArray($authorArray);
  }
  
  function loadFromArray($authorArray)
  {
    return $this->_loadFromRow($authorArray);
  }
  
  function _loadFromRow($R)
  {
    //cleanup old author data and create new Author object
    if (!$this->_clearData())
      return false;
    
    //get all fields from the database and store in the Author object
    //since the variables in the Author object have the same names as the database fields
    //we can use this simple foreach loop
    foreach ($R as $key => $value)
    {
      $this->data->$key = $value;
    }
    return true;
  }
  
  function loadFromPost()
  {
    //cleanup old author data and create new Author object
    if (!$this->_clearData())
      return false;
    
    //create the array with variables to retrieve
    $fields = array('author_id',
                    'specialchars',
                    'cleanname',
                    'firstname',
                    'von',
                    'surname',
                    'email',
                    'url',
                    'institute'
                   );
    
    //retrieve all fields
    foreach ($fields as $key)
    {
      $this->data->$key = $this->input->post($key);
    }
    
    return true;
  }
  
  function addObject()
  {
    //fields that are to be submitted
    $fields = array('specialchars',
                    'cleanname',
                    'firstname',
                    'von',
                    'surname',
                    'email',
                    'url',
                    'institute'
                   );
    
    //check for specialchars
    $specialfields = array('firstname', 'von', 'surname', 'institute');
    foreach ($specialfields as $field)
    {
      if (findSpecialCharsInString($this->data->$field))
        $this->data->specialchars = 'TRUE';
    }
    
    //create cleanname
    $cleanname = stripSpecialCharsFromString($this->data->getName('lvf'));
    $cleanname = stripQuotesFromString($cleanname);
    $this->data->cleanname = $cleanname;
    
    //get the data to store in the database
    $data = array();
    foreach($fields as $field)
      $data[$field] = $this->data->$field;
    
    //insert into database using active record helper
    $this->db->insert('author', $data);
    
    //update this author's author_id
    $this->data->author_id = $this->db->insert_id();
    
    return $this->data->author_id;
  }
  
  function updateObject()
  {
    //fields that are to be updated
    $fields = array('specialchars',
                    'cleanname',
                    'firstname',
                    'von',
                    'surname',
                    'email',
                    'url',
                    'institute'
                   );
    
    //check for specialchars
    $specialfields = array('firstname', 'von', 'surname', 'institute');
    foreach ($specialfields as $field)
    {
      if (findSpecialCharsInString($this->data->$field))
        $this->data->specialchars = 'TRUE';
    }
    
    //create cleanname
    $cleanname = stripSpecialCharsFromString($this->data->getName('lvf'));
    $cleanname = stripQuotesFromString($cleanname);
    $this->data->cleanname = $cleanname;
    
    //get the data to store in the database
    $data = array();
    foreach($fields as $field)
      $data[$field] = $this->data->$field;
    
    //update database using active record helper
    $this->db->where('author_id', $this->author_id);
    $this->db->update('mytable', $data);

    //if the update was succesful, only 1 row is affected
    if ($this->db->affected_rows() == 1)
      return true;
    else
      return false;
  }
  
  function deleteObject()
  {
    //only delete a valid object
    if ($this->data->author_id == 0)
      return false;
      
    //remove all links to this author
/*
TODO:
- remove publicationauthorlinks
- remove other (new?) authorlinks
*/
    //remove the actual author
    $this->db->where('author_id', $this->data->author_id);
    $this->db->delete('author');
    
    //if the delete was succesful, only one single row is affected
    //please note: mysql returns 0 affected rows, CI has a work-around
    //in the db class.
    if ($this->db->affected_rows() == 1)
      return true;
    else
      return false;
  }
  
  function _clearData()
  {
    //cleanup old author data and create new Author
    unset($this->data);
    $this->data = new Author;
    
    return (isset($this->data));
  }
  
  function format($formatStyle, $data='')
  {
    //if no data are passed, use $this->data
    if ($data == '')
      $data = $this->data;
    else
    {
      //cleanup and assign new data. $data should be of the Author class type.
      $this->_clearData();
      $this->data = $data;
    }
    
    //only format if there are special characters in the data
    if ($data->specialchars == 'TRUE')
    {  
      //the only fields where special characters should be formatted:
      $fields = array(  'firstname',
                        'von',
                        'surname',
                        'institute'
                      );

      //TODO: FORMATTING, FOR DIFFERENT FORMATTING STYLES                      
      foreach ($fields as $field)
      {
        $this->data->$field = $data->$field;
      }
    }
  }
}
?>
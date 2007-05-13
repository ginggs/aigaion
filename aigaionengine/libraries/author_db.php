<?php
/** This class regulates the database access for Authors. Several accessors are present that return an Author or 
array of Authors. */
class Author_db {
  
    var $CI = null;
  
    function Author_db()
    {
        $this->CI = &get_instance();
    }

  function getByID($author_id)
  {
    //retrieve one author row	  
    $Q = $this->CI->db->query("SELECT * FROM author WHERE author_id = ".$this->CI->db->escape($author_id));
    if ($Q->num_rows() == 1) 
    {
      //load the author
      return $this->getFromRow($Q->row());
    }
    else 
      return false;
  }
  
  function getByExactName($firstname = "", $von = "", $surname = "")
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
    $Q = $this->CI->db->query("SELECT * FROM author WHERE cleanname = ".$this->CI->db->escape($name));
  
    //only when a single result is found, load the result. Else fail
    if ($Q->num_rows() == 1)
      return $this->getFromRow($Q->row());
    else
      return null;
  }
  
  function setByName($firstname = "", $von = "", $surname = "")
  {
    //check if there is input, if not fail
    if (!($firstname || $von || $surname))
      return null;
    
    //pack into array
    $authorArray = array("firstname" => $firstname, "von" => $von, "surname" => $surname);
    
    //load from array
    return $this->getFromArray($authorArray);
  }
  
  function getFromArray($authorArray)
  {
    return $this->getFromRow($authorArray);
  }
  
  function getFromRow($R)
  {
    $author = new Author;
    foreach ($R as $key => $value)
    {
        $author->$key = $value;
    }
    return $author;
  }
  
  function getFromPost()
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
    
    $author = new Author;
    
    //retrieve all fields
    foreach ($fields as $key)
    {
      $author->$key = $this->input->post($key);
    }
    return $author;
  }
  
  function add($author)
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
      if (findSpecialCharsInString($author->$field))
        $author->specialchars = 'TRUE';
    }
    
    //create cleanname
    $cleanname = stripBibCharsFromString($author->getName('lvf'));
    $cleanname = stripQuotesFromString($cleanname);
    $author->cleanname = $cleanname;
    
    //get the data to store in the database
    $data = array();
    foreach($fields as $field)
      $data[$field] = $author->$field;
    
    //insert into database using active record helper
    $this->CI->db->insert('author', $data);
    
    //update this author's author_id
    $author->author_id = $this->CI->db->insert_id();
    return $author;
  }
  
  function update($author)
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
      if (findSpecialCharsInString($author->$field))
        $author->specialchars = 'TRUE';
    }
    
    //create cleanname
    $cleanname = stripSpecialCharsFromString($author->getName('lvf'));
    $cleanname = stripQuotesFromString($cleanname);
    $author->cleanname = $cleanname;
    
    //get the data to store in the database
    $data = array();
    foreach($fields as $field)
      $data[$field] = $author->$field;
    
    //update database using active record helper
    $this->CI->db->where('author_id', $author->author_id);
    $this->CI->db->update('author', $data);

    //if the update was succesful, only 1 row is affected
    if ($this->CI->db->affected_rows() == 1)
      return true;
    else
      return false;
  }
  
  function deleteAuthor($author)
  {
    //only delete a valid object
    if ($author->author_id == 0)
      return false;
      
    //remove all links to this author
/*
TODO:
- remove publicationauthorlinks
- remove other (new?) authorlinks
*/
    //remove the actual author
    $this->db->where('author_id', $author->author_id);
    $this->db->delete('author');
    
    //if the delete was succesful, only one single row is affected
    //please note: mysql returns 0 affected rows, CI has a work-around
    //in the db class.
    if ($this->db->affected_rows() == 1)
      return true;
    else
      return false;
  }
  
  function getAllAuthors()
  {
    $result = array();
    
    //get all authors from the database, order by cleanname
    $Q = $this->CI->db->query('SELECT * FROM author ORDER BY cleanname');
    
    //retrieve results or fail
    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[] = $next;
      }
    }
    return $result;
  }
  
  function getAuthorsLike($cleanname)
  {
    //select all authors from the database where the cleanname begins with the characters
    //as given in $cleanname
    $Q = $this->CI->db->query('SELECT * FROM author 
                           WHERE cleanname LIKE "'.addslashes($cleanname).'%" 
                           ORDER BY cleanname');
    
    //retrieve results or fail
    $result = array();
    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[] = $next;
      }
    }
    
    return $result;

  }
  
  function getForPublication($pub_id, $is_editor = 'N')
  {
    $result = array();
    
    //retrieve authors and editors
    $Q = $this->CI->db->query("SELECT * FROM author, publicationauthorlink 
                           WHERE author.author_id = publicationauthorlink.author_id
                           AND publicationauthorlink.pub_id = ".$this->CI->db->escape($pub_id)."
                           AND publicationauthorlink.is_editor = ".$this->CI->db->escape($is_editor)."
                           ORDER BY publicationauthorlink.rank");
    
    //retrieve results or fail                       
    foreach ($Q->result() as $row)
    {
      $next = $this->getFromRow($row);
      if ($next != null)
      {
        $result[] = $next;
      }
    }

    return $result;
  }
  
  function ensureAuthorsInDatabase($authors)
  {
    if (!is_array($authors))
      return null;
      
    $result = array();
    foreach ($authors as $author)
    {
      $current      = $this->getByExactName($author->firstname, $author->von, $author->surname);
      if ($current == null)
        $current    = $this->add($author);
      
      $result[] = $author;
    }
    return $result;
  }
  
  function review($authors)
  {
    if (!is_array($authors))
      return null;
    
    $result_message   = "";
    
    //get database author array
    $db_authors = $this->getAllAuthors();
    
    //check availability of the keywords in the database
    foreach ($keywords as $keyword)
    {
      $keyword_low  = strtolower($keyword);
      $keyword_id   = array_search($keyword_low, $db_keywords);
      
      //is the keyword already in the db?
      if (!is_numeric($keyword_id))
      {
        //not found in the database, so check for similar keywords
        $db_distances = array();
        foreach ($db_keywords as $keyword_id => $db_keyword)
        {
          $distance = levenshtein($db_keyword, $keyword_low);
          if ($distance < 3)
            $db_distances[$keyword_id] = $distance;
        }
        
        //sort while keeping key relationship
        asort($db_distances, SORT_NUMERIC);
        
        //are there similar keywords?
        if (count($db_distances) > 0)
        {
          $result_message .= "Found similar keywords for <b>&quot;".$keyword."&quot;</b>:<br/>\n";
          $result_message .= "<ul>\n";
          foreach($db_distances as $key => $value)
          {
            $result_message .= "<li>".$db_keywords[$key]."</li>\n";
          }
          $result_message .= "</ul>\n";
        }
        //when no similar keywords are found, we add the unknown keyword to the database
        else
        {
          $this->add($keyword);
        }
      }
    }
    if ($result_message != "")
    {
      $result_message .= "Please review the entered keywords.<br/>\n";
      return $result_message;
    }
    else
      return null;
  }
}
?>